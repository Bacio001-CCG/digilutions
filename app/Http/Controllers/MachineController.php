<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMachineRequest;
use App\Models\Machine;
use App\Services\Rental\ReservationPricingPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class MachineController extends Controller
{
    public function __construct(private ReservationPricingPresenter $pricing) {}

    public function index(): Response
    {
        $machines = Machine::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'daily_price']);

        return Inertia::render('machines/index', [
            'machines' => $machines,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('machines/create');
    }

    public function store(StoreMachineRequest $request): RedirectResponse
    {
        Machine::query()->create($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Machine opgeslagen',
        ]);

        return to_route('machines.index');
    }

    public function show(Machine $machine): Response
    {
        $today = Carbon::today();

        $machine->load([
            'reservations' => fn ($query) => $query
                ->with('customer:id,name')
                ->orderByDesc('pickup_date')
                ->orderByDesc('id'),
        ]);

        return Inertia::render('machines/show', [
            'machine' => [
                'id' => $machine->id,
                'name' => $machine->name,
                'daily_price' => $machine->daily_price,
                'reservations' => $machine->reservations->map(fn ($reservation) => [
                    'id' => $reservation->id,
                    'customer_name' => $reservation->customer?->name,
                    'pickup_date' => $reservation->pickup_date->toDateString(),
                    'return_date' => $reservation->return_date->toDateString(),
                    'returned_at' => $reservation->returned_at?->toIso8601String(),
                    'can_stop' => $reservation->returned_at === null
                        && $reservation->pickup_date->lte($today),
                    'pricing' => $this->pricing->present($reservation->setRelation('machine', $machine), $today),
                ]),
            ],
        ]);
    }
}
