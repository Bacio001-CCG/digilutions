import { Form, Head, Link } from '@inertiajs/react';
import { Check } from 'lucide-react';
import Heading from '@/components/heading';
import { MonthlyIncomeChart } from '@/components/monthly-income-chart';
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
import { home, reservering } from '@/routes';
import { show as klantShow } from '@/routes/klanten';
import { retour } from '@/routes/reserveringen';

type ReservationPricing = {
    rental_amount: string;
    late_fee_amount: string;
    total_amount: string;
    late_workdays: number;
    volume_discount_applied: boolean;
    is_final: boolean;
};

type TodayReservation = {
    id: number;
    machine_name: string | null;
    customer_id: number;
    customer_name: string | null;
    pickup_date: string;
    return_date: string;
    returned_at: string | null;
    is_overdue: boolean;
    is_pickup_today: boolean;
    is_return_today: boolean;
    pricing: ReservationPricing;
};

function PricingBlock({ pricing }: { pricing: ReservationPricing }) {
    const hasLateFee = Number(pricing.late_fee_amount) > 0;

    return (
        <div className="space-y-1 text-sm">
            <div className="flex justify-between gap-4">
                <span className="text-muted-foreground">Huur</span>
                <span>{formatEuro(pricing.rental_amount)}</span>
            </div>
            {hasLateFee && (
                <div className="flex justify-between gap-4 text-status-overdue">
                    <span>
                        Boete
                        {pricing.late_workdays > 0
                            ? ` (${pricing.late_workdays} werkdag${pricing.late_workdays === 1 ? '' : 'en'})`
                            : ''}
                    </span>
                    <span>{formatEuro(pricing.late_fee_amount)}</span>
                </div>
            )}
            <div className="flex justify-between gap-4 font-medium">
                <span>{pricing.is_final ? 'Totaal' : 'Verwacht totaal'}</span>
                <span className="text-primary">
                    {formatEuro(pricing.total_amount)}
                </span>
            </div>
            <p className="text-xs text-muted-foreground">
                {pricing.volume_discount_applied
                    ? 'Inclusief volumekorting'
                    : 'Zonder volumekorting'}
            </p>
        </div>
    );
}

type MonthlyIncome = {
    month_label: string;
    total: string;
    days: Array<{
        date: string;
        label: string;
        amount: string;
    }>;
};

