<?php

namespace App\Http\Resources;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Certificate $resource
 */
class CertificateResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'issued_date' => $this->resource->issued_date,
            'expiration_date' => $this->resource->expiration_date,
            'user' => new UserResource($this->resource->user),
            'category' => new CertificateCategoryResource($this->resource->category),
        ];
    }
}
