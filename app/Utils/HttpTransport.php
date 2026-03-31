<?php
namespace App\Utils;

/**
 * Minimal HTTP client: uses cURL when available, otherwise PHP streams (allow_url_fopen).
 */
class HttpTransport {

    /**
     * True for localhost / loopback hosts (typical dev HTTPS with self-signed certs).
     */
    public static function isLocalDevUrl(string $url): bool {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host === null || $host === '') {
            return false;
        }
        $h = strtolower(trim($host, '[]'));
        return $h === 'localhost' || $h === '127.0.0.1' || $h === '::1';
    }

    /**
     * @param string $method GET or POST
     * @param array $headers Lines like "Name: value"
     * @param string|null $body Raw body (e.g. JSON) for POST
     * @return array{body: string|false, http_code: int, error: string}
     */
    public static function request(
        string $url,
        string $method,
        array $headers,
        ?string $body,
        int $timeoutSeconds = 15,
        bool $insecureSsl = false
    ): array {
        $method = strtoupper($method);
        if (function_exists('curl_init')) {
            return self::viaCurl($url, $method, $headers, $body, $timeoutSeconds, $insecureSsl);
        }
        return self::viaStream($url, $method, $headers, $body, $timeoutSeconds, $insecureSsl);
    }

    /**
     * @return array{body: string|false, http_code: int, error: string}
     */
    private static function viaCurl(
        string $url,
        string $method,
        array $headers,
        ?string $body,
        int $timeoutSeconds,
        bool $insecureSsl
    ): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$insecureSsl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $insecureSsl ? 0 : 2);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body ?? '');
        }
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        if ($response === false) {
            return ['body' => false, 'http_code' => $httpCode, 'error' => $curlError ?: 'cURL request failed'];
        }
        return ['body' => $response, 'http_code' => $httpCode, 'error' => ''];
    }

    /**
     * @return array{body: string|false, http_code: int, error: string}
     */
    private static function viaStream(
        string $url,
        string $method,
        array $headers,
        ?string $body,
        int $timeoutSeconds,
        bool $insecureSsl
    ): array {
        if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
            return [
                'body' => false,
                'http_code' => 0,
                'error' => 'Weder cURL noch allow_url_fopen ist verfügbar. Bitte php-curl installieren oder in php.ini allow_url_fopen=On setzen.',
            ];
        }

        $http = [
            'method' => $method,
            'timeout' => $timeoutSeconds,
            'ignore_errors' => true,
            'follow_location' => 1,
            'max_redirects' => 5,
        ];
        if ($method === 'POST') {
            $http['content'] = $body ?? '';
        }

        $headerLines = $headers;
        $hasUa = false;
        foreach ($headerLines as $line) {
            if (stripos($line, 'user-agent:') === 0) {
                $hasUa = true;
                break;
            }
        }
        if (!$hasUa) {
            array_unshift($headerLines, 'User-Agent: Drohnen-Einsatztagebuch/1.0');
        }
        $http['header'] = implode("\r\n", $headerLines);

        $scheme = parse_url($url, PHP_URL_SCHEME);
        $opts = ['http' => $http];
        if ($scheme === 'https') {
            $opts['ssl'] = [
                'verify_peer' => !$insecureSsl,
                'verify_peer_name' => !$insecureSsl,
            ];
        }

        $ctx = stream_context_create($opts);
        $http_response_header = [];
        $response = @file_get_contents($url, false, $ctx);
        $httpCode = 0;
        if (!empty($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $httpCode = (int) $m[1];
        }
        if ($response === false) {
            $err = error_get_last();
            $msg = ($err && isset($err['message'])) ? $err['message'] : 'HTTP-Anfrage fehlgeschlagen';
            return ['body' => false, 'http_code' => $httpCode, 'error' => $msg];
        }
        return ['body' => $response, 'http_code' => $httpCode, 'error' => ''];
    }
}
