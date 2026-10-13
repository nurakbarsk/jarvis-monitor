<?php
/**
 * Jarvis Monitor — Konfigurasi Database & Layanan
 * Subfolder Deployment: /monitor/ (https://matla.id/monitor/)
 */

declare(strict_types=1);

// Set timezone default (WIB / UTC+7)
// Auto-load .env jika tersedia (memudahkan testing di lokal tanpa mengubah config produksi)
if (file_exists(__DIR__ . '/.env')) {
    $envLines = @file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($envLines as $envLine) {
        $envLine = trim($envLine);
        if ($envLine === '' || str_starts_with($envLine, '#')) {
            continue;
        }
        if (str_contains($envLine, '=')) {
            [$k, $v] = explode('=', $envLine, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            putenv("{$k}={$v}");
            $_ENV[$k] = $v;
        }
    }
}

// Kredensial Database MySQL / MariaDB (Dapat dioverride via .env di lokal atau diset default untuk hosting)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'cnnelcmz_jarvis');
define('DB_USER', getenv('DB_USER') ?: 'cnnelcmz_jarvis');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));

// Kredensial Telegram Bot
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: '8681632478:AAFHhc-GOVOGUHS133Xd70jixvoKR1llfwI');
define('TELEGRAM_CHAT_ID', getenv('TELEGRAM_CHAT_ID') ?: '1142103262');

// Mulai session PHP jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi PIN Autentikasi Admin Developer (Default: 260704)
define('ADMIN_PIN', getenv('ADMIN_PIN') ?: '260704');

// Path penyimpanan berkas screenshot upload (di dalam folder public untuk web akses)
define('UPLOAD_DIR', is_dir(__DIR__ . '/public/uploads') ? __DIR__ . '/public/uploads/' : __DIR__ . '/public/uploads/');
define('UPLOAD_URL', 'uploads/'); // Relative URL untuk browser dari document root /public

/**
 * Cek apakah session admin telah diautentikasi dengan PIN
 *
 * @return bool
 */
function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Singleton koneksi PDO MySQL
 *
 * @return PDO
 */
function getDb(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Koneksi database MySQL gagal: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    return $pdo;
}

/**
 * Kirim notifikasi pesan atau foto langsung ke Telegram Bot via cURL native
 *
 * @param string $message Format teks atau HTML
 * @param string|null $imagePath Path absolut berkas gambar di server (jika ada)
 * @return bool
 */
function sendTelegramNotification(string $message, ?string $imagePath = null): bool
{
    $token = trim(TELEGRAM_BOT_TOKEN);
    $chatId = trim(TELEGRAM_CHAT_ID);

    if (empty($token) || empty($chatId)) {
        return false;
    }

    // 1. Jika ada lampiran gambar dan berkasnya ada di server, kirim menggunakan sendPhoto
    if (!empty($imagePath) && file_exists($imagePath) && is_file($imagePath)) {
        $url = "https://api.telegram.org/bot{$token}/sendPhoto";
        $caption = mb_substr($message, 0, 1020);

        $mime = @mime_content_type($imagePath) ?: 'image/jpeg';
        $cFile = new CURLFile($imagePath, $mime, basename($imagePath));

        $payload = [
            'chat_id'    => $chatId,
            'photo'      => $cFile,
            'caption'    => $caption,
            'parse_mode' => 'HTML',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }
        // Jika gagal mengirim foto (misal ukuran terlalu besar/format), fallback ke text biasa di bawah
    }

    // 2. Kirim pesan teks biasa (sendMessage)
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $payload = [
        'chat_id'                  => $chatId,
        'text'                     => $message,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => false,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 6,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 300;
}

/**
 * Response JSON terstandarisasi
 *
 * @param mixed $data
 * @param int $statusCode
 * @return void
 */
function jsonResponse($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Auto-detect / parse nama repositori dari URL GitHub
 *
 * @param string $urlOrName
 * @return string
 */
function parseRepoName(string $urlOrName): string
{
    $trimmed = trim($urlOrName);
    if (empty($trimmed)) {
        return '';
    }

    $trimmed = rtrim($trimmed, '/');
    if (str_ends_with($trimmed, '.git')) {
        $trimmed = substr($trimmed, 0, -4);
    }

    if (preg_match('#[/:]+([^/:]+)$#', $trimmed, $matches)) {
        return trim($matches[1]);
    }

    return $trimmed;
}

/**
 * Klasifikasi prefix pesan commit GitHub
 * - fitur: feat, feature, fitur, add, new, tambah
 * - bugfix: fix, bug, error, patch, resolve, perbaikan
 * - update: lainnya (refactor, chore, docs, update, dll)
 *
 * @param string $message
 * @return string ('fitur' | 'bugfix' | 'update')
 */
function classifyCommit(string $message): string
{
    $lower = strtolower(trim($message));

    // Cek keyword fitur
    if (preg_match('/\b(feat|feature|fitur|add|new|tambah)\b|\[(feat|feature|fitur|add|new|tambah)\]/i', $lower)) {
        return 'fitur';
    }

    // Cek keyword bugfix
    if (preg_match('/\b(fix|bug|error|patch|resolve|perbaikan)\b|\[(fix|bug|error|patch|resolve|perbaikan)\]/i', $lower)) {
        return 'bugfix';
    }

    return 'update';
}

/**
 * Ekstrak owner dan repo dari URL GitHub atau string 'owner/repo'
 *
 * @param string $urlOrName
 * @return array{0: string, 1: string}|null [owner, repo]
 */
function parseGitHubOwnerRepo(string $urlOrName): ?array
{
    $trimmed = trim($urlOrName);
    if (empty($trimmed)) {
        return null;
    }

    $trimmed = rtrim($trimmed, '/');
    if (str_ends_with($trimmed, '.git')) {
        $trimmed = substr($trimmed, 0, -4);
    }

    // Format git@github.com:owner/repo atau https://github.com/owner/repo
    if (preg_match('#(?:github\.com[:/]|/|^)([^/:\s]+)/([^/:\s]+)$#i', $trimmed, $m)) {
        return [trim($m[1]), trim($m[2])];
    }

    return null;
}
