import { Head, Link, router } from '@inertiajs/react';
import { Plane, Search, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AirlineLayout from '@/layouts/airline-layout';
import type { SharedData } from '@/types';
import type { FlightData, Paginator } from '@/types/airline';
import { usePage } from '@inertiajs/react';

interface FlightsPageProps {
    flights: Paginator<FlightData>;
    search: string;
}

const currency = (amount: number | string) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(amount) || 0);

const dateTime = (value: string) => new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value));

export default function FlightsIndex({ flights, search }: FlightsPageProps) {
    const { auth } = usePage<SharedData>().props;
    const [query, setQuery] = useState(search);
    const [selectedFlight, setSelectedFlight] = useState<FlightData | null>(null);
    const [deleting, setDeleting] = useState(false);

    const searchFlights = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        router.get(route('flights.index'), { search: query.trim() }, { preserveState: true, preserveScroll: true, replace: true });
    };

    const deleteFlight = () => {
        if (!selectedFlight) return;
        setDeleting(true);
        router.delete(route('flights.destroy', selectedFlight.id), {
            onFinish: () => {
                setDeleting(false);
                setSelectedFlight(null);
            },
        });
    };

    return (
        <AirlineLayout>
            <Head title="Flights" />
            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-primary text-sm font-semibold">Flight operations</p>
                        <h1 className="mt-1 text-3xl font-semibold tracking-tight">Flights</h1>
                        <p className="text-muted-foreground mt-2">Browse schedules, routes, fares, and available seats.</p>
                    </div>
                    {auth.user.is_admin && (
                        <Button asChild>
                            <Link href={route('flights.create')}>Add flight</Link>
                        </Button>
                    )}
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Flight schedule</CardTitle>
                        <CardDescription>Search by flight number, airport, or city.</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-5">
                        <form onSubmit={searchFlights} className="flex flex-col gap-2 sm:flex-row">
                            <div className="relative flex-1">
                                <Search className="text-muted-foreground pointer-events-none absolute top-3 left-3 size-4" aria-hidden="true" />
                                <Input
                                    aria-label="Search flights"
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    placeholder="Flight number, airport, or city"
                                    className="pl-9"
                                />
                            </div>
                            <Button type="submit">Search</Button>
                            {search !== '' && (
                                <Button type="button" variant="outline" onClick={() => router.get(route('flights.index'))}>
                                    Clear
                                </Button>
                            )}
                        </form>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Flight</TableHead>
                                    <TableHead>Route</TableHead>
                                    <TableHead>Departure</TableHead>
                                    <TableHead>Base fare</TableHead>
                                    <TableHead>Seats</TableHead>
                                    {auth.user.is_admin && <TableHead className="text-right">Actions</TableHead>}
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {flights.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={auth.user.is_admin ? 6 : 5} className="h-32 text-center">
                                            <Plane className="text-muted-foreground mx-auto mb-2 size-6" aria-hidden="true" />
                                            <p className="font-medium">No flights found</p>
                                            <p className="text-muted-foreground mt-1 text-sm">
                                                {search ? 'Try another search.' : 'There are no flights scheduled yet.'}
                                            </p>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    flights.data.map((flight) => (
                                        <TableRow key={flight.id}>
                                            <TableCell className="font-semibold">{flight.flight_number}</TableCell>
                                            <TableCell>
                                                {flight.origin_airport?.code ?? flight.origin_airport_id} →{' '}
                                                {flight.destination_airport?.code ?? flight.destination_airport_id}
                                            </TableCell>
                                            <TableCell>{dateTime(flight.departure_time)}</TableCell>
                                            <TableCell>{currency(flight.base_fare)}</TableCell>
                                            <TableCell>{flight.available_seats_count ?? 0}</TableCell>
                                            {auth.user.is_admin && (
                                                <TableCell className="text-right">
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => setSelectedFlight(flight)}
                                                        aria-label={`Delete ${flight.flight_number}`}
                                                    >
                                                        <Trash2 className="size-4" aria-hidden="true" />
                                                        Delete
                                                    </Button>
                                                </TableCell>
                                            )}
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>

                        {flights.last_page > 1 && (
                            <nav aria-label="Flight pages" className="flex flex-wrap items-center justify-between gap-3 border-t pt-4">
                                <p className="text-muted-foreground text-sm">
                                    Showing {flights.from ?? 0}–{flights.to ?? 0} of {flights.total}
                                </p>
                                <div className="flex flex-wrap gap-1">
                                    {flights.links.map((link, index) => (
                                        <Button
                                            key={`${link.label}-${index}`}
                                            asChild={link.url !== null}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            disabled={link.url === null}
                                        >
                                            {link.url ? (
                                                <Link href={link.url} preserveScroll>
                                                    {link.label.replace('&laquo;', '‹').replace('&raquo;', '›')}
                                                </Link>
                                            ) : (
                                                <span>{link.label.replace('&laquo;', '‹').replace('&raquo;', '›')}</span>
                                            )}
                                        </Button>
                                    ))}
                                </div>
                            </nav>
                        )}
                    </CardContent>
                </Card>
            </div>

            <Dialog open={selectedFlight !== null} onOpenChange={(open) => !open && setSelectedFlight(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete flight?</DialogTitle>
                        <DialogDescription>
                            {selectedFlight?.flight_number} will be removed only if it has no bookings. Existing reservations are preserved.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline" disabled={deleting}>
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button variant="destructive" onClick={deleteFlight} disabled={deleting}>
                            {deleting ? 'Deleting…' : 'Delete flight'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AirlineLayout>
    );
}
