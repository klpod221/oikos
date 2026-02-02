<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Transaction
 *
 * @property int $id
 * @property int $user_id
 * @property int $wallet_id
 * @property int $category_id
 * @property string $type
 * @property float $amount
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $transaction_date
 * @property string|null $reference
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read Wallet $wallet
 * @property-read Category $category
 *
 * @package App\Models
 */
class Transaction extends Model
{
    use HasFactory;
    use \App\Traits\Filterable;

    /**
     * Type constants
     */
    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'wallet_id',
        'category_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'reference',
        'metadata',
        'gmail_message_id',
    ];

    /**
     * Fields that can be filtered
     *
     * @var array
     */
    public $filterable = [
        'type',
        'wallet_id',
        'category_id',
        'start_date' => 'transaction_date:>=',
        'end_date' => 'transaction_date:<=',
    ];

    /**
     * Fields that can be sorted
     *
     * @var array
     */
    public $sortable = [
        'transaction_date',
        'amount',
        'created_at',
    ];

    /**
     * Fields that can be searched
     *
     * @var array
     */
    public $searchable = [
        'description',
        'category.name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
            'metadata' => 'array',
        ];
    }

    // ===== Relationships =====

    /**
     * Transaction belongs to a user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transaction belongs to a wallet
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Transaction belongs to a category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ===== Query Scopes =====

    /**
     * Scope to get income transactions
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    /**
     * Scope to get expense transactions
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by wallet
     */
    public function scopeForWallet(Builder $query, int $walletId): Builder
    {
        return $query->where('wallet_id', $walletId);
    }

    // ===== Helper Methods =====

    /**
     * Check if transaction is income
     */
    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    /**
     * Check if transaction is expense
     */
    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }
}
