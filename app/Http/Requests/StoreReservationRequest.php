<?php

namespace App\Http\Requests;

use App\Services\Rental\MachineAvailabilityChecker;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'machine_id' => ['required', 'integer', 'exists:machines,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'pickup_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:pickup_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'return_date.after_or_equal' => 'De terugkomstdatum mag niet vóór de ophaaldatum liggen.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $available = app(MachineAvailabilityChecker::class)->isAvailable(
                    (int) $this->input('machine_id'),
                    Carbon::parse((string) $this->input('pickup_date'))->startOfDay(),
                    Carbon::parse((string) $this->input('return_date'))->startOfDay(),
                );

                if (! $available) {
                    $validator->errors()->add(
                        'pickup_date',
                        'Deze machine is in die periode niet beschikbaar (inclusief keuringsdag).',
                    );
                }
            },
        ];
    }
}
