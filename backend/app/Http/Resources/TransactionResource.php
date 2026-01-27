<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date->format('Y-m-d'),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'wallet' => new WalletResource($this->whenLoaded('wallet')),
            'reference' => $this->reference,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
