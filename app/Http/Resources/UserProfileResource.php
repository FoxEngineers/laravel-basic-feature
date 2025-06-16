<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserProfileResource extends UserResource
{
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'payments' => PaymentResource::collection($this->resource->payments),
            'certificates' => CertificateResource::collection($this->resource->certificates),
        ]);
    }
}
