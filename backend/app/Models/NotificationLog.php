<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_name',
        'title',
        'content',
        'hash',
        'status',
        'result',
        'error',
    ];

    /**
     * Get the user that owns the notification log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
