import { Head, Link, usePage } from '@inertiajs/react';
import { Armchair, CalendarClock, CircleDollarSign, Plane, TicketCheck, UsersRound } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
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

export default function Dashboard() {
    const { auth, stats, recentBookings = [], upcomingFlights = [], upcomingBookings = [] } = usePage<SharedData & DashboardProps>().props;

    if (!auth.user.is_admin) {
        return (
            <AirlineLayout>
                <Head title="My flight schedule" />
                <div className="space-y-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-primary text-sm font-semibold">Passenger dashboard</p>
                            <h1 className="mt-1 text-3xl font-semibold tracking-tight">Welcome, {auth.user.name}</h1>
                            <p className="text-muted-foreground mt-2">Your upcoming reservations and departure information.</p>
                        </div>
                        <Button asChild>
                            <Link href={route('bookings.create')}>Book a flight</Link>
                        </Button>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Upcoming flights</CardTitle>
                            <CardDescription>Your confirmed reservations, ordered by departure time.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {upcomingBookings.length === 0 ? (
                                <div className="rounded-lg border border-dashed p-8 text-center">
                                    <Plane className="text-muted-foreground mx-auto size-8" aria-hidden="true" />
                                    <p className="mt-3 font-medium">No upcoming reservations</p>
                                    <p className="text-muted-foreground mt-1 text-sm">Book a seat to see your flight schedule here.</p>
                                    <Button asChild variant="outline" className="mt-4">
                                        <Link href={route('flights.index')}>Browse flights</Link>
                                    </Button>
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {upcomingBookings.map((booking) => {
                                        const flight = booking.flight;
                                        if (!flight) return null;

                                        const departure = new Date(flight.departure_time);
                                        const departingNow = Math.abs(departure.getTime() - Date.now()) <= 30 * 60 * 1000;

                                        return (
                                            <article
                                                key={booking.id}
                                                className="flex flex-col gap-4 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between"
                                            >
                                                <div className="space-y-1">
                                                    <p className="font-semibold">{flight.flight_number}</p>
                                                    <p className="text-lg font-medium">
                                                        {flight.origin_airport?.code ?? 'N/A'} <span className="text-muted-foreground">to</span>{' '}
                                                        {flight.destination_airport?.code ?? 'N/A'}
                                                    </p>
                                                    <p className="text-muted-foreground text-sm">
                                                        {flight.origin_airport?.name ?? 'Unknown airport'} (
                                                        {flight.origin_airport?.city ?? 'Unknown city'}) to{' '}
                                                        {flight.destination_airport?.name ?? 'Unknown airport'} (
                                                        {flight.destination_airport?.city ?? 'Unknown city'})
                                                    </p>
                                                    <p className="text-muted-foreground text-sm">
                                                        Seat {booking.seat?.seat_number ?? 'N/A'} · Ref {booking.booking_reference}
                                                    </p>
                                                </div>
                                                <div className="flex items-center justify-between gap-4 sm:justify-end">
                                                    <div className="text-left sm:text-right">
                                                        <p className="font-medium">{dateTime(flight.departure_time)}</p>
                                                        <p className="text-muted-foreground text-sm">Departure</p>
                                                    </div>
                                                    <Badge variant={departingNow ? 'default' : 'secondary'}>
                                                        {departingNow ? 'Departing now' : 'Upcoming'}
                                                    </Badge>
                                                </div>
                                            </article>
                                        );
                                    })}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <div className="flex justify-end">
                        <Button asChild variant="outline">
                            <Link href={route('bookings.index')}>View all my bookings</Link>
                        </Button>
                    </div>
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
