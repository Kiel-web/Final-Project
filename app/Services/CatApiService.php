<?php
namespace App\Services;

use Core\Cache;
use Core\Config;
use Core\HttpClient;
use Core\Logger;

class CatApiService
{
    private HttpClient $http;
    private array $config;
    private ?string $lastError = null;

    public function __construct()
    {
        $this->config = Config::get('services.cat_api');
        $this->http   = new HttpClient([
            'timeout'   => $this->config['timeout'],
            'ca_bundle' => $this->config['ca_bundle'],
        ]);
    }

    public function lastError(): ?string
    {
        return $this->lastError;
    }

    public function breeds(): array
    {
        $breeds = $this->cached('cat_breeds', $this->config['ttl_breeds'], function () {
            $data = $this->request('/breeds');
            return (is_array($data) && isset($data[0]['id'])) ? $data : null;
        });

        return is_array($breeds) ? $breeds : [];
    }

    public function breedOptions(): array
    {
        $options = [];
        foreach ($this->breeds() as $breed) {
            $options[$breed['id']] = $breed['name'];
        }
        if (empty($options)) {
            $options = $this->config['fallback_breeds'];
        }
        asort($options);
        return $options;
    }

    public function breed(string $breedId): ?array
    {
        foreach ($this->breeds() as $breed) {
            if ($breed['id'] === $breedId) {
                return $breed;
            }
        }
        return null;
    }

    public function photoForBreed(string $breedId): ?array
    {
        $data = $this->request('/images/search', ['breed_ids' => $breedId, 'limit' => 1]);
        return $this->firstImage($data);
    }

    public function catOfTheDay(): ?array
    {
        $key = 'cat_of_the_day_' . date('Y-m-d');

        return $this->cached($key, $this->config['ttl_daily'], function () {
            $data  = $this->request('/images/search', ['limit' => 1]);
            $image = $this->firstImage($data);
            if ($image === null) {
                return null;
            }
            $image['breed'] = $data[0]['breeds'][0]['name'] ?? null;
            return $image;
        });
    }

    private function firstImage($data): ?array
    {
        if (is_array($data) && isset($data[0]['url'])) {
            return ['url' => $data[0]['url']];
        }
        if (is_array($data) && $this->lastError === null) {
            $this->lastError = 'No photo found for this request.';
        }
        return null;
    }

    private function cached(string $key, int $ttl, callable $fetch)
    {
        $hit = Cache::get($key);
        if ($hit !== null) {
            return $hit;
        }

        $fresh = $fetch();
        if ($fresh !== null) {
            Cache::put($key, $fresh, $ttl);
            return $fresh;
        }

        return Cache::stale($key);
    }

    private function request(string $path, array $query = []): ?array
    {
        $this->lastError = null;

        if (Cache::get($this->pauseKey()) !== null) {
            $this->lastError = 'The Cat API is temporarily unavailable.';
            return null;
        }

        $headers = $this->config['key'] !== '' ? ['x-api-key' => $this->config['key']] : [];
        $result  = $this->http->get($this->config['url'] . $path, $query, $headers);

        if ($result['ok']) {
            return $result['json'];
        }

        $status = $result['status'];

        if ($status === 401 || $status === 403) {
            $this->lastError = 'The Cat API key is missing or invalid.';
            $this->pause(300);
        } elseif ($status === 429) {
            $this->lastError = 'Cat API rate limit reached - showing saved data.';
            $this->pause(120);
        } elseif ($status === 0 || $status >= 500) {
            $this->lastError = 'Could not reach the Cat API (' . ($result['error'] ?? 'network error') . ').';
            $this->pause(60);
        } else {
            $this->lastError = 'Cat API answered: ' . ($result['error'] ?? 'error');
        }

        Logger::warning('Cat API call failed: ' . $path, ['status' => $status, 'error' => $result['error']]);
        return null;
    }

    private function pause(int $seconds): void
    {
        Cache::put($this->pauseKey(), 1, $seconds);
    }

    public function pauseKey(): string
    {
        return 'cat_api_paused_' . substr(md5($this->config['url'] . '|' . $this->config['key']), 0, 10);
    }
}
