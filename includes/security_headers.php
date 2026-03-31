<?php
/**
 * Security headers
 */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Only add CSP if not already set
if (!headers_sent() && !isset($GLOBALS['csp_set'])) {
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.buymeacoffee.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' data: https://fonts.gstatic.com; connect-src 'self' https://api.github.com https://maker.ifttt.com https://nominatim.openstreetmap.org;");
    $GLOBALS['csp_set'] = true;
}
