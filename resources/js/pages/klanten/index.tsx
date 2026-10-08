import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index as klantenIndex, create, show } from '@/routes/klanten';

type Customer = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    reservations_count: number;
};

export default function KlantenIndex({ customers }: { customers: Customer[] }) {
    return (
        <>
            <Head title="Klanten" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <Heading
                        title="Klanten"
                        description="Overzicht van huurders en hun reserveringen."
                    />
                    <Button asChild>
                        <Link href={create()}>
                            <Plus />
                            Nieuwe klant
                        </Link>
                    </Button>
                </div>

                {customers.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Nog geen klanten. Voeg er een toe om te reserveren.
                    </p>
                ) : (
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {customers.map((customer) => (
                            <Link
                                key={customer.id}
                                href={show(customer.id)}
                                className="block"
                            >
                                <Card className="h-full py-4 transition-colors hover:border-primary/40">
                                    <CardHeader className="px-4 pb-0">
                                        <CardTitle className="text-base">
                                            {customer.name}
                                        </CardTitle>
                                        <CardDescription>
                                            {customer.reservations_count}{' '}
                                            reservering
                                            {customer.reservations_count === 1
                                                ? ''
                                                : 'en'}
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="space-y-1 px-4 pt-3 text-sm text-muted-foreground">
                                        {customer.phone && <p>{customer.phone}</p>}
                                        {customer.email && <p>{customer.email}</p>}
                                        {!customer.phone && !customer.email && (
                                            <p>Geen contactgegevens</p>
                                        )}
                                    </CardContent>
                                </Card>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

KlantenIndex.layout = {
    breadcrumbs: [
        {
            title: 'Klanten',
            href: klantenIndex(),
        },
    ],
};
