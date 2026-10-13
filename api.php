<?php
/**
 * Jarvis Monitor — REST API Gateway (PHP Native PDO MySQL)
 * Endpoint: api.php?action=...
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Set header JSON & CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Tangani preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$db = getDb();

// Tangani HTTP Method (dukung method spoofing via _method)
$method = strtoupper($_SERVER['REQUEST_METHOD']);
if ($method === 'POST') {
    if (!empty($_POST['_method'])) {
        $method = strtoupper($_POST['_method']);
    } elseif (!empty($_GET['_method'])) {
        $method = strtoupper($_GET['_method']);
    }
}

// Tangani URL parsing: dukung '?action=...' atau rewrite '?route=...'
$action = $_GET['action'] ?? '';
if (empty($action) && !empty($_GET['route'])) {
    $routeParts = explode('/', trim($_GET['route'], '/'));
    $action = $routeParts[0] ?? '';
    if (isset($routeParts[1]) && !isset($_GET['id']) && !isset($_GET['project'])) {
        if ($action === 'data') {
            $action = 'project_data';
            $_GET['project'] = $routeParts[1];
        } else {
            $_GET['id'] = $routeParts[1];
        }
    }
}

// Ambil input JSON jika Content-Type adalah application/json
$rawInput = file_get_contents('php://input');
$jsonBody = [];
if (!empty($rawInput) && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
    $decoded = json_decode($rawInput, true);
    if (is_array($decoded)) {
        $jsonBody = $decoded;
    }
}

// Helper untuk membaca parameter dari JSON body atau $_POST atau $_GET
$input = array_merge($_GET, $_POST, $jsonBody);

try {
    switch ($action) {
        /* -----------------------------------------------------------------
         * 1. HEALTH CHECK
         * ----------------------------------------------------------------- */
        case 'health':
            jsonResponse([
                'ok'        => true,
                'status'    => 'healthy',
                'database'  => 'MySQL PDO Connected',
                'timestamp' => date('c'),
            ]);
            break;

        /* -----------------------------------------------------------------
         * 2. PROJECTS (CRUD)
         * ----------------------------------------------------------------- */
        case 'projects':
            if ($method === 'GET') {
                $stmt = $db->query('SELECT id, name, repo_url, client_name, created_at FROM projects ORDER BY created_at DESC, id DESC');
                jsonResponse(['projects' => $stmt->fetchAll()]);
            }

            if ($method === 'POST') {
                $repoUrl = trim((string)($input['repo_url'] ?? ''));
                $clientName = trim((string)($input['client_name'] ?? ''));

                if (empty($repoUrl)) {
                    jsonResponse(['error' => 'Link repositori GitHub wajib diisi.'], 400);
                }

                $repoName = parseRepoName($repoUrl);
                if (empty($repoName)) {
                    jsonResponse(['error' => 'URL repositori tidak valid atau nama repo tidak terdeteksi.'], 400);
                }

                // Cek duplikasi
                $stmtCheck = $db->prepare('SELECT id FROM projects WHERE name = ?');
                $stmtCheck->execute([$repoName]);
                if ($stmtCheck->fetch()) {
                    jsonResponse(['error' => "Repositori \"{$repoName}\" sudah terdaftar."], 400);
                }

                $stmtInsert = $db->prepare('INSERT INTO projects (name, repo_url, client_name, created_at) VALUES (?, ?, ?, NOW())');
                $stmtInsert->execute([$repoName, $repoUrl, $clientName ?: $repoName]);
                $newId = (int)$db->lastInsertId();

                $stmtNew = $db->prepare('SELECT id, name, repo_url, client_name, created_at FROM projects WHERE id = ?');
                $stmtNew->execute([$newId]);

                jsonResponse([
                    'ok'      => true,
                    'message' => 'Proyek berhasil ditambahkan.',
                    'project' => $stmtNew->fetch()
                ], 201);
            }

            if ($method === 'DELETE') {
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) {
                    jsonResponse(['error' => 'ID proyek tidak valid.'], 400);
                }

                $stmtFind = $db->prepare('SELECT id, name FROM projects WHERE id = ?');
                $stmtFind->execute([$id]);
                $project = $stmtFind->fetch();

                if (!$project) {
                    jsonResponse(['error' => 'Proyek tidak ditemukan.'], 404);
                }

                // Hapus data (ON DELETE CASCADE di MySQL akan otomatis menghapus changelogs, tickets, features, timetables)
                $stmtDel = $db->prepare('DELETE FROM projects WHERE id = ?');
                $stmtDel->execute([$id]);

                jsonResponse([
                    'ok'      => true,
                    'message' => "Proyek \"{$project['name']}\" beserta seluruh datanya berhasil dihapus.",
                    'project' => $project
                ]);
            }

            jsonResponse(['error' => 'Metode HTTP tidak didukung untuk action projects.'], 405);
            break;

        /* -----------------------------------------------------------------
         * 3. PROJECT DATA (AGREGASI LENGKAP DASHBOARD)
         * ----------------------------------------------------------------- */
        case 'project_data':
            $projectName = trim((string)($input['project'] ?? ''));
            if (empty($projectName)) {
                jsonResponse(['error' => 'Parameter nama proyek (project) wajib diisi.'], 400);
            }

            // Ambil info proyek
            $stmtProj = $db->prepare('SELECT id, name, repo_url, client_name, created_at FROM projects WHERE name = ?');
            $stmtProj->execute([$projectName]);
            $project = $stmtProj->fetch();

            if (!$project) {
                jsonResponse(['error' => "Proyek \"{$projectName}\" tidak ditemukan."], 404);
            }

            // 1. Changelogs (limit 50 terbaru)
            $stmtLogs = $db->prepare('SELECT id, project_name, commit_message, commit_type, created_at FROM changelogs WHERE project_name = ? ORDER BY created_at DESC, id DESC LIMIT 50');
            $stmtLogs->execute([$projectName]);
            $changelogs = $stmtLogs->fetchAll();

            // 2. Tickets (urutkan Open -> In Progress -> Resolved, lalu terbaru)
            $stmtTickets = $db->prepare("
                SELECT id, project_name, reporter_name, category, issue_description, image_url, status, created_at
                FROM tickets
                WHERE project_name = ?
                ORDER BY 
                    CASE status
                        WHEN 'Open' THEN 1
                        WHEN 'In Progress' THEN 2
                        ELSE 3
                    END,
                    created_at DESC, id DESC
            ");
            $stmtTickets->execute([$projectName]);
            $tickets = $stmtTickets->fetchAll();

            // 3. Features
            $stmtFeatures = $db->prepare('SELECT id, project_name, feature_name, description, doc_url, status, created_at FROM features WHERE project_name = ? ORDER BY created_at DESC, id DESC');
            $stmtFeatures->execute([$projectName]);
            $features = $stmtFeatures->fetchAll();

            // 4. Timetables (Roadmap bulanan)
            $stmtTime = $db->prepare('SELECT id, project_name, task_name, target_month, status, updated_at FROM timetables WHERE project_name = ? ORDER BY target_month ASC, id ASC');
            $stmtTime->execute([$projectName]);
            $timetable = $stmtTime->fetchAll();

            // 5. Weekly Activity (7 hari terakhir)
            $stmtAct = $db->prepare("
                SELECT DATE(created_at) AS day, COUNT(*) AS count
                FROM changelogs
                WHERE project_name = ? AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(created_at)
            ");
            $stmtAct->execute([$projectName]);
            $actRows = $stmtAct->fetchAll();
            $byDay = [];
            foreach ($actRows as $r) {
                $byDay[$r['day']] = (int)$r['count'];
            }

            $activity = [];
            for ($i = 6; $i >= 0; $i--) {
                $dayKey = date('Y-m-d', strtotime("-{$i} days"));
                $activity[] = [
                    'day'   => $dayKey,
                    'count' => $byDay[$dayKey] ?? 0,
                ];
            }

            jsonResponse([
                'project_name' => $projectName,
                'project'      => $project,
                'changelogs'   => $changelogs,
                'tickets'      => $tickets,
                'features'     => $features,
                'timetable'    => $timetable,
                'activity'     => $activity,
            ]);
            break;

        /* -----------------------------------------------------------------
         * 4. TICKETS (CRUD & UPLOAD GAMBAR)
         * ----------------------------------------------------------------- */
        case 'tickets':
            // SUB-ACTION: PATCH / PUT UPDATE STATUS ONLY
            if ($method === 'PATCH' || ($method === 'POST' && ($input['sub'] ?? '') === 'status')) {
                $id = (int)($input['id'] ?? 0);
                $status = trim((string)($input['status'] ?? 'Open'));

                $validStatuses = ['Open', 'In Progress', 'Resolved'];
                if (!in_array($status, $validStatuses, true)) {
                    jsonResponse(['error' => 'Status tiket tidak valid.'], 400);
                }

                $stmtUp = $db->prepare('UPDATE tickets SET status = ? WHERE id = ?');
                $stmtUp->execute([$status, $id]);

                $stmtGet = $db->prepare('SELECT id, project_name, reporter_name, category, issue_description, image_url, status, created_at FROM tickets WHERE id = ?');
                $stmtGet->execute([$id]);
                $ticket = $stmtGet->fetch();

                if (!$ticket) {
                    jsonResponse(['error' => 'Tiket tidak ditemukan.'], 404);
                }

                jsonResponse([
                    'ok'      => true,
                    'message' => "Status tiket diubah menjadi {$status}.",
                    'ticket'  => $ticket
                ]);
            }

            // SUB-ACTION: EDIT LENGKAP TIKET (PUT / POST _method=PUT)
            if ($method === 'PUT' || ($method === 'POST' && (($input['sub'] ?? '') === 'edit' || ($input['_method'] ?? '') === 'PUT'))) {
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) {
                    jsonResponse(['error' => 'ID tiket tidak valid.'], 400);
                }

                $stmtFind = $db->prepare('SELECT * FROM tickets WHERE id = ?');
                $stmtFind->execute([$id]);
                $existing = $stmtFind->fetch();

                if (!$existing) {
                    jsonResponse(['error' => 'Tiket tidak ditemukan.'], 404);
                }

                $reporterName = isset($input['reporter_name']) ? trim((string)$input['reporter_name']) : $existing['reporter_name'];
                $category = isset($input['category']) && strtolower(trim((string)$input['category'])) === 'ide' ? 'Ide' : 'Bug';
                $description = isset($input['issue_description']) ? trim((string)$input['issue_description']) : $existing['issue_description'];
                $imageUrl = $existing['image_url'];

                // Handle remove image flag
                $removeImage = ($input['remove_image'] ?? '') === 'true' || ($input['remove_image'] ?? '') === true;
                if ($removeImage && !empty($imageUrl)) {
                    $oldFilePath = __DIR__ . '/' . ltrim($imageUrl, '/');
                    if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                    $imageUrl = null;
                }

                // Handle upload screenshot baru jika ada
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }

                    $file = $_FILES['image'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                    if (!in_array($ext, $allowed, true)) {
                        jsonResponse(['error' => 'Format file tidak didukung. Gunakan .jpg, .jpeg, .png, atau .webp.'], 400);
                    }

                    if ($file['size'] > 5 * 1024 * 1024) {
                        jsonResponse(['error' => 'Ukuran file gambar maksimal 5MB.'], 400);
                    }

                    // Hapus gambar lama jika ada
                    if (!empty($imageUrl)) {
                        $oldFilePath = __DIR__ . '/' . ltrim($imageUrl, '/');
                        if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }

                    $fileName = sprintf('%d-%d.%s', (int)(microtime(true) * 1000), random_int(100000000, 999999999), $ext);
                    $destPath = UPLOAD_DIR . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $imageUrl = UPLOAD_URL . $fileName;
                    }
                }

                $stmtUpdate = $db->prepare('UPDATE tickets SET reporter_name = ?, category = ?, issue_description = ?, image_url = ? WHERE id = ?');
                $stmtUpdate->execute([$reporterName, $category, $description, $imageUrl, $id]);

                $stmtFind->execute([$id]);
                jsonResponse([
                    'ok'      => true,
                    'message' => 'Tiket berhasil diperbarui.',
                    'ticket'  => $stmtFind->fetch()
                ]);
            }

            // CREATE TIKET BARU (POST)
            if ($method === 'POST') {
                $projectName = trim((string)($input['project_name'] ?? ''));
                $reporterName = trim((string)($input['reporter_name'] ?? ''));
                $category = strtolower(trim((string)($input['category'] ?? 'Bug'))) === 'ide' ? 'Ide' : 'Bug';
                $description = trim((string)($input['issue_description'] ?? ''));

                if (empty($projectName) || empty($reporterName) || empty($description)) {
                    jsonResponse(['error' => 'Nama proyek, nama pelapor, dan deskripsi kendala wajib diisi.'], 400);
                }

                // Cek proyek
                $stmtP = $db->prepare('SELECT id, name, client_name FROM projects WHERE name = ?');
                $stmtP->execute([$projectName]);
                $project = $stmtP->fetch();

                if (!$project) {
                    jsonResponse(['error' => "Proyek \"{$projectName}\" tidak terdaftar."], 400);
                }

                // Proses Upload Gambar (opsional)
                $imageUrl = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }

                    $file = $_FILES['image'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                    if (!in_array($ext, $allowed, true)) {
                        jsonResponse(['error' => 'Format file tidak didukung. Gunakan .jpg, .jpeg, .png, atau .webp.'], 400);
                    }

                    if ($file['size'] > 5 * 1024 * 1024) {
                        jsonResponse(['error' => 'Ukuran gambar maksimal 5MB.'], 400);
                    }

                    $fileName = sprintf('%d-%d.%s', (int)(microtime(true) * 1000), random_int(100000000, 999999999), $ext);
                    $destPath = UPLOAD_DIR . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $imageUrl = UPLOAD_URL . $fileName;
                    }
                }

                $stmtInsert = $db->prepare('
                    INSERT INTO tickets (project_name, reporter_name, category, issue_description, image_url, status, created_at)
                    VALUES (?, ?, ?, ?, ?, "Open", NOW())
                ');
                $stmtInsert->execute([$projectName, $reporterName, $category, $description, $imageUrl]);
                $ticketId = (int)$db->lastInsertId();

                $stmtGet = $db->prepare('SELECT * FROM tickets WHERE id = ?');
                $stmtGet->execute([$ticketId]);
                $newTicket = $stmtGet->fetch();

                // Kirim notifikasi Telegram
                $icon = $category === 'Ide' ? '💡' : '🔴';
                $siteUrl = rtrim((string)(getenv('APP_URL') ?: 'https://matla.id/monitor/'), '/');
                $imgNotice = $imageUrl ? "\n📷 <b>Bukti:</b> <a href=\"{$siteUrl}/{$imageUrl}\">Lihat Screenshot</a>" : '';

                $teleMsg = "{$icon} <b>Tiket Baru: {$projectName} (#{$ticketId})</b>\n" .
                    "Kategori: <b>{$category}</b>\n" .
                    "Pelapor: <b>" . htmlspecialchars($reporterName) . "</b>\n" .
                    "Deskripsi: <i>" . htmlspecialchars($description) . "</i>" .
                    $imgNotice;

                sendTelegramNotification($teleMsg);

                jsonResponse([
                    'ok'      => true,
                    'message' => 'Tiket kendala berhasil dilaporkan ke tim developer.',
                    'ticket'  => $newTicket
                ], 201);
            }

            // DELETE TIKET
            if ($method === 'DELETE') {
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) {
                    jsonResponse(['error' => 'ID tiket tidak valid.'], 400);
                }

                $stmtFind = $db->prepare('SELECT * FROM tickets WHERE id = ?');
                $stmtFind->execute([$id]);
                $ticket = $stmtFind->fetch();

                if (!$ticket) {
                    jsonResponse(['error' => 'Tiket tidak ditemukan.'], 404);
                }

                // Hapus berkas fisik jika ada
                if (!empty($ticket['image_url'])) {
                    $filePath = __DIR__ . '/' . ltrim($ticket['image_url'], '/');
                    if (file_exists($filePath) && is_file($filePath)) {
                        @unlink($filePath);
                    }
                }

                $stmtDel = $db->prepare('DELETE FROM tickets WHERE id = ?');
                $stmtDel->execute([$id]);

                jsonResponse([
                    'ok'      => true,
                    'message' => 'Tiket berhasil dihapus.',
                    'ticket'  => $ticket
                ]);
            }

            jsonResponse(['error' => 'Metode HTTP tidak didukung untuk action tickets.'], 405);
            break;

        /* -----------------------------------------------------------------
         * 5. FEATURES (CRUD)
         * ----------------------------------------------------------------- */
        case 'features':
            if ($method === 'GET') {
                $projectName = trim((string)($input['project'] ?? ''));
                $stmt = $db->prepare('SELECT * FROM features WHERE project_name = ? ORDER BY created_at DESC, id DESC');
                $stmt->execute([$projectName]);
                jsonResponse(['features' => $stmt->fetchAll()]);
            }

            if ($method === 'POST') {
                $projectName = trim((string)($input['project_name'] ?? ''));
                $featureName = trim((string)($input['feature_name'] ?? ''));
                $description = trim((string)($input['description'] ?? ''));
                $docUrl = trim((string)($input['doc_url'] ?? ''));
                $status = in_array($input['status'] ?? '', ['Planned', 'In Progress', 'Done'], true) ? $input['status'] : 'Done';

                if (empty($projectName) || empty($featureName)) {
                    jsonResponse(['error' => 'Nama proyek dan nama fitur wajib diisi.'], 400);
                }

                $stmtInsert = $db->prepare('
                    INSERT INTO features (project_name, feature_name, description, doc_url, status, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ');
                $stmtInsert->execute([$projectName, $featureName, $description, $docUrl, $status]);
                $featId = (int)$db->lastInsertId();

                $stmtGet = $db->prepare('SELECT * FROM features WHERE id = ?');
                $stmtGet->execute([$featId]);

                jsonResponse([
                    'ok'      => true,
                    'message' => 'Fitur berhasil ditambahkan.',
                    'feature' => $stmtGet->fetch()
                ], 201);
            }

            if ($method === 'DELETE') {
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) {
                    jsonResponse(['error' => 'ID fitur tidak valid.'], 400);
                }

                $stmtFind = $db->prepare('SELECT * FROM features WHERE id = ?');
                $stmtFind->execute([$id]);
                $feat = $stmtFind->fetch();

                if (!$feat) {
                    jsonResponse(['error' => 'Fitur tidak ditemukan.'], 404);
                }

                $stmtDel = $db->prepare('DELETE FROM features WHERE id = ?');
                $stmtDel->execute([$id]);

                jsonResponse([
                    'ok'      => true,
                    'message' => 'Fitur berhasil dihapus.',
                    'feature' => $feat
                ]);
            }

            jsonResponse(['error' => 'Metode HTTP tidak didukung untuk action features.'], 405);
            break;

        /* -----------------------------------------------------------------
         * 6. TIMETABLES (ROADMAP BULANAN IT)
         * ----------------------------------------------------------------- */
        case 'timetables':
        case 'timetable':
            if ($method === 'GET') {
                $projectName = trim((string)($input['project'] ?? ''));
                $stmt = $db->prepare('SELECT * FROM timetables WHERE project_name = ? ORDER BY target_month ASC, id ASC');
                $stmt->execute([$projectName]);
                jsonResponse(['timetable' => $stmt->fetchAll()]);
            }

            if ($method === 'POST') {
                // UPDATE STATUS JIKA SUB=STATUS
                if (($input['sub'] ?? '') === 'status') {
                    $id = (int)($input['id'] ?? 0);
                    $status = trim((string)($input['status'] ?? 'Planned'));

                    $valid = ['Planned', 'In Progress', 'Done'];
                    if (!in_array($status, $valid, true)) {
                        jsonResponse(['error' => 'Status roadmap tidak valid.'], 400);
                    }

                    $stmtUp = $db->prepare('UPDATE timetables SET status = ?, updated_at = NOW() WHERE id = ?');
                    $stmtUp->execute([$status, $id]);

                    $stmtGet = $db->prepare('SELECT * FROM timetables WHERE id = ?');
                    $stmtGet->execute([$id]);

                    jsonResponse(['ok' => true, 'task' => $stmtGet->fetch()]);
                }

                // CREATE TASK BARU
                $projectName = trim((string)($input['project_name'] ?? ''));
                $taskName = trim((string)($input['task_name'] ?? ''));
                $targetMonth = trim((string)($input['target_month'] ?? 'Oktober 2026'));
                $status = in_array($input['status'] ?? '', ['Planned', 'In Progress', 'Done'], true) ? $input['status'] : 'Planned';

                if (empty($projectName) || empty($taskName)) {
                    jsonResponse(['error' => 'Nama proyek dan nama task pekerjaan wajib diisi.'], 400);
                }

                $stmtInsert = $db->prepare('
                    INSERT INTO timetables (project_name, task_name, target_month, status, updated_at)
                    VALUES (?, ?, ?, ?, NOW())
                ');
                $stmtInsert->execute([$projectName, $taskName, $targetMonth, $status]);
                $taskId = (int)$db->lastInsertId();

                $stmtGet = $db->prepare('SELECT * FROM timetables WHERE id = ?');
                $stmtGet->execute([$taskId]);

                jsonResponse([
                    'ok'   => true,
                    'task' => $stmtGet->fetch()
                ], 201);
            }

            if ($method === 'PATCH' || $method === 'PUT') {
                $id = (int)($input['id'] ?? 0);
                $status = trim((string)($input['status'] ?? 'Planned'));

                $valid = ['Planned', 'In Progress', 'Done'];
                if (!in_array($status, $valid, true)) {
                    jsonResponse(['error' => 'Status roadmap tidak valid.'], 400);
                }

                $stmtUp = $db->prepare('UPDATE timetables SET status = ?, updated_at = NOW() WHERE id = ?');
                $stmtUp->execute([$status, $id]);

                $stmtGet = $db->prepare('SELECT * FROM timetables WHERE id = ?');
                $stmtGet->execute([$id]);

                jsonResponse(['ok' => true, 'task' => $stmtGet->fetch()]);
            }

            if ($method === 'DELETE') {
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) {
                    jsonResponse(['error' => 'ID task tidak valid.'], 400);
                }

                $stmtFind = $db->prepare('SELECT * FROM timetables WHERE id = ?');
                $stmtFind->execute([$id]);
                $task = $stmtFind->fetch();

                if (!$task) {
                    jsonResponse(['error' => 'Task tidak ditemukan.'], 404);
                }

                $stmtDel = $db->prepare('DELETE FROM timetables WHERE id = ?');
                $stmtDel->execute([$id]);

                jsonResponse([
                    'ok'   => true,
                    'task' => $task
                ]);
            }

            jsonResponse(['error' => 'Metode HTTP tidak didukung untuk action timetables.'], 405);
            break;

        /* -----------------------------------------------------------------
         * 7. CHANGELOGS (MANUAL INSERT)
         * ----------------------------------------------------------------- */
        case 'changelogs':
            if ($method === 'POST') {
                $projectName = trim((string)($input['project_name'] ?? ''));
                $commitMessage = trim((string)($input['commit_message'] ?? ''));
                $commitType = in_array($input['commit_type'] ?? '', ['fitur', 'bugfix', 'update'], true)
                    ? $input['commit_type']
                    : classifyCommit($commitMessage);

                if (empty($projectName) || empty($commitMessage)) {
                    jsonResponse(['error' => 'Nama proyek dan pesan commit wajib diisi.'], 400);
                }

                $stmtInsert = $db->prepare('
                    INSERT INTO changelogs (project_name, commit_message, commit_type, created_at)
                    VALUES (?, ?, ?, NOW())
                ');
                $stmtInsert->execute([$projectName, $commitMessage, $commitType]);
                $logId = (int)$db->lastInsertId();

                $stmtGet = $db->prepare('SELECT * FROM changelogs WHERE id = ?');
                $stmtGet->execute([$logId]);

                jsonResponse(['ok' => true, 'changelog' => $stmtGet->fetch()], 201);
            }

            jsonResponse(['error' => 'Metode HTTP tidak didukung untuk action changelogs.'], 405);
            break;

        /* -----------------------------------------------------------------
         * 8. GITHUB WEBHOOK (AUTO RECEIVE COMMITS)
         * ----------------------------------------------------------------- */
        case 'webhook_github':
        case 'webhook':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Webhook hanya menerima metode POST.'], 405);
            }

            $payload = $jsonBody;
            if (empty($payload)) {
                jsonResponse(['error' => 'Payload JSON webhook kosong.'], 400);
            }

            $rawRepo = $payload['repository']['name'] ?? '';
            $repoName = parseRepoName($rawRepo);

            if (empty($repoName)) {
                jsonResponse(['error' => 'Nama repositori tidak ditemukan dalam webhook.'], 400);
            }

            // Pastikan proyek terdaftar
            $stmtProj = $db->prepare('SELECT id, name, client_name FROM projects WHERE name = ?');
            $stmtProj->execute([$repoName]);
            $project = $stmtProj->fetch();

            if (!$project) {
                jsonResponse(['error' => "Repositori \"{$repoName}\" belum terdaftar di Jarvis Monitor."], 404);
            }

            $commits = $payload['commits'] ?? [];
            if (!is_array($commits)) {
                $commits = [];
            }

            $inserted = 0;
            $teleLines = [];

            $stmtIns = $db->prepare('
                INSERT INTO changelogs (project_name, commit_message, commit_type, created_at)
                VALUES (?, ?, ?, ?)
            ');

            foreach ($commits as $c) {
                $msg = trim((string)($c['message'] ?? ''));
                if (empty($msg)) {
                    continue;
                }

                $type = classifyCommit($msg);
                $ts = !empty($c['timestamp']) ? date('Y-m-d H:i:s', strtotime($c['timestamp'])) : date('Y-m-d H:i:s');

                $stmtIns->execute([$repoName, $msg, $type, $ts]);
                $inserted++;

                $icon = match ($type) {
                    'fitur'  => '✨',
                    'bugfix' => '🐛',
                    default  => '⚡',
                };
                $teleLines[] = "{$icon} " . htmlspecialchars($msg);
            }

            // Kirim ringkasan ke Telegram jika ada commit baru
            if ($inserted > 0) {
                $teleMsg = "🚀 <b>{$inserted} Commit Baru: {$repoName}</b>\n\n" . implode("\n", array_slice($teleLines, 0, 5));
                if (count($teleLines) > 5) {
                    $teleMsg .= "\n<i>...dan " . (count($teleLines) - 5) . " commit lainnya</i>";
                }
                sendTelegramNotification($teleMsg);
            }

            jsonResponse([
                'ok'             => true,
                'project_name'   => $repoName,
                'commits_saved'  => $inserted,
            ]);
            break;

        default:
            jsonResponse([
                'error'   => 'Action tidak ditemukan atau endpoint tidak valid.',
                'action'  => $action,
            ], 404);
            break;
    }
} catch (Throwable $e) {
    jsonResponse([
        'error'   => 'Terjadi kesalahan pada server backend PHP: ' . $e->getMessage(),
        'action'  => $action,
    ], 500);
}
