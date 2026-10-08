import { Form, Head, Link, useHttp } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatEuro } from '@/lib/format';
import { home, reservering } from '@/routes';
import { quote, store } from '@/routes/reserveringen';

type Machine = {
    id: number;
    name: string;
    daily_price: string;
};

type Customer = {
    id: number;
    name: string;
};

type QuoteResponse = {
    amount: string;
    currency: string;
    machine_id: number;
    daily_price: string;
};

export default function Reservering({
    machines,
    customers,
}: {
    machines: Machine[];
    customers: Customer[];
}) {
    const defaultMachineId = String(machines[0]?.id ?? '');
    const defaultCustomerId = String(customers[0]?.id ?? '');
    const [machineId, setMachineId] = useState(defaultMachineId);
    const [customerId, setCustomerId] = useState(defaultCustomerId);
    const [pickupDate, setPickupDate] = useState('');
    const [returnDate, setReturnDate] = useState('');
    const [quotedAmount, setQuotedAmount] = useState<string | null>(null);

    const { transform, post, processing: quoting, errors: quoteErrors } =
        useHttp({
            machine_id: defaultMachineId,
            pickup_date: '',
            return_date: '',
        });

    const selectedMachine = machines.find(
        (machine) => String(machine.id) === machineId,
    );

    useEffect(() => {
        transform(() => ({
            machine_id: machineId,
            pickup_date: pickupDate,
            return_date: returnDate,
        }));
    }, [machineId, pickupDate, returnDate, transform]);

    useEffect(() => {
        if (
            !machineId ||
            !pickupDate ||
            !returnDate ||
            returnDate < pickupDate
        ) {
            setQuotedAmount(null);

            return;
        }

        const timer = window.setTimeout(() => {
            void post(quote.url(), {
                onSuccess: (response) => {
                    setQuotedAmount((response as QuoteResponse).amount);
                },
                onError: () => setQuotedAmount(null),
            });
        }, 250);

        return () => window.clearTimeout(timer);
    }, [machineId, pickupDate, returnDate, post]);

    return (
        <>
            <Head title="Nieuwe reservering" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Nieuwe reservering"
                    description="Machine, klant en periode. Prijs wordt server-side berekend."
                />

                <div className="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle>Gegevens</CardTitle>
                            <CardDescription>
                                Overlap en keuringsdag worden bij opslaan
                                gecontroleerd.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form()}
                                className="space-y-5"
                                options={{ preserveScroll: true }}
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <input
                                            type="hidden"
                                            name="machine_id"
                                            value={machineId}
                                        />

                                        <div className="grid gap-2">
                                            <Label htmlFor="machine">
                                                Machine
                                            </Label>
                                            <Select
                                                value={machineId}
                                                onValueChange={setMachineId}
                                            >
                                                <SelectTrigger
                                                    id="machine"
                                                    className="w-full"
                                                >
                                                    <SelectValue placeholder="Kies een machine" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {machines.map((machine) => (
                                                        <SelectItem
                                                            key={machine.id}
                                                            value={String(
                                                                machine.id,
                                                            )}
                                                        >
                                                            {machine.name} (
                                                            {formatEuro(
                                                                machine.daily_price,
                                                            )}
                                                            /dag)
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={errors.machine_id}
                                            />
                                        </div>

                                        <input
                                            type="hidden"
                                            name="customer_id"
                                            value={customerId}
                                        />

                                        <div className="grid gap-2">
                                            <Label htmlFor="customer">
                                                Klant
                                            </Label>
                                            <Select
                                                value={customerId}
                                                onValueChange={setCustomerId}
                                            >
                                                <SelectTrigger
                                                    id="customer"
                                                    className="w-full"
                                                >
                                                    <SelectValue placeholder="Kies een klant" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {customers.map(
                                                        (customer) => (
                                                            <SelectItem
                                                                key={
                                                                    customer.id
                                                                }
                                                                value={String(
                                                                    customer.id,
                                                                )}
                                                            >
                                                                {customer.name}
                                                            </SelectItem>
                                                        ),
                                                    )}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={errors.customer_id}
                                            />
                                            {customers.length === 0 && (
                                                <p className="text-sm text-muted-foreground">
                                                    Maak eerst een klant aan
                                                    onder Klanten.
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="pickup">
                                                    Ophaaldatum
                                                </Label>
                                                <Input
                                                    id="pickup"
                                                    type="date"
                                                    name="pickup_date"
                                                    required
                                                    value={pickupDate}
                                                    onChange={(event) =>
                                                        setPickupDate(
                                                            event.target.value,
                                                        )
                                                    }
                                                />
                                                <InputError
                                                    message={errors.pickup_date}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="return">
                                                    Terugkomstdatum
                                                </Label>
                                                <Input
                                                    id="return"
                                                    type="date"
                                                    name="return_date"
                                                    required
                                                    value={returnDate}
                                                    onChange={(event) =>
                                                        setReturnDate(
                                                            event.target.value,
                                                        )
                                                    }
                                                />
                                                <InputError
                                                    message={
                                                        errors.return_date ??
                                                        quoteErrors.return_date
                                                    }
                                                />
                                            </div>
                                        </div>

                                        <div className="flex flex-wrap gap-3">
                                            <Button
                                                type="submit"
                                                disabled={
                                                    processing ||
                                                    !machineId ||
                                                    !customerId
                                                }
                                            >
                                                Opslaan
                                            </Button>
                                            <Button asChild variant="outline">
                                                <Link href={home()}>
                                                    Terug naar vandaag
                                                </Link>
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>

                    <Card className="h-fit border-signal/30 bg-signal/5">
                        <CardHeader>
                            <CardTitle>Geschatte huur</CardTitle>
                            <CardDescription>
                                Offerte bij op tijd inleveren. Volumekorting
                                (5 werkdagen, 4 betalen) vervalt bij vroeg of te
                                laat. Te laat: boete per werkdag.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <p className="text-3xl font-semibold tracking-tight text-primary">
                                {quoting
                                    ? 'Berekenen…'
                                    : quotedAmount === null
                                      ? 'Nog geen prijs'
                                      : formatEuro(quotedAmount)}
                            </p>
                            {selectedMachine && (
                                <p className="text-sm text-muted-foreground">
                                    {selectedMachine.name} ·{' '}
                                    {formatEuro(selectedMachine.daily_price)}{' '}
                                    per werkdag
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

Reservering.layout = {
    breadcrumbs: [
        {
            title: 'Vandaag',
            href: home(),
        },
        {
            title: 'Nieuwe reservering',
            href: reservering(),
        },
    ],
};
