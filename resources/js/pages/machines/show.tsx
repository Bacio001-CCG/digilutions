import { Form, Head, Link } from '@inertiajs/react';
import { Check } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDate, formatEuro } from '@/lib/format';
import { reservering } from '@/routes';
import { index as machinesIndex } from '@/routes/machines';
import { retour } from '@/routes/reserveringen';

type ReservationPricing = {
    rental_amount: string;
    late_fee_amount: string;
    total_amount: string;
    late_workdays: number;
    volume_discount_applied: boolean;
    is_final: boolean;
};

type MachineReservation = {
    id: number;
    customer_name: string | null;
    pickup_date: string;
    return_date: string;
    returned_at: string | null;
    can_stop: boolean;
    pricing: ReservationPricing;
};

type Machine = {
    id: number;
    name: string;
    daily_price: string;
    reservations: MachineReservation[];
};

export default function MachinesShow({ machine }: { machine: Machine }) {
    return (
        <>
            <Head title={machine.name} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <Heading
                        title={machine.name}
                        description="Dagprijs en reserveringen."
                    />
                    <Button asChild>
                        <Link href={reservering()}>Nieuwe reservering</Link>
                    </Button>
                </div>

                <Card className="py-4">
                    <CardHeader className="px-4 pb-0">
                        <CardTitle className="text-base">Dagprijs</CardTitle>
                        <CardDescription>
                            Huidige prijs per werkdag.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="px-4 pt-3">
                        <p className="text-2xl font-semibold tracking-tight text-primary">
                            {formatEuro(machine.daily_price)}
                        </p>
                    </CardContent>
                </Card>

                <section className="space-y-3">
                    <h3 className="text-sm font-medium">Reserveringen</h3>
                    {machine.reservations.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Nog geen reserveringen voor deze machine.
                        </p>
                    ) : (
                        <div className="space-y-3">
                            {machine.reservations.map((reservation) => (
                                <Card key={reservation.id} className="py-4">
                                    <CardHeader className="px-4 pb-0">
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div className="space-y-1">
                                                <CardTitle className="text-base">
                                                    {reservation.customer_name ??
                                                        'Onbekende klant'}
                                                </CardTitle>
                                                <CardDescription>
                                                    {formatDate(
                                                        reservation.pickup_date,
                                                    )}{' '}
                                                    tot{' '}
                                                    {formatDate(
                                                        reservation.return_date,
                                                    )}
                                                </CardDescription>
                                            </div>
                                            <Badge
                                                variant={
                                                    reservation.returned_at
                                                        ? 'secondary'
                                                        : 'outline'
                                                }
                                            >
                                                {reservation.returned_at
                                                    ? 'Retour'
                                                    : 'Open'}
                                            </Badge>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="flex flex-col gap-3 px-4 pt-3 text-sm sm:flex-row sm:items-end sm:justify-between">
                                        <div className="space-y-1">
                                            <p>
                                                {reservation.pricing.is_final
                                                    ? 'Totaal'
                                                    : 'Verwacht totaal'}
                                                :{' '}
                                                <span className="font-medium text-primary">
                                                    {formatEuro(
                                                        reservation.pricing
                                                            .total_amount,
                                                    )}
                                                </span>
                                            </p>
                                            {Number(
                                                reservation.pricing
                                                    .late_fee_amount,
                                            ) > 0 && (
                                                <p className="text-status-overdue">
                                                    Boete
                                                    {reservation.pricing
                                                        .late_workdays > 0
                                                        ? ` (${reservation.pricing.late_workdays} werkdag${reservation.pricing.late_workdays === 1 ? '' : 'en'})`
                                                        : ''}
                                                    :{' '}
                                                    {formatEuro(
                                                        reservation.pricing
                                                            .late_fee_amount,
                                                    )}
                                                </p>
                                            )}
                                            <p className="text-xs text-muted-foreground">
                                                {reservation.pricing
                                                    .volume_discount_applied
                                                    ? 'Inclusief volumekorting'
                                                    : 'Zonder volumekorting'}
                                            </p>
                                        </div>
                                        {reservation.can_stop && (
                                            <Form
                                                {...retour.form(reservation.id)}
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        size="sm"
                                                        variant="secondary"
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        <Check />
                                                        Eerder stoppen
                                                    </Button>
                                                )}
                                            </Form>
                                        )}
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

MachinesShow.layout = {
    breadcrumbs: [
        {
            title: 'Machines',
            href: machinesIndex(),
        },
        {
            title: 'Detail',
            href: machinesIndex(),
        },
    ],
};
