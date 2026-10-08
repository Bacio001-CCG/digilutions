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
import { formatEuro } from '@/lib/format';
import { reservering } from '@/routes';
import { create, index as machinesIndex, show } from '@/routes/machines';

type Machine = {
    id: number;
    name: string;
    daily_price: string;
};

export default function MachinesIndex({ machines }: { machines: Machine[] }) {
    return (
        <>
            <Head title="Machines" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <Heading
                        title="Machines"
                        description="Naam en dagprijs van het materieel."
                    />
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <Link href={reservering()}>Reserveren</Link>
                        </Button>
                        <Button asChild>
                            <Link href={create()}>
                                <Plus />
                                Machine toevoegen
                            </Link>
                        </Button>
                    </div>
                </div>

                {machines.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Nog geen machines. Voeg er een toe om te reserveren.
                    </p>
                ) : (
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {machines.map((machine) => (
                            <Link
                                key={machine.id}
                                href={show(machine.id)}
                                className="block"
                            >
                                <Card className="h-full py-4 transition-colors hover:border-primary/40">
                                    <CardHeader className="px-4 pb-0">
                                        <CardTitle className="text-base">
                                            {machine.name}
                                        </CardTitle>
                                        <CardDescription>Dagprijs</CardDescription>
                                    </CardHeader>
                                    <CardContent className="px-4 pt-2">
                                        <p className="text-2xl font-semibold tracking-tight text-primary">
                                            {formatEuro(machine.daily_price)}
                                        </p>
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

MachinesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Machines',
            href: machinesIndex(),
        },
    ],
};
