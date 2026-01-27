<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for vector search operations via Qdrant.
 */
class VectorSearchService
{
    private string $baseUrl;
    private string $collection;
    private int $timeout;
    private float $scoreThreshold;
    private int $limit;

    public function __construct()
    {
        $this->baseUrl = config('services.qdrant.url', 'http://localhost:6333');
        $this->collection = config('services.qdrant.collection', 'oikos_knowledge');
        $this->timeout = config('services.qdrant.timeout', 10);
        $this->scoreThreshold = config('services.qdrant.score_threshold', 0.7);
        $this->limit = config('services.qdrant.limit', 5);
    }

    /**
     * Search for similar documents.
     *
     * @param array<float> $vector
     * @param array<string, mixed> $filter Optional filter conditions
     * @param int|null $limit Override default limit
     * @return array<array{id: string, score: float, payload: array}>
     */
    public function search(array $vector, array $filter = [], ?int $limit = null): array
    {
        try {
            $payload = [
                'vector' => $vector,
                'limit' => $limit ?? $this->limit,
                'score_threshold' => $this->scoreThreshold,
                'with_payload' => true,
            ];

            if (!empty($filter)) {
                $payload['filter'] = $filter;
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/collections/{$this->collection}/points/search", $payload);

            if (!$response->successful()) {
                Log::error('Qdrant search failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            return $response->json('result', []);
        } catch (\Exception $e) {
            Log::error('Qdrant search error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Upsert points into the collection.
     *
     * @param array<array{id: string|int, vector: array<float>, payload: array}> $points
     * @return bool
     */
    public function upsert(array $points): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->put("{$this->baseUrl}/collections/{$this->collection}/points", [
                    'points' => $points,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Qdrant upsert error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create collection if not exists.
     *
     * @param int $vectorSize
     * @return bool
     */
    public function ensureCollection(int $vectorSize = 1024): bool
    {
        try {
            // Check if collection exists
            $checkResponse = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/collections/{$this->collection}");

            if ($checkResponse->successful()) {
                return true; // Already exists
            }

            // Create collection
            $response = Http::timeout($this->timeout)
                ->put("{$this->baseUrl}/collections/{$this->collection}", [
                    'vectors' => [
                        'size' => $vectorSize,
                        'distance' => 'Cosine',
                    ],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Qdrant collection creation error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete points by IDs.
     *
     * @param array<string|int> $ids
     * @return bool
     */
    public function delete(array $ids): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/collections/{$this->collection}/points/delete", [
                    'points' => $ids,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Qdrant delete error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if Qdrant is healthy.
     *
     * @return bool
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/readyz");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Format search results as context string for LLM.
     *
     * @param array $results
     * @return string
     */
    public function formatAsContext(array $results): string
    {
        if (empty($results)) {
            return '';
        }

        $context = "Relevant information from knowledge base:\n\n";

        foreach ($results as $i => $result) {
            $content = $result['payload']['content'] ?? '';
            $source = $result['payload']['source'] ?? 'Unknown';
            $score = round($result['score'], 2);

            $context .= "---\n";
            $context .= "[Source: {$source}, Relevance: {$score}]\n";
            $context .= "{$content}\n";
        }

        return $context;
    }
}
