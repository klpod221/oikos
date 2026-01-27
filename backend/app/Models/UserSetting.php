<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserSetting
 *
 * @property int $id
 * @property int $user_id
 * @property string $currency
 * @property string $gold_unit
 * @property string $silver_unit
 * @property string $language
 * @property string $theme
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user
 *
 * @package App\Models
 */
class UserSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'currency',
        'gold_unit',
        'silver_unit',
        'language',
        'theme',
    ];

    /**
     * Get the user that owns the settings.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
