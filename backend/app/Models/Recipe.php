<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Recipe
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string|null $instructions
 * @property int|null $prep_time
 * @property int|null $cooking_time
 * @property int|null $servings
 * @property string|null $image
 * @property float|null $calories
 * @property float|null $protein
 * @property float|null $carbs
 * @property float|null $fat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|Ingredient[] $ingredients
 *
 * @package App\Models
 */
class Recipe extends Model
{
    use HasFactory;
    use \App\Traits\Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'instructions',
        'prep_time',
        'cooking_time',
        'servings',
        'image',
        'calories',
        'protein',
        'carbs',
        'fat',
    ];

    /**
     * Fields that can be filtered
     *
     * @var array
     */
    public $filterable = [];

    /**
     * Fields that can be sorted
     *
     * @var array
     */
    public $sortable = ['name', 'prep_time', 'cooking_time', 'created_at'];

    /**
     * Fields that can be searched
     *
     * @var array
     */
    public $searchable = ['name', 'description'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'prep_time' => 'integer',
        'cooking_time' => 'integer',
        'servings' => 'integer',
        'calories' => 'decimal:2',
        'protein' => 'decimal:2',
        'carbs' => 'decimal:2',
        'fat' => 'decimal:2',
    ];

    /**
     * Get the user that owns the recipe.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ingredients for the recipe.
     *
     * @return BelongsToMany
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot('quantity', 'unit')
            ->withTimestamps();
    }
}
