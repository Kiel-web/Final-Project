<?php
namespace Core;

class HttpClient
{
    private int $timeout;
    private int $connectTimeout;
    private ?string $caBundle;
    private array $defaultHeaders;

    public function __construct(array $options = [])
    {
        $this->timeout        = (int) ($options['timeout'] ?? 5);
        $this->connectTimeout = (int) ($options['connect_timeout'] ?? 3);
        $this->caBundle       = $options['ca_bundle'] ?? null;
        $this->defaultHeaders = $options['headers'] ?? [];
    }

    public function get(string $url, array $query = [], array $headers = []): array
    {
        if (!empty($query)) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        $result = ['ok' => false, 'status' => 0, 'body' => '', 'json' => null, 'error' => null];

        if (!function_exists('curl_init')) {
            $result['error'] = 'The PHP cURL extension is not enabled.';
            return $result;
        }

        $allHeaders = [];
        foreach (array_merge(['Accept' => 'application/json'], $this->defaultHeaders, $headers) as $name => $value) {
            $allHeaders[] = $name . ': ' . $value;
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_HTTPHEADER     => $allHeaders,
            CURLOPT_USERAGENT      => 'WhiskerHub/1.0 (student project)',
        ]);
        if ($this->caBundle) {
            curl_setopt($curl, CURLOPT_CAINFO, $this->caBundle);
        }

        $body = curl_exec($curl);

        if ($body === false) {
            $result['error'] = curl_error($curl) ?: 'Unknown network error';
            curl_close($curl);
            return $result;
        }

        $result['status'] = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        $result['body'] = (string) $body;
        $result['json'] = json_decode($result['body'], true);
        $result['ok']   = $result['status'] >= 200 && $result['status'] < 300;

        if (!$result['ok']) {
            $result['error'] = 'HTTP ' . $result['status'];
        } elseif ($result['json'] === null && trim($result['body']) !== 'null') {
            $result['ok']    = false;
            $result['error'] = 'Response was not valid JSON';
        }

        return $result;
    }
}
