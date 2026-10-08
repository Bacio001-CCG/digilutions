import { Form, Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
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
import { destroy, edit, index as klantenIndex } from '@/routes/klanten';
type ReservationPricing = {
    rental_amount: string;
    late_fee_amount: string;
    total_amount: string;
    late_workdays: number;
    volume_discount_applied: boolean;
    is_final: boolean;
};

type CustomerReservation = {
    id: number;
    machine_name: string | null;
    pickup_date: string;
    return_date: string;
    returned_at: string | null;
    pricing: ReservationPricing;
};

type Customer = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    reservations: CustomerReservation[];
};

export default function KlantenShow({ customer }: { customer: Customer }) {
    return (
        <>
            <Head title={customer.name} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <Heading
                        title={customer.name}
                        description="Contactgegevens en reserveringen."
                    />
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <Link href={edit(customer.id)}>
                                <Pencil />
                                Bewerken
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href={reservering()}>Nieuwe reservering</Link>
                        </Button>
                    </div>
                </div>

                <Card className="py-4">
                    <CardHeader className="px-4 pb-0">
                        <CardTitle className="text-base">Contact</CardTitle>
                        <CardDescription>
                            Gegevens van deze klant.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-1 px-4 pt-3 text-sm">
                        <p>
                            <span className="text-muted-foreground">
                                Telefoon:{' '}
                            </span>
                            {customer.phone ?? 'Niet ingevuld'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                E-mail:{' '}
                            </span>
                            {customer.email ?? 'Niet ingevuld'}
                        </p>
                    </CardContent>
                </Card>

                <section className="space-y-3">
                    <h3 className="text-sm font-medium">Reserveringen</h3>
                    {customer.reservations.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Nog geen reserveringen voor deze klant.
                        </p>
                    ) : (
                        <div className="space-y-3">
                            {customer.reservations.map((reservation) => (
                                <Card key={reservation.id} className="py-4">
                                    <CardHeader className="px-4 pb-0">
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div className="space-y-1">
                                                <CardTitle className="text-base">
                                                    {reservation.machine_name}
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
                                    <CardContent className="space-y-1 px-4 pt-3 text-sm">
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
                                            reservation.pricing.late_fee_amount,
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
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </section>

                <Form
                    {...destroy.form(customer.id)}
                    className="max-w-xl"
                    options={{ preserveScroll: true }}
                >
                    {({ processing, errors }) => (
                        <div className="space-y-2">
                            <Button
                                type="submit"
                                variant="destructive"
                                disabled={processing}
                            >
                                Klant verwijderen
                            </Button>
                            {errors.customer && (
                                <p className="text-sm text-destructive">
                                    {errors.customer}
                                </p>
                            )}
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

KlantenShow.layout = {
    breadcrumbs: [
        {
            title: 'Klanten',
            href: klantenIndex(),
        },
        {
            title: 'Detail',
            href: klantenIndex(),
        },
    ],
};
