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
    index as klantenIndex,
    store,
} from '@/routes/klanten';

export default function KlantenCreate() {
    return (
        <>
            <Head title="Nieuwe klant" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Nieuwe klant"
                    description="Naam is verplicht. Telefoon en e-mail zijn optioneel."
                />

                <Card className="max-w-xl">
                    <CardHeader>
                        <CardTitle>Gegevens</CardTitle>
                        <CardDescription>
                            Deze klant kun je daarna kiezen bij een reservering.
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
                                            placeholder="Bijvoorbeeld Bouwbedrijf Jansen"
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="phone">Telefoon</Label>
                                        <Input
                                            id="phone"
                                            name="phone"
                                            placeholder="06..."
                                        />
                                        <InputError message={errors.phone} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="email">E-mail</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            placeholder="info@bedrijf.nl"
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
                                            <Link href={klantenIndex()}>
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

KlantenCreate.layout = {
    breadcrumbs: [
        {
            title: 'Klanten',
            href: klantenIndex(),
        },
        {
            title: 'Nieuw',
            href: create(),
        },
    ],
};
