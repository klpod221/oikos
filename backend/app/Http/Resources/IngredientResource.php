<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'unit' => $this->unit,
            'is_global' => $this->is_global,
            'calories' => $this->calories,
            'protein' => $this->protein,
            'carbs' => $this->carbs,
            'fat' => $this->fat,
            'fiber' => $this->fiber,
            'sugar' => $this->sugar,
            'image' => $this->image,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'pivot' => $this->whenPivotLoaded('recipe_ingredients', function () {
                return [
                    'quantity' => $this->pivot->quantity,
                    'unit' => $this->pivot->unit,
                ];
            }),
        ];
    }
}
