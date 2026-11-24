import { Head } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';
import AirlineLayout from '@/layouts/airline-layout';
import SettingsLayout from '@/layouts/settings/layout';

export default function Appearance() {
    return (
        <AirlineLayout>
            <Head title="Appearance settings" />

            <SettingsLayout>
                <section className="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <HeadingSmall title="Appearance settings" description="Choose how the airline dashboard is displayed." />
                    <AppearanceTabs />
                </section>
            </SettingsLayout>
        </AirlineLayout>
    );
}
