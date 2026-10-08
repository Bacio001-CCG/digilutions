import { usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    const { name } = usePage().props;
    const label = typeof name === 'string' ? name : 'Van Dijk Verhuur';
    const [brand, ...rest] = label.split(' ');
    const subtitle = rest.join(' ') || 'Verhuur';

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                <AppLogoIcon className="size-5 fill-current" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="truncate leading-tight font-semibold">
                    {brand}
                </span>
                <span className="truncate text-xs leading-tight text-sidebar-foreground/70">
                    {subtitle}
                </span>
            </div>
        </>
    );
}
