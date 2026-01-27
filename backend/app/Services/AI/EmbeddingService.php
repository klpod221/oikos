<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for generating text embeddings via HuggingFace TEI.
 */
class EmbeddingService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.tei.url', 'http://localhost:8080');
        $this->timeout = config('services.tei.timeout', 30);
    }

    /**
     * Generate embedding for a single text.
     *
     * @param string $text
     * @return array<float>
     * @throws \RuntimeException
     */
    public function embed(string $text): array
    {
        return $this->embedBatch([$text])[0];
    }

    /**
     * Generate embeddings for multiple texts.
     *
     * @param array<string> $texts
     * @return array<array<float>>
     * @throws \RuntimeException
     */
    public function embedBatch(array $texts): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/embed", [
                    'inputs' => $texts,
                ]);

            if (!$response->successful()) {
                Log::error('TEI embedding failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException("Embedding service error: {$response->status()}");
            }

            return $response->json();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('TEI connection failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Embedding service unavailable');
        }
    }

    /**
     * Check if the embedding service is healthy.
     *
     * @return bool
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get embedding dimension (for bge-m3: 1024).
     *
     * @return int
     */
    public function getDimension(): int
    {
        return 1024; // bge-m3 dimension
    }
}
