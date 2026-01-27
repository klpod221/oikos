<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Chat history message model.
 *
 * @property int $id
 * @property int $user_id
 * @property string $role
 * @property string $content
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ChatHistory extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the last N messages for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRecentMessages(int $userId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Convert to OpenAI message format.
     *
     * @return array
     */
    public function toOpenAIFormat(): array
    {
        $message = [
            'role' => $this->role,
            'content' => $this->content,
        ];

        if ($this->role === 'tool' && isset($this->metadata['tool_call_id'])) {
            $message['tool_call_id'] = $this->metadata['tool_call_id'];
        }

        if ($this->role === 'assistant' && isset($this->metadata['tool_calls'])) {
            $message['tool_calls'] = $this->metadata['tool_calls'];
        }

        return $message;
    }
}
