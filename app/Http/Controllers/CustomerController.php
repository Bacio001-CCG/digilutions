<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\Rental\ReservationPricingPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(private ReservationPricingPresenter $pricing) {}

    public function index(): Response
    {
        $customers = Customer::query()
            ->withCount('reservations')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'phone', 'email']);

        return Inertia::render('klanten/index', [
            'customers' => $customers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('klanten/create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::query()->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Klant opgeslagen',
        ]);

        return to_route('klanten.show', $customer);
    }

    public function show(Customer $customer): Response
    {
        $today = Carbon::today();

        $customer->load([
            'reservations' => fn ($query) => $query
                ->with('machine:id,name,daily_price')
                ->orderByDesc('pickup_date')
                ->orderByDesc('id'),
        ]);

        return Inertia::render('klanten/show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'reservations' => $customer->reservations->map(fn ($reservation) => [
                    'id' => $reservation->id,
                    'machine_name' => $reservation->machine?->name,
                    'pickup_date' => $reservation->pickup_date->toDateString(),
                    'return_date' => $reservation->return_date->toDateString(),
                    'returned_at' => $reservation->returned_at?->toIso8601String(),
                    'pricing' => $this->pricing->present($reservation, $today),
                ]),
            ],
        ]);
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('klanten/edit', [
            'customer' => $customer->only(['id', 'name', 'phone', 'email']),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Klant bijgewerkt',
        ]);

        return to_route('klanten.show', $customer);
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->reservations()->exists()) {
            throw ValidationException::withMessages([
                'customer' => 'Deze klant heeft reserveringen en kan niet worden verwijderd.',
            ]);
        }

        $customer->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Klant verwijderd',
        ]);

        return to_route('klanten.index');
    }
}
