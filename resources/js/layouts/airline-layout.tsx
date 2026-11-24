import { type SharedData } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, Plane } from 'lucide-react';

import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';

export default function AirlineLayout({ children }: { children: React.ReactNode }) {
    const {
        props: { auth, flash },
        url,
    } = usePage<SharedData>();
    const { post, processing } = useForm({});
    const isAdmin = auth.user.is_admin;
    const navigationItems = [
        { label: 'Dashboard', href: route('dashboard'), path: '/dashboard' },
        { label: isAdmin ? 'Flights' : 'Find flights', href: route('flights.index'), path: '/flights' },
        { label: isAdmin ? 'Bookings' : 'My bookings', href: route('bookings.index'), path: '/bookings' },
        { label: 'Settings', href: route('profile.edit'), path: '/settings' },
    ];

    return (
        <div className="bg-background text-foreground min-h-screen md:flex">
            <aside className="bg-card flex w-full shrink-0 flex-col border-b md:min-h-screen md:w-64 md:border-r md:border-b-0">
                <Link href={route('dashboard')} className="flex items-center gap-3 px-5 py-5 text-lg font-bold tracking-tight">
                    <span className="bg-primary text-primary-foreground flex size-9 items-center justify-center rounded-lg">
                        <Plane className="size-5" aria-hidden="true" />
                    </span>
                    <span>Gonzaga Air</span>
                </Link>
                <nav aria-label="Main navigation" className="grid grid-cols-2 gap-1 px-3 pb-3 sm:grid-cols-4 md:flex md:flex-col md:px-3">
                    {navigationItems.map((item) => {
                        const currentPath = url.split('?')[0];
                        const active =
                            item.path === '/settings'
                                ? currentPath.startsWith(item.path)
                                : currentPath === item.path || currentPath.startsWith(`${item.path}/`);

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                aria-current={active ? 'page' : undefined}
                                className={`rounded-md px-3 py-2.5 text-sm font-medium transition-colors ${
                                    active ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                            >
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>
                <div className="mt-auto flex items-center justify-between gap-3 border-t px-5 py-4">
                    <div className="min-w-0">
                        <p className="font-semibold">{auth.user.name}</p>
                        <p className="text-muted-foreground truncate text-xs">{auth.user.is_admin ? 'Administrator' : auth.user.email}</p>
                    </div>
                    <form
                        onSubmit={(event) => {
                            event.preventDefault();
                            post(route('logout'));
                        }}
                    >
                        <Button type="submit" variant="outline" size="sm" disabled={processing}>
                            {processing ? 'Signing out…' : 'Log out'}
                        </Button>
                    </form>
                </div>
            </aside>
            <main className="min-w-0 flex-1">
                <div className="mx-auto w-full max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                    {flash?.success && (
                        <Alert className="border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
                            <CheckCircle2 className="size-4" aria-hidden="true" />
                            <AlertDescription>{flash.success}</AlertDescription>
                        </Alert>
                    )}
                    {flash?.error && (
                        <Alert variant="destructive">
                            <AlertCircle className="size-4" aria-hidden="true" />
                            <AlertDescription>{flash.error}</AlertDescription>
                        </Alert>
                    )}
                    {children}
                </div>
            </main>
        </div>
    );
}
