<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $invoice_number
 * @property string $amount
 * @property int $status
 * @property Carbon $created_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $user_id
 * @property User $user
 */
class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_number',
        'amount',
        'status',
        'created_date',
    ];

    protected $casts = [
        'created_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
