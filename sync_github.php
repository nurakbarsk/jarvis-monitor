<?php
/**
 * Jarvis Monitor — Sinkronisasi Riwayat Commit GitHub
 * Menarik riwayat commit lama (termasuk 200+ commit) ke tabel changelogs.
 *
 * Cara Pemakaian:
 * 1. CLI: php sync_github.php [nama_proyek] [github_token_opsional]
 * 2. Web: GET /sync_github.php?project=nama_proyek&token=opsional
 * 3. API: GET /api.php?action=sync_github&project=nama_proyek
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Tarik seluruh commit dari GitHub REST API dengan pagination loop otomatis
 * dan simpan ke database tanpa duplikasi.
 *
 * @param string $projectName Nama proyek di database
 * @param string|null $githubToken Personal Access Token GitHub (opsional)
 * @param int $maxPages Maksimal halaman yang diambil (default 20 = s.d. 2000 commit)
 * @return array
 */
function syncGitHubCommits(string $projectName, ?string $githubToken = null, int $maxPages = 20): array
{
    $db = getDb();

    // 1. Validasi keberadaan proyek
    $stmt = $db->prepare('SELECT id, name, repo_url, client_name FROM projects WHERE name = ?');
    $stmt->execute([$projectName]);
    $project = $stmt->fetch();

    if (!$project) {
        return [
            'ok'    => false,
            'error' => "Proyek \"{$projectName}\" tidak ditemukan di database.",
        ];
    }

    $repoUrl = trim((string)($project['repo_url'] ?? ''));
    if (empty($repoUrl)) {
        return [
            'ok'    => false,
            'error' => "URL repositori untuk proyek \"{$projectName}\" belum disetel.",
        ];
    }

    // 2. Ekstrak owner dan repo dari URL
    $repoMeta = parseGitHubOwnerRepo($repoUrl);
    if (!$repoMeta) {
        return [
            'ok'    => false,
            'error' => "Format URL repositori GitHub tidak valid: \"{$repoUrl}\". Gunakan format https://github.com/owner/repo atau git@github.com:owner/repo.git",
        ];
    }

    [$owner, $repo] = $repoMeta;

    // 3. Ambil token dari argumen atau fallback .env
    $token = $githubToken ?: (getenv('GITHUB_TOKEN') ?: null);

    // 4. Muat daftar commit yang sudah ada di database untuk mencegah duplikasi
    $stmtExisting = $db->prepare('SELECT commit_message, created_at FROM changelogs WHERE project_name = ?');
    $stmtExisting->execute([$projectName]);
    $existingMap = [];

    while ($row = $stmtExisting->fetch()) {
        $msgKey = trim((string)$row['commit_message']);
        $dateKey = !empty($row['created_at']) ? date('Y-m-d H:i:s', strtotime((string)$row['created_at'])) : '';
        $existingMap[$msgKey . '||' . $dateKey] = true;
    }

    $stmtInsert = $db->prepare('
        INSERT INTO changelogs (project_name, commit_message, commit_type, created_at)
        VALUES (?, ?, ?, ?)
    ');

    $page = 1;
    $totalFetched = 0;
    $totalInserted = 0;
    $totalSkipped = 0;
    $typeBreakdown = ['fitur' => 0, 'bugfix' => 0, 'update' => 0];

    // 5. Pagination loop (ambil 100 commit per halaman)
    while ($page <= $maxPages) {
        $apiUrl = sprintf(
            'https://api.github.com/repos/%s/%s/commits?per_page=100&page=%d',
            rawurlencode($owner),
            rawurlencode($repo),
            $page
        );

        $headers = [
            'User-Agent: Jarvis-Monitor-PHP/1.0',
            'Accept: application/vnd.github.v3+json',
        ];

        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . trim($token);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($curlError)) {
            return [
                'ok'             => false,
                'error'          => "Koneksi cURL ke GitHub API gagal pada halaman {$page}: {$curlError}",
                'total_inserted' => $totalInserted,
            ];
        }

        if ($httpCode === 404) {
            return [
                'ok'    => false,
                'error' => "Repositori GitHub \"{$owner}/{$repo}\" tidak ditemukan atau bertipe private. Jika private, sertakan GitHub Personal Access Token.",
            ];
        }

        if ($httpCode === 403 || $httpCode === 429) {
            $errData = json_decode((string)$response, true);
            $errMsg = $errData['message'] ?? 'API rate limit exceeded';
            return [
                'ok'             => false,
                'error'          => "GitHub API rate limit: {$errMsg}. Gunakan GitHub Personal Access Token untuk kuota 5000 request/jam.",
                'total_inserted' => $totalInserted,
                'total_skipped'  => $totalSkipped,
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'ok'             => false,
                'error'          => "GitHub API mengembalikan HTTP status {$httpCode} pada halaman {$page}.",
                'total_inserted' => $totalInserted,
            ];
        }

        $commits = json_decode((string)$response, true);
        if (!is_array($commits)) {
            break;
        }

        $pageCount = count($commits);
        if ($pageCount === 0) {
            // Sudah tidak ada commit lagi
            break;
        }

        // GitHub API mengembalikan commit dari yang terbaru ke terlama.
        // Kita simpan ke DB.
        foreach ($commits as $item) {
            $totalFetched++;
            $msg = trim((string)($item['commit']['message'] ?? ''));
            if ($msg === '') {
                continue;
            }

            // Dapatkan tanggal commit (committer date atau author date)
            $rawDate = $item['commit']['committer']['date'] ?? ($item['commit']['author']['date'] ?? null);
            $formattedDate = !empty($rawDate) ? date('Y-m-d H:i:s', strtotime((string)$rawDate)) : date('Y-m-d H:i:s');

            $dedupKey = $msg . '||' . $formattedDate;
            if (isset($existingMap[$dedupKey])) {
                $totalSkipped++;
                continue;
            }

            $type = classifyCommit($msg);
            $stmtInsert->execute([$projectName, $msg, $type, $formattedDate]);
            $existingMap[$dedupKey] = true;

            $totalInserted++;
            $typeBreakdown[$type]++;
        }

        // Jika commit pada halaman ini < 100, berarti seluruh riwayat commit telah habis
        if ($pageCount < 100) {
            break;
        }

        $page++;
    }

    return [
        'ok'             => true,
        'project_name'   => $projectName,
        'repository'     => "{$owner}/{$repo}",
        'pages_crawled'  => $page,
        'total_fetched'  => $totalFetched,
        'total_inserted' => $totalInserted,
        'total_skipped'  => $totalSkipped,
        'breakdown'      => $typeBreakdown,
        'message'        => sprintf(
            'Berhasil menyinkronkan %d commit baru untuk proyek "%s" (%d duplikat dilewati). Rincian: %d fitur, %d bugfix, %d update.',
            $totalInserted,
            $projectName,
            $totalSkipped,
            $typeBreakdown['fitur'],
            $typeBreakdown['bugfix'],
            $typeBreakdown['update']
        ),
    ];
}

