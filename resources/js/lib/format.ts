export function formatEuro(amount: number | string): string {
    const value = typeof amount === 'string' ? Number(amount) : amount;

    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR',
    }).format(value);
}

export function formatDate(isoDate: string): string {
    return new Intl.DateTimeFormat('nl-NL', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(`${isoDate}T12:00:00`));
}
