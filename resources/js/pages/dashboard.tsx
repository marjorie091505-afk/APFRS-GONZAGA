import { Head, Link, usePage } from '@inertiajs/react';
import { Armchair, CalendarClock, ChevronLeft, ChevronRight, CircleDollarSign, Plane, TicketCheck, UsersRound } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AirlineLayout from '@/layouts/airline-layout';
import type { SharedData } from '@/types';
import type { BookingData, DashboardStats, FlightData } from '@/types/airline';

interface DashboardProps {
    stats?: DashboardStats;
    recentBookings?: BookingData[];
    upcomingFlights?: FlightData[];
    upcomingBookings?: BookingData[];
}

const currency = (amount: number | string) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(amount) || 0);

const dateTime = (value: string) => new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));

const destinations = [
    {
        city: 'Cebu City',
        duration: '0h 55m, direct',
        dates: 'Sun 12/6 · Sat 12/12',
        price: '₱3,826',
        image: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85',
    },
    {
        city: 'Manila',
        duration: '1h 25m, direct',
        dates: 'Tue 12/1 · Sat 12/5',
        price: '₱4,974',
        image: 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=900&q=85',
    },
    {
        city: 'Panglao',
        duration: '1h 5m, direct',
        dates: 'Mon 12/7 · Fri 12/11',
        price: '₱5,484',
        image: 'https://images.unsplash.com/photo-1547471080-7cc2caa47102?auto=format&fit=crop&w=900&q=85',
    },
    {
        city: 'Puerto Princesa',
        duration: '1h 10m, direct',
        dates: 'Thu 12/10 · Mon 12/14',
        price: '₱5,548',
        image: 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=900&q=85',
    },
];