export default function Vandaag({
    reservations,
    monthly_income,
}: {
    reservations: TodayReservation[];
    today: string;
    monthly_income: MonthlyIncome;
}) {
    const overdue = reservations.filter((item) => item.is_overdue);
    const pickups = reservations.filter(
        (item) => item.is_pickup_today && !item.is_overdue,
    );
    const returns = reservations.filter(
        (item) => item.is_return_today && !item.is_overdue,
    );

    return (
        <>
            <Head title="Vandaag" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <Heading
                        title="Vandaag"
                        description="Wat er de deur uit gaat en wat er terug moet komen."
                    />
                    <Button asChild>
                        <Link href={reservering()}>Nieuwe reservering</Link>
                    </Button>
                </div>

                <MonthlyIncomeChart monthlyIncome={monthly_income} />

                {overdue.length > 0 && (
                    <section className="space-y-3">
                        <h3 className="text-sm font-medium text-status-overdue">
                            Te laat
                        </h3>
                        <div className="space-y-3">
                            {overdue.map((reservation) => (
                                <Card
                                    key={reservation.id}
                                    className="border-status-overdue/35 bg-status-overdue/5 py-4"
                                >
                                    <CardHeader className="px-4 pb-0">
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div className="space-y-1">
                                                <CardTitle className="text-base">
                                                    {reservation.machine_name}
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
                                            <Badge variant="destructive">
                                                Te laat
                                            </Badge>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="flex flex-col gap-4 px-4 pt-3 sm:flex-row sm:items-end sm:justify-between">
                                        <div className="space-y-3">
                                            <p className="text-sm text-muted-foreground">
                                                Terug op{' '}
                                                {formatDate(
                                                    reservation.return_date,
                                                )}
                                            </p>
                                            <PricingBlock
                                                pricing={reservation.pricing}
                                            />
                                        </div>
                                        <Form
                                            {...retour.form(reservation.id)}
                                            options={{ preserveScroll: true }}
                                        >
                                            {({ processing }) => (
                                                <Button
                                                    size="sm"
                                                    variant="secondary"
                                                    type="submit"
                                                    disabled={processing}
                                                >
                                                    <Check />
                                                    Retour afvinken
                                                </Button>
                                            )}
                                        </Form>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </section>
                )}

                <div className="grid gap-6 lg:grid-cols-2">
                    <section className="space-y-3">
                        <h3 className="text-sm font-medium">Ophalen vandaag</h3>
                        {pickups.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Geen ophalingen vandaag.
                            </p>
                        ) : (
                            <div className="space-y-3">
                                {pickups.map((reservation) => (
                                    <Card key={reservation.id} className="py-4">
                                        <CardHeader className="px-4 pb-0">
                                            <div className="flex flex-wrap items-start justify-between gap-3">
                                                <div className="space-y-1">
                                                    <CardTitle className="text-base">
                                                        {reservation.machine_name}
                                                    </CardTitle>
                                                    <CardDescription>
                                                        <Link
                                                            href={klantShow(
                                                                reservation.customer_id,
                                                            )}
                                                            className="hover:underline"
                                                        >
                                                            {
                                                                reservation.customer_name
                                                            }
                                                        </Link>
                                                    </CardDescription>
                                                </div>
                                                <Badge className="border-transparent bg-signal/20 text-signal-foreground">
                                                    Ophalen
                                                </Badge>
                                            </div>
                                        </CardHeader>
                                        <CardContent className="space-y-3 px-4 pt-3">
                                            <p className="text-sm text-muted-foreground">
                                                Tot{' '}
                                                {formatDate(
                                                    reservation.return_date,
                                                )}
                                            </p>
                                            <PricingBlock
                                                pricing={reservation.pricing}
                                            />
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </section>

                    <section className="space-y-3">
                        <h3 className="text-sm font-medium">
                            Terugkomen vandaag
                        </h3>
                        {returns.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Geen retouren vandaag.
                            </p>
                        ) : (
                            <div className="space-y-3">
                                {returns.map((reservation) => (
                                    <Card key={reservation.id} className="py-4">
                                        <CardHeader className="px-4 pb-0">
                                            <div className="flex flex-wrap items-start justify-between gap-3">
                                                <div className="space-y-1">
                                                    <CardTitle className="text-base">
                                                        {reservation.machine_name}
                                                    </CardTitle>
                                                    <CardDescription>
                                                        <Link
                                                            href={klantShow(
                                                                reservation.customer_id,
                                                            )}
                                                            className="hover:underline"
                                                        >
                                                            {
                                                                reservation.customer_name
                                                            }
                                                        </Link>
                                                    </CardDescription>
                                                </div>
                                                <Badge variant="outline">
                                                    Terugkomen
                                                </Badge>
                                            </div>
                                        </CardHeader>
                                        <CardContent className="flex flex-col gap-4 px-4 pt-3 sm:flex-row sm:items-end sm:justify-between">
                                            <div className="space-y-3">
                                                <p className="text-sm text-muted-foreground">
                                                    Sinds{' '}
                                                    {formatDate(
                                                        reservation.pickup_date,
                                                    )}
                                                </p>
                                                <PricingBlock
                                                    pricing={
                                                        reservation.pricing
                                                    }
                                                />
                                            </div>
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
                                                        Retour afvinken
                                                    </Button>
                                                )}
                                            </Form>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </section>
                </div>
            </div>
        </>
    );
}

Vandaag.layout = {
    breadcrumbs: [
        {
            title: 'Vandaag',
            href: home(),
        },
    ],
};
