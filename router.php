<?php
/**
 * Router for PHP's built-in server (start-server.bat).
 * Blocks direct downloads of local secret files, then serves everything else normally.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rawurldecode((string)$path);

if ($path === '/router.php'
    || preg_match('#(^|/)\.env$#', $path)
    || preg_match('#(^|/)\.git(/|$)#', $path)
    || preg_match('#(^|/)\.htaccess$#', $path)) {
    http_response_code(404);
    exit;
}

// Let the built-in server serve PHP files and static assets as usual.
return false;