export default function Dashboard() {
    const { auth, stats, recentBookings = [], upcomingFlights = [], upcomingBookings = [] } = usePage<SharedData & DashboardProps>().props;
    const [destinationOffset, setDestinationOffset] = useState(0);

    if (!auth.user.is_admin) {
        const visibleDestinations = [...destinations.slice(destinationOffset), ...destinations.slice(0, destinationOffset)];

        return (
            <AirlineLayout>
                <Head title="My flight schedule" />
                <div className="space-y-6">
                    <section aria-label="Popular destinations" className="space-y-4">
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                            {visibleDestinations.map((destination) => (
                                <Link
                                    key={destination.city}
                                    href={`${route('flights.index')}?search=${encodeURIComponent(destination.city)}`}
                                    className="group overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_12px_28px_rgba(15,23,42,0.12)] transition-transform hover:-translate-y-1 focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:outline-none"
                                >
                                    <div className="p-3 pb-0">
                                        <img
                                            src={destination.image}
                                            alt={`${destination.city} beach destination`}
                                            className="h-52 w-full rounded-[22px] object-cover"
                                        />
                                    </div>
                                    <div className="flex h-56 flex-col px-5 pt-6 pb-6">
                                        <h2 className="text-[26px] leading-none font-black tracking-tight text-slate-950">{destination.city}</h2>
                                        <p className="mt-3 font-semibold text-slate-950">{destination.duration}</p>
                                        <p className="mt-1 text-sm font-medium text-slate-950">{destination.dates}</p>
                                        <p className="mt-auto text-[26px] leading-none font-black tracking-tight text-slate-950">
                                            from {destination.price}
                                        </p>
                                    </div>
                                </Link>
                            ))}
                        </div>
                        <div className="flex justify-end gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                className="size-12 rounded-full border-slate-200 bg-white text-slate-500 shadow-sm"
                                aria-label="Previous destinations"
                                onClick={() => setDestinationOffset((current) => (current - 1 + destinations.length) % destinations.length)}
                            >
                                <ChevronLeft className="size-5" aria-hidden="true" />
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="icon"
                                className="size-12 rounded-full border-slate-200 bg-white text-slate-950 shadow-sm"
                                aria-label="Next destinations"
                                onClick={() => setDestinationOffset((current) => (current + 1) % destinations.length)}
                            >
                                <ChevronRight className="size-5" aria-hidden="true" />
                            </Button>
                        </div>
                    </section>

                </div>
            </AirlineLayout>
        );
    }

    const cards = [
        { label: 'Flights', value: stats?.flights ?? 0, icon: Plane, href: route('flights.index') },
        { label: 'Bookings', value: stats?.bookings ?? 0, icon: TicketCheck, href: route('bookings.index') },
        { label: 'Passengers', value: stats?.passengers ?? 0, icon: UsersRound, href: route('bookings.index') },
        { label: 'Seats available', value: stats?.available_seats ?? 0, icon: Armchair, href: route('flights.index') },
        { label: 'Upcoming flights', value: stats?.upcoming_flights ?? 0, icon: CalendarClock, href: route('flights.index') },
        { label: 'Revenue', value: currency(stats?.revenue ?? 0), icon: CircleDollarSign, href: route('bookings.index') },
    ];

    return (
        <AirlineLayout>
            <Head title="Dashboard" />
            <div className="space-y-7">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-primary text-sm font-semibold">Airline operations</p>
                        <h1 className="mt-1 text-3xl font-semibold tracking-tight">Dashboard</h1>
                        <p className="text-muted-foreground mt-2">Live overview of your flights, reservations, seats, and revenue.</p>
                    </div>
                    <Button asChild>
                        <Link href={route('flights.create')}>Add flight</Link>
                    </Button>
                </div>

                <section aria-label="Airline statistics" className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {cards.map(({ label, value, icon: Icon, href }) => (
                        <Link key={label} href={href} className="focus-visible:ring-ring rounded-xl focus-visible:ring-2 focus-visible:outline-none">
                            <Card className="hover:border-primary/40 h-full transition-colors">
                                <CardHeader className="flex-row items-center justify-between space-y-0 pb-2">
                                    <CardDescription>{label}</CardDescription>
                                    <Icon className="text-muted-foreground size-4" aria-hidden="true" />
                                </CardHeader>
                                <CardContent>
                                    <p className="text-2xl font-semibold tracking-tight">{value}</p>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </section>

                <div className="grid gap-6 xl:grid-cols-2">
                    <Card>
                        <CardHeader className="flex-row items-center justify-between">
                            <div>
                                <CardTitle>Recent bookings</CardTitle>
                                <CardDescription>Latest reservations in the system.</CardDescription>
                            </div>
                            <Button asChild variant="outline" size="sm">
                                <Link href={route('bookings.index')}>View all</Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Reference</TableHead>
                                        <TableHead>Passenger</TableHead>
                                        <TableHead>Flight</TableHead>
                                        <TableHead>Departure</TableHead>
                                        <TableHead className="text-right">Paid</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {recentBookings.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={5} className="text-muted-foreground h-24 text-center">
                                                No bookings yet.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        recentBookings.map((booking) => (
                                            <TableRow key={booking.id}>
                                                <TableCell className="font-mono text-xs">{booking.booking_reference}</TableCell>
                                                <TableCell>{booking.passenger?.full_name ?? 'Unknown passenger'}</TableCell>
                                                <TableCell>
                                                    <p className="font-medium">{booking.flight?.flight_number ?? 'Unknown flight'}</p>
                                                    <p className="text-muted-foreground text-xs">
                                                        {booking.flight?.origin_airport?.code ?? 'N/A'} (
                                                        {booking.flight?.origin_airport?.city ?? 'Unknown'}) →{' '}
                                                        {booking.flight?.destination_airport?.code ?? 'N/A'} (
                                                        {booking.flight?.destination_airport?.city ?? 'Unknown'})
                                                    </p>
                                                </TableCell>
                                                <TableCell>{booking.flight ? dateTime(booking.flight.departure_time) : 'N/A'}</TableCell>
                                                <TableCell className="text-right font-medium">{currency(booking.total_paid)}</TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex-row items-center justify-between">
                            <div>
                                <CardTitle>Next departures</CardTitle>
                                <CardDescription>Flights scheduled to depart soon.</CardDescription>
                            </div>
                            <Button asChild variant="outline" size="sm">
                                <Link href={route('flights.index')}>View all</Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {upcomingFlights.length === 0 ? (
                                <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center text-sm">
                                    No upcoming flights are scheduled.
                                </div>
                            ) : (
                                <div className="divide-y">
                                    {upcomingFlights.map((flight) => (
                                        <div key={flight.id} className="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                            <div>
                                                <p className="font-semibold">{flight.flight_number}</p>
                                                <p className="text-muted-foreground text-sm">
                                                    {flight.origin_airport?.code ?? 'N/A'} → {flight.destination_airport?.code ?? 'N/A'}
                                                </p>
                                            </div>
                                            <p className="text-right text-sm">{dateTime(flight.departure_time)}</p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AirlineLayout>
    );
}
