<?php
/**
 * Root Redirector
 * Fallback jika server lokal atau web server belum mengarahkan Document Root ke /public
 */
header('Location: public/');
exit;
