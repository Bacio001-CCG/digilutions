import { Link, router } from '@inertiajs/react';
import {
    CalendarDays,
    ClipboardList,
    Forklift,
    LogOut,
    Plus,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { home, logout, reservering } from '@/routes';
import { index as klanten } from '@/routes/klanten';
import { index as machines } from '@/routes/machines';
import { index as reserveringen } from '@/routes/reserveringen';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Vandaag',
        href: home(),
        icon: CalendarDays,
    },
    {
        title: 'Reserveringen',
        href: reserveringen(),
        icon: ClipboardList,
    },
    {
        title: 'Machines',
        href: machines(),
        icon: Forklift,
    },
    {
        title: 'Klanten',
        href: klanten(),
        icon: Users,
    },
    {
        title: 'Nieuwe reservering',
        href: reservering(),
        icon: Plus,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={home()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild tooltip="Uitloggen">
                            <Link
                                href={logout()}
                                method="post"
                                as="button"
                                onClick={() => router.flushAll()}
                                data-test="sidebar-logout-button"
                            >
                                <LogOut />
                                <span>Uitloggen</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    );
}
