<?php

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $machine_id
 * @property int $customer_id
 * @property Carbon $pickup_date
 * @property Carbon $return_date
 * @property Carbon|null $returned_at
 * @property string|null $charged_amount
 * @property string|null $late_fee_amount
 * @property bool|null $volume_discount_applied
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'machine_id',
    'customer_id',
    'pickup_date',
    'return_date',
    'returned_at',
    'charged_amount',
    'late_fee_amount',
    'volume_discount_applied',
])]
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'return_date' => 'date',
            'returned_at' => 'datetime',
            'charged_amount' => 'decimal:2',
            'late_fee_amount' => 'decimal:2',
            'volume_discount_applied' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Machine, $this>
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
