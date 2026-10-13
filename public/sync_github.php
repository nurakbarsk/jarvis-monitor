<?php
/**
 * Jarvis Monitor — Web Endpoint Proxy untuk Sync GitHub Commit
 * Meneruskan request ke script sync_github.php yang berada di luar folder public.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/sync_github.php';
