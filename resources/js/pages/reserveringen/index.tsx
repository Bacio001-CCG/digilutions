import { Form, Head, Link } from '@inertiajs/react';
import { Check } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
import { show as klantShow } from '@/routes/klanten';
import { show as machineShow } from '@/routes/machines';
import {
    destroy,
    index as reserveringenIndex,
    retour,
} from '@/routes/reserveringen';

type ReservationPricing = {
    rental_amount: string;
    late_fee_amount: string;
    total_amount: string;
    late_workdays: number;
    volume_discount_applied: boolean;
    is_final: boolean;
};

type ListedReservation = {
    id: number;
    machine_id: number;
    machine_name: string | null;
    customer_id: number;
    customer_name: string | null;
    pickup_date: string;
    return_date: string;
    returned_at: string | null;
    is_returned: boolean;
    is_overdue: boolean;
    is_planned: boolean;
    is_early_stop: boolean;
    can_stop: boolean;
    can_delete: boolean;
    pricing: ReservationPricing;
};

function statusLabel(reservation: ListedReservation): string {
    if (reservation.is_returned) {
        return 'Retour';
    }

    if (reservation.is_overdue) {
        return 'Te laat';
    }

    if (reservation.is_planned) {
        return 'Gepland';
    }

    if (reservation.is_early_stop) {
        return 'Lopend';
    }

    return 'Retour vandaag';
}

export default function ReserveringenIndex({
    reservations,
}: {
    reservations: ListedReservation[];
    today: string;
}) {
    return (
        <>
            <Head title="Reserveringen" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <Heading
                        title="Reserveringen"
                        description="Alle reserveringen. Geplande kun je verwijderen, lopende kun je eerder stoppen."
                    />
                    <Button asChild>
                        <Link href={reservering()}>Nieuwe reservering</Link>
                    </Button>
                </div>

                {reservations.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Nog geen reserveringen.
                    </p>
                ) : (
                    <div className="space-y-3">
                        {reservations.map((reservation) => (
                            <Card
                                key={reservation.id}
                                className={
                                    reservation.is_overdue
                                        ? 'border-status-overdue/35 bg-status-overdue/5 py-4'
                                        : 'py-4'
                                }
                            >
                                <CardHeader className="px-4 pb-0">
                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                        <div className="space-y-1">
                                            <CardTitle className="text-base">
                                                <Link
                                                    href={machineShow(
                                                        reservation.machine_id,
                                                    )}
                                                    className="hover:underline"
                                                >
                                                    {reservation.machine_name}
                                                </Link>
                                            </CardTitle>
                                            <CardDescription>
                                                <Link
                                                    href={klantShow(
                                                        reservation.customer_id,
                                                    )}
                                                    className="hover:underline"
                                                >
                                                    {reservation.customer_name}
                                                </Link>
                                            </CardDescription>
                                        </div>
                                        <Badge
                                            variant={
                                                reservation.is_overdue
                                                    ? 'destructive'
                                                    : reservation.is_returned
                                                      ? 'secondary'
                                                      : 'outline'
                                            }
                                        >
                                            {statusLabel(reservation)}
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent className="flex flex-col gap-4 px-4 pt-3 sm:flex-row sm:items-end sm:justify-between">
                                    <div className="space-y-2 text-sm">
                                        <p className="text-muted-foreground">
                                            {formatDate(reservation.pickup_date)}{' '}
                                            tot{' '}
                                            {formatDate(reservation.return_date)}
                                        </p>
                                        <div className="space-y-1">
                                            <div className="flex justify-between gap-6">
                                                <span className="text-muted-foreground">
                                                    Huur
                                                </span>
                                                <span>
                                                    {formatEuro(
                                                        reservation.pricing
                                                            .rental_amount,
                                                    )}
                                                </span>
                                            </div>
                                            {Number(
                                                reservation.pricing
                                                    .late_fee_amount,
                                            ) > 0 && (
                                                <div className="flex justify-between gap-6 text-status-overdue">
                                                    <span>
                                                        Boete
                                                        {reservation.pricing
                                                            .late_workdays > 0
                                                            ? ` (${reservation.pricing.late_workdays} werkdag${reservation.pricing.late_workdays === 1 ? '' : 'en'})`
                                                            : ''}
                                                    </span>
                                                    <span>
                                                        {formatEuro(
                                                            reservation.pricing
                                                                .late_fee_amount,
                                                        )}
                                                    </span>
                                                </div>
                                            )}
                                            <div className="flex justify-between gap-6 font-medium">
                                                <span>
                                                    {reservation.is_early_stop
                                                        ? 'Bij stoppen nu'
                                                        : reservation.pricing
                                                                .is_final
                                                          ? 'Totaal'
                                                          : 'Verwacht totaal'}
                                                </span>
                                                <span className="text-primary">
                                                    {formatEuro(
                                                        reservation.pricing
                                                            .total_amount,
                                                    )}
                                                </span>
                                            </div>
                                            <p className="text-xs text-muted-foreground">
                                                {reservation.pricing
                                                    .volume_discount_applied
                                                    ? 'Inclusief volumekorting'
                                                    : 'Zonder volumekorting'}
                                                {reservation.is_early_stop
                                                    ? '. Vroeg stoppen: volle geplande periode, geen korting.'
                                                    : ''}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex flex-col items-stretch gap-2 sm:items-end">
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
                                                        variant={
                                                            reservation.is_overdue
                                                                ? 'default'
                                                                : 'secondary'
                                                        }
                                                        type="submit"
                                                        disabled={processing}
                                                    >
                                                        <Check />
                                                        {reservation.is_early_stop
                                                            ? 'Eerder stoppen'
                                                            : 'Retour afvinken'}
                                                    </Button>
                                                )}
                                            </Form>
                                        )}
                                        {reservation.can_delete && (
                                            <Form
                                                {...destroy.form(
                                                    reservation.id,
                                                )}
                                                options={{
                                                    preserveScroll: true,
                                                }}
                                            >
                                                {({ processing, errors }) => (
                                                    <div className="space-y-1">
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            type="submit"
                                                            disabled={
                                                                processing
                                                            }
                                                        >
                                                            Verwijderen
                                                        </Button>
                                                        <InputError
                                                            message={
                                                                errors.reservation
                                                            }
                                                        />
                                                    </div>
                                                )}
                                            </Form>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

ReserveringenIndex.layout = {
    breadcrumbs: [
        {
            title: 'Reserveringen',
            href: reserveringenIndex(),
        },
    ],
};
