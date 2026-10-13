<?php
/**
 * Jarvis Monitor — Konfigurasi Database & Layanan
 * Subfolder Deployment: /monitor/ (https://matla.id/monitor/)
 */

declare(strict_types=1);

// Set timezone default (WIB / UTC+7)
date_default_timezone_set('Asia/Jakarta');

// Kredensial Database MySQL / MariaDB (Sesuaikan dengan data cPanel MySQL Anda)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'cnnelcmz_jarvis');
define('DB_USER', getenv('DB_USER') ?: 'cnnelcmz_jarvis');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));

// Kredensial Telegram Bot
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: '8681632478:AAFHhc-GOVOGUHS133Xd70jixvoKR1llfwI');
define('TELEGRAM_CHAT_ID', getenv('TELEGRAM_CHAT_ID') ?: '1142103262');

// Path penyimpanan berkas screenshot upload
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/'); // Relative URL untuk browser

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
 * Kirim notifikasi pesan ke Telegram Bot via cURL native
 *
 * @param string $message Format teks atau HTML
 * @return bool
 */
function sendTelegramNotification(string $message): bool
{
    $token = trim(TELEGRAM_BOT_TOKEN);
    $chatId = trim(TELEGRAM_CHAT_ID);

    if (empty($token) || empty($chatId)) {
        return false;
    }

    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $payload = [
        'chat_id'                  => $chatId,
        'text'                     => $message,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true,
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 5,
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
 *
 * @param string $message
 * @return string ('fitur' | 'bugfix' | 'update')
 */
function classifyCommit(string $message): string
{
    $lower = strtolower(trim($message));
    if (str_starts_with($lower, 'feat') || str_contains($lower, '[feat]')) {
        return 'fitur';
    }
    if (str_starts_with($lower, 'fix') || str_contains($lower, '[fix]')) {
        return 'bugfix';
    }
    return 'update';
}
