<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'fee' => (float) $this->fee,
            'balance_before' => (float) $this->balance_before,
            'balance_after' => (float) $this->balance_after,
            'status' => $this->status,
            'currency' => $this->currency,
            'from_user' => $this->whenLoaded('fromUser', fn() => new UserResource($this->fromUser)),
            'to_user' => $this->whenLoaded('toUser', fn() => new UserResource($this->toUser)),
            'description' => $this->description,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
