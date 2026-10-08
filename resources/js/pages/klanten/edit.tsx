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
import { index as klantenIndex, show, update } from '@/routes/klanten';

type Customer = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
};

export default function KlantenEdit({ customer }: { customer: Customer }) {
    return (
        <>
            <Head title={`Klant bewerken: ${customer.name}`} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Klant bewerken"
                    description={customer.name}
                />

                <Card className="max-w-xl">
                    <CardHeader>
                        <CardTitle>Gegevens</CardTitle>
                        <CardDescription>
                            Wijzigingen gelden voor nieuwe en bestaande
                            overzichten.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            {...update.form(customer.id)}
                            className="space-y-5"
                        >
                            {({ errors, processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Naam</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            required
                                            defaultValue={customer.name}
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="phone">Telefoon</Label>
                                        <Input
                                            id="phone"
                                            name="phone"
                                            defaultValue={customer.phone ?? ''}
                                        />
                                        <InputError message={errors.phone} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="email">E-mail</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            defaultValue={customer.email ?? ''}
                                        />
                                        <InputError message={errors.email} />
                                    </div>
                                    <div className="flex flex-wrap gap-3">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            Opslaan
                                        </Button>
                                        <Button asChild variant="outline">
                                            <Link href={show(customer.id)}>
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

KlantenEdit.layout = {
    breadcrumbs: [
        {
            title: 'Klanten',
            href: klantenIndex(),
        },
        {
            title: 'Bewerken',
            href: klantenIndex(),
        },
    ],
};
