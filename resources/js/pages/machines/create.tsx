import { Form, Head, Link } from '@inertiajs/react';
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
    create,
    index as machinesIndex,
    store,
} from '@/routes/machines';

export default function MachinesCreate() {
    return (
        <>
            <Head title="Machine toevoegen" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Machine toevoegen"
                    description="Naam en dagprijs zijn verplicht."
                />

                <Card className="max-w-xl">
                    <CardHeader>
                        <CardTitle>Gegevens</CardTitle>
                        <CardDescription>
                            Deze machine kun je daarna kiezen bij een
                            reservering.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form {...store.form()} className="space-y-5">
                            {({ errors, processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Naam</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            placeholder="Bijvoorbeeld Hoogwerker 12m"
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="daily_price">
                                            Dagprijs (euro)
                                        </Label>
                                        <Input
                                            id="daily_price"
                                            name="daily_price"
                                            type="number"
                                            inputMode="decimal"
                                            min="0"
                                            step="0.01"
                                            required
                                            placeholder="95.00"
                                        />
                                        <InputError
                                            message={errors.daily_price}
                                        />
                                    </div>
                                    <div className="flex flex-wrap gap-3">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Opslaan
                                        </Button>
                                        <Button asChild variant="outline">
                                            <Link href={machinesIndex()}>
                                                Annuleren
                                            </Link>
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

MachinesCreate.layout = {
    breadcrumbs: [
        {
            title: 'Machines',
            href: machinesIndex(),
        },
        {
            title: 'Toevoegen',
            href: create(),
        },
    ],
};
