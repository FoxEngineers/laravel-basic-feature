<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Payment $resource
 */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'invoice_number' => $this->resource->invoice_number,
            'amount' => $this->resource->amount,
            'status' => $this->resource->status,
            'created_date' => $this->resource->created_date,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
