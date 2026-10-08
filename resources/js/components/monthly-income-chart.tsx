import { formatEuro } from '@/lib/format';
import { cn } from '@/lib/utils';

type DayIncome = {
    date: string;
    label: string;
    amount: string;
};

type MonthlyIncome = {
    month_label: string;
    total: string;
    days: DayIncome[];
};

export function MonthlyIncomeChart({
    monthlyIncome,
}: {
    monthlyIncome: MonthlyIncome;
}) {
    const maxAmount = Math.max(
        ...monthlyIncome.days.map((day) => Number(day.amount)),
        0,
    );

    return (
        <section className="space-y-3">
            <div className="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h3 className="text-sm font-medium">Inkomsten deze maand</h3>
                    <p className="text-sm text-muted-foreground capitalize">
                        {monthlyIncome.month_label}
                    </p>
                </div>
                <p className="text-2xl font-semibold tracking-tight text-signal">
                    {formatEuro(monthlyIncome.total)}
                </p>
            </div>

            <div className="rounded-md border border-border bg-card p-4">
                {Number(monthlyIncome.total) === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        Nog geen afgeronde inkomsten deze maand.
                    </p>
                ) : (
                    <div className="flex h-40 items-end gap-px sm:gap-0.5">
                        {monthlyIncome.days.map((day) => {
                            const amount = Number(day.amount);
                            const heightPercent =
                                maxAmount > 0
                                    ? Math.max((amount / maxAmount) * 100, amount > 0 ? 4 : 0)
                                    : 0;

                            return (
                                <div
                                    key={day.date}
                                    className="group relative flex min-w-0 flex-1 flex-col items-center justify-end"
                                    title={`${day.label}: ${formatEuro(day.amount)}`}
                                >
                                    <div
                                        className={cn(
                                            'w-full max-w-3 rounded-t-sm transition-colors',
                                            amount > 0
                                                ? 'bg-primary group-hover:bg-signal'
                                                : 'bg-transparent',
                                        )}
                                        style={{
                                            height: `${heightPercent}%`,
                                            minHeight: amount > 0 ? '4px' : '0',
                                        }}
                                    />
                                </div>
                            );
                        })}
                    </div>
                )}
                <div className="mt-2 flex justify-between text-xs text-muted-foreground">
                    <span>1</span>
                    <span>{monthlyIncome.days.at(-1)?.label}</span>
                </div>
            </div>
        </section>
    );
}
