<?php

namespace App\Services\Rental;

final class RentalPriceBreakdown
{
    public function __construct(
        public readonly int $plannedWorkdays,
        public readonly int $billableWorkdays,
        public readonly bool $volumeDiscountApplied,
        public readonly int $lateWorkdays,
        public readonly string $rentalAmount,
        public readonly string $lateFeeAmount,
        public readonly string $totalAmount,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'planned_workdays' => $this->plannedWorkdays,
            'billable_workdays' => $this->billableWorkdays,
            'volume_discount_applied' => $this->volumeDiscountApplied,
            'late_workdays' => $this->lateWorkdays,
            'rental_amount' => $this->rentalAmount,
            'late_fee_amount' => $this->lateFeeAmount,
            'total_amount' => $this->totalAmount,
        ];
    }
}
