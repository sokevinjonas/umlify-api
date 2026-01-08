<?php

namespace App\Services\IA;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    private Client $client;
    private string $apiKey;
    private string $model;
    private string $apiVersion;
    private int $timeout;

    public function __construct()
    {
        $config = config('services.anthropic');

        $this->apiKey = $config['api_key'];
        $this->model = $config['model'];
        $this->apiVersion = $config['api_version'];
        $this->timeout = $config['timeout'];

        $this->client = new Client([
            'base_uri' => $config['base_uri'],
            'timeout' => $this->timeout,
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->apiVersion,
                'content-type' => 'application/json',
            ]
        ]);
    }

    public function sendMessage(string $prompt, ?int $timeout = null): string
    {
        $startTime = microtime(true);

        try {
            $payload = [
                'model' => $this->model,
                'max_tokens' => 4096,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ];

            Log::info('Claude API call started', [
                'prompt_length' => strlen($prompt),
                'timeout' => $timeout ?? $this->timeout
            ]);

            $response = $this->client->post('messages', [
                'json' => $payload,
                'timeout' => $timeout ?? $this->timeout
            ]);

            $body = json_decode($response->getBody(), true);

            if (!isset($body['content'][0]['text'])) {
                throw new \RuntimeException('Invalid response structure from Claude API');
            }

            $result = $body['content'][0]['text'];
            $duration = round((microtime(true) - $startTime) * 1000);

            Log::info('Claude API call completed', [
                'duration_ms' => $duration,
                'response_length' => strlen($result)
            ]);

            return $result;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 429) {
                Log::warning('Claude API rate limit hit, retrying after 1 second');
                sleep(1);
                return $this->sendMessage($prompt, $timeout);
            }

            Log::error('Claude API client error', [
                'status_code' => $statusCode,
                'message' => $e->getMessage()
            ]);

            throw new \RuntimeException('Claude API error: ' . $e->getMessage());

        } catch (GuzzleException $e) {
            Log::error('Claude API request failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \RuntimeException('Claude API request failed: ' . $e->getMessage());
        }
    }
}
