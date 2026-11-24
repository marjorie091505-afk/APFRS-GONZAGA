import { Link, usePage } from '@inertiajs/react';

const sidebarNavItems = [
    { title: 'Profile', url: '/settings/profile' },
    { title: 'Password', url: '/settings/password' },
    { title: 'Appearance', url: '/settings/appearance' },
];

export default function SettingsLayout({ children }: { children: React.ReactNode }) {
    const currentPath = usePage().url.split('?')[0];

    return (
        <div className="mx-auto max-w-4xl space-y-8">
            <div>
                <p className="text-sm font-semibold text-blue-600">Account</p>
                <h1 className="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-100">Settings</h1>
                <p className="mt-1 text-gray-500 dark:text-gray-400">Manage your profile and account settings.</p>
            </div>
            <div className="grid gap-8 md:grid-cols-[180px_1fr]">
                <aside>
                    <nav aria-label="Settings navigation" className="space-y-1">
                        {sidebarNavItems.map((item) => (
                            <Link
                                key={item.url}
                                href={item.url}
                                className={`block rounded-md px-3 py-2 ${
                                    currentPath === item.url
                                        ? 'bg-blue-50 font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-200'
                                        : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                                }`}
                            >
                                {item.title}
                            </Link>
                        ))}
                    </nav>
                </aside>
                <div className="space-y-8">{children}</div>
            </div>
        </div>
    );
}
