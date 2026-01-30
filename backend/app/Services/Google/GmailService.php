<?php

namespace App\Services\Google;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailService
{
    private const GMAIL_API_BASE = 'https://gmail.googleapis.com/gmail/v1/users/me';
    private const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';

    /**
     * Get the latest bank notification emails.
     * Use simple query to find emails from common banks or with specific keywords.
     */
    /**
     * Get the latest bank notification emails.
     */
    public function getBankEmails(User $user, int $limit = 10, ?\Carbon\Carbon $since = null, ?string $pageToken = null)
    {
        // Get query from System Settings or fallback
        $query = \App\Models\SystemSetting::getValue('gmail_search_query', 'subject:(biến động số dư) OR subject:(giao dịch) OR subject:(transaction) OR subject:(biên lai) OR subject:(transfer) OR subject:(receipt) -category:promotions -category:social');

        // Append timestamp filter if provided to optimize query
        if ($since) {
            $query .= ' after:' . $since->timestamp;
        }

        $params = [
            'q' => $query,
            'maxResults' => $limit,
        ];

        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $list = $this->callApi($user, 'GET', '/messages', $params);

        if (!isset($list['messages'])) {
            return [
                'messages' => [],
                'nextPageToken' => null
            ];
        }

        $emails = [];
        foreach ($list['messages'] as $msg) {
            // Check if transaction with this Message ID already exists to avoid API call
            // We rely on deduplication in the calling command, but checking here saves API quota.
            // However, ensuring we don't return "empty" prematurely requires care.

            if (\App\Models\Transaction::where('gmail_message_id', $msg['id'])->exists()) {
                continue;
            }

            $details = $this->callApi($user, 'GET', "/messages/{$msg['id']}");
            if ($details) {
                // Pass msg['id'] explicitly as it is the stable ID
                $parsed = $this->parseEmail($details);
                if ($parsed) {
                    $parsed['message_id'] = $msg['id']; // Ensure ID is passed
                    $emails[] = $parsed;
                }
            }
        }

        return [
            'messages' => $emails,
            'nextPageToken' => $list['nextPageToken'] ?? null
        ];
    }

    /**
     * Parse raw email response to extract body and snippet.
     */
    private function parseEmail(array $data)
    {
        $payload = $data['payload'] ?? [];
        $headers = collect($payload['headers'] ?? []);

        $subject = $headers->firstWhere('name', 'Subject')['value'] ?? '(No Subject)';
        $from = $headers->firstWhere('name', 'From')['value'] ?? '(Unknown)';
        $date = $headers->firstWhere('name', 'Date')['value'] ?? now();

        // Decode body (prefer snippet for list views, but get full body for parsing)
        $snippet = $data['snippet'] ?? '';

        // Simple body extraction (works for plain text parts, HTML might need more logic)
        $body = '';
        if (isset($payload['body']['data'])) {
            $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload['body']['data']));
        } elseif (isset($payload['parts'])) {
            foreach ($payload['parts'] as $part) {
                if ($part['mimeType'] === 'text/plain' && isset($part['body']['data'])) {
                    $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $part['body']['data']));
                    break;
                }
            }
        }

        return [
            'id' => $data['id'],
            'subject' => $subject,
            'from' => $from,
            'date' => $date,
            'snippet' => $snippet,
            'body' => $body,
        ];
    }

    /**
     * Call Gmail API handling token refresh.
     */
    private function callApi(User $user, string $method, string $endpoint, array $params = [])
    {
        if ($this->shouldRefreshToken($user)) {
            $this->refreshToken($user);
        }

        $url = self::GMAIL_API_BASE . $endpoint;

        $response = Http::withToken($user->google_access_token)
            ->$method($url, $params);

        if ($response->status() === 401) {
            // Token might be invalid despite expiration check, try refreshing once
            Log::warning("Gmail API 401 for user {$user->id}, attempting force refresh.");
            $this->refreshToken($user);
            $response = Http::withToken($user->google_access_token)
                ->$method($url, $params);
        }

        if (!$response->successful()) {
            Log::error("Gmail API Error: " . $response->body());
            return null;
        }

        return $response->json();
    }

    /**
     * Check if token needs refresh.
     */
    private function shouldRefreshToken(User $user): bool
    {
        if (!$user->google_access_token) {
            return true;
        }
        if (!$user->google_token_expires_at) {
            return true;
        }

        // Refresh if expired or expiring in less than 5 minutes
        return $user->google_token_expires_at->lt(now()->addMinutes(5));
    }

    /**
     * Refresh OAuth access token.
     */
    private function refreshToken(User $user): void
    {
        if (!$user->google_refresh_token) {
            throw new \Exception("User {$user->id} does not have a refresh token.");
        }

        $response = Http::asForm()->post(self::TOKEN_ENDPOINT, [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $user->google_refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $user->update([
                'google_access_token' => $data['access_token'],
                'google_token_expires_at' => now()->addSeconds($data['expires_in']),
            ]);
        } else {
            Log::error("Failed to refresh Google Token for user {$user->id}: " . $response->body());
            throw new \Exception("Could not refresh Google Token. Please re-login.");
        }
    }
}