// -------------------------------------------------------------------------
// ENTRY POINT EKSEKUSI (CLI & WEB)
// -------------------------------------------------------------------------
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $isCli = (PHP_SAPI === 'cli');

    if ($isCli) {
        $targetProject = $argv[1] ?? '';
        $cliToken = $argv[2] ?? null;

        if (empty($targetProject)) {
            // Tampilkan daftar proyek yang ada
            echo "========================================================\n";
            echo " Jarvis Monitor — Sinkronisasi Commit GitHub (CLI)\n";
            echo "========================================================\n\n";

            $db = getDb();
            $projects = $db->query('SELECT name, repo_url FROM projects ORDER BY id ASC')->fetchAll();

            if (empty($projects)) {
                echo "Belum ada proyek yang terdaftar di database.\n";
                echo "Gunakan: php sync_github.php <nama_proyek> [github_token]\n";
                exit(1);
            }

            echo "Daftar proyek terdaftar:\n";
            foreach ($projects as $p) {
                echo " - {$p['name']} ({$p['repo_url']})\n";
            }
            echo "\nJalankan: php sync_github.php <nama_proyek> [token_opsional]\n";
            echo "Atau sinkronkan semua proyek: php sync_github.php all [token_opsional]\n";
            exit(0);
        }

        if (strtolower($targetProject) === 'all') {
            $db = getDb();
            $projects = $db->query('SELECT name FROM projects ORDER BY id ASC')->fetchAll();
            echo "Menyinkronkan seluruh proyek (" . count($projects) . " proyek)...\n\n";

            foreach ($projects as $p) {
                echo "-> Memproses {$p['name']}... ";
                $res = syncGitHubCommits($p['name'], $cliToken);
                if ($res['ok']) {
                    echo "OK! (+{$res['total_inserted']} baru, {$res['total_skipped']} duplikat)\n";
                } else {
                    echo "GAGAL: {$res['error']}\n";
                }
            }
            echo "\nSelesai!\n";
            exit(0);
        }

        echo "Menyinkronkan riwayat commit untuk proyek \"{$targetProject}\"...\n";
        $res = syncGitHubCommits($targetProject, $cliToken);

        if (!$res['ok']) {
            echo "❌ ERROR: " . $res['error'] . "\n";
            exit(1);
        }

        echo "✅ " . $res['message'] . "\n";
        echo "   Total Commit Diperiksa : " . $res['total_fetched'] . "\n";
        echo "   Halaman Ditelusuri     : " . $res['pages_crawled'] . "\n";
        echo "   - Fitur Baru           : " . $res['breakdown']['fitur'] . "\n";
        echo "   - Bugfix               : " . $res['breakdown']['bugfix'] . "\n";
        echo "   - Update Lainnya       : " . $res['breakdown']['update'] . "\n";
        exit(0);
    }

    // Web Access
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');

    $projectName = trim((string)($_GET['project'] ?? ($_POST['project'] ?? '')));
    $token = trim((string)($_GET['token'] ?? ($_POST['token'] ?? ''))) ?: null;

    if (empty($projectName)) {
        jsonResponse([
            'error'   => 'Parameter "project" wajib disertakan.',
            'contoh'  => 'sync_github.php?project=jarvis-monitor',
        ], 400);
    }

    $result = syncGitHubCommits($projectName, $token);
    $statusCode = $result['ok'] ? 200 : 400;
    jsonResponse($result, $statusCode);
}
