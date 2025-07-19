<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\AtkResource;

class KeranjangItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            // 'atk' adalah relasi dari KeranjangItem ke Atk
            // Kita ingin resource AtkResource memformat data atk
            'atk' => new AtkResource($this->whenLoaded('atk')),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}