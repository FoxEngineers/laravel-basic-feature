<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property Carbon $issued_date
 * @property Carbon $expiration_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $certificate_category_id
 * @property int $user_id
 * @property CertificateCategory $category
 * @property User $user
 */
class Certificate extends Model
{
    protected $fillable = [
        'title',
        'issued_date',
        'expiration_date',
        'certificate_category_id',
        'user_id',
    ];

    protected $casts = [
        'issued_date' => 'datetime',
        'expiration_date' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CertificateCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
