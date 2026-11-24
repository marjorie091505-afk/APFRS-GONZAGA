import { Head, Link, router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AirlineLayout from '@/layouts/airline-layout';
import type { SharedData } from '@/types';
import type { BookingData, Paginator } from '@/types/airline';
import { usePage } from '@inertiajs/react';

interface BookingsPageProps {
    bookings: Paginator<BookingData>;
}

const currency = (amount: number | string) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(amount) || 0);

export default function BookingsIndex({ bookings }: BookingsPageProps) {
    const { auth } = usePage<SharedData>().props;
    const [selectedBooking, setSelectedBooking] = useState<BookingData | null>(null);
    const [deleting, setDeleting] = useState(false);

    const deleteBooking = () => {
        if (!selectedBooking) return;
        setDeleting(true);
        router.delete(route('bookings.destroy', selectedBooking.id), {
            onFinish: () => {
                setDeleting(false);
                setSelectedBooking(null);
            },
        });
    };

    return (
        <AirlineLayout>
            <Head title="Bookings" />
            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="text-primary text-sm font-semibold">Reservations</p>
                        <h1 className="mt-1 text-3xl font-semibold tracking-tight">{auth.user.is_admin ? 'All bookings' : 'My bookings'}</h1>
                        <p className="text-muted-foreground mt-2">
                            {auth.user.is_admin
                                ? 'Review passenger reservations and payment totals.'
                                : 'Review your reservation details and flight schedule.'}
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={route('bookings.create')}>New booking</Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Reservations</CardTitle>
                        <CardDescription>
                            {bookings.total} {bookings.total === 1 ? 'booking' : 'bookings'} found.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Reference</TableHead>
                                    <TableHead>Passenger</TableHead>
                                    <TableHead>Flight</TableHead>
                                    <TableHead>Seat</TableHead>
                                    <TableHead className="text-right">Total paid</TableHead>
                                    {auth.user.is_admin && <TableHead className="text-right">Actions</TableHead>}
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {bookings.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={auth.user.is_admin ? 6 : 5} className="h-32 text-center">
                                            <p className="font-medium">No bookings yet</p>
                                            <p className="text-muted-foreground mt-1 text-sm">Your reservations will appear here.</p>
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    bookings.data.map((booking) => (
                                        <TableRow key={booking.id}>
                                            <TableCell className="font-mono text-xs">{booking.booking_reference}</TableCell>
                                            <TableCell>
                                                <div className="font-medium">{booking.passenger?.full_name ?? 'Unknown passenger'}</div>
                                                <div className="text-muted-foreground text-xs">
                                                    {booking.passenger?.passport_number ?? 'No passport'}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="font-medium">{booking.flight?.flight_number ?? 'Unknown flight'}</div>
                                                <div className="text-muted-foreground text-xs">
                                                    {booking.flight?.origin_airport?.code ?? 'N/A'} →{' '}
                                                    {booking.flight?.destination_airport?.code ?? 'N/A'}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {booking.seat?.seat_number ?? 'N/A'} · {booking.seat?.seat_class ?? booking.seat_class}
                                            </TableCell>
                                            <TableCell className="text-right font-medium">{currency(booking.total_paid)}</TableCell>
                                            {auth.user.is_admin && (
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        <Button asChild variant="outline" size="sm">
                                                            <Link href={route('bookings.edit', booking.id)}>Edit</Link>
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() => setSelectedBooking(booking)}
                                                            aria-label={`Delete booking ${booking.booking_reference}`}
                                                        >
                                                            <Trash2 className="size-4" aria-hidden="true" />
                                                            Delete
                                                        </Button>
                                                    </div>
                                                </TableCell>
                                            )}
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>

                        {bookings.last_page > 1 && (
                            <nav aria-label="Booking pages" className="flex flex-wrap items-center justify-between gap-3 border-t pt-4">
                                <p className="text-muted-foreground text-sm">
                                    Showing {bookings.from ?? 0}–{bookings.to ?? 0} of {bookings.total}
                                </p>
                                <div className="flex flex-wrap gap-1">
                                    {bookings.links.map((link, index) => (
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

            <Dialog open={selectedBooking !== null} onOpenChange={(open) => !open && setSelectedBooking(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete booking?</DialogTitle>
                        <DialogDescription>This will cancel reservation {selectedBooking?.booking_reference} and release its seat.</DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <DialogClose asChild>
                            <Button variant="outline" disabled={deleting}>
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button variant="destructive" onClick={deleteBooking} disabled={deleting}>
                            {deleting ? 'Deleting…' : 'Delete booking'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AirlineLayout>
    );
}
