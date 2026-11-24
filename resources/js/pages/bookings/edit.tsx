import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AirlineLayout from '@/layouts/airline-layout';
import type { BookingData, FlightData } from '@/types/airline';

interface EditBookingProps {
    booking: BookingData;
    flights: FlightData[];
}

const currency = (amount: number) =>
    new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number.isFinite(amount) ? amount : 0);

export default function BookingEdit({ booking, flights }: EditBookingProps) {
    const { data, setData, put, processing, errors } = useForm({
        passport_number: booking.passenger?.passport_number ?? '',
        full_name: booking.passenger?.full_name ?? '',
        flight_id: String(booking.flight_id),
        passenger_seat_id: String(booking.passenger_seat_id ?? ''),
        baggage_fee: String(booking.baggage_fee),
    });
    const selectedFlight = flights.find((flight) => String(flight.id) === data.flight_id);
    const seats = (selectedFlight?.passenger_seats ?? []).filter((seat) => seat.is_available || seat.id === booking.passenger_seat_id);
    const total = Number(selectedFlight?.base_fare ?? 0) + Number(data.baggage_fee || 0);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        put(route('bookings.update', booking.id));
    };

    return (
        <AirlineLayout>
            <Head title={`Edit ${booking.booking_reference}`} />
            <div className="mx-auto max-w-3xl space-y-6">
                <div>
                    <p className="text-primary text-sm font-semibold">Reservation {booking.booking_reference}</p>
                    <h1 className="mt-1 text-3xl font-semibold tracking-tight">Edit booking</h1>
                    <p className="text-muted-foreground mt-2">Update the passenger details, flight, or assigned seat.</p>
                </div>

                <form onSubmit={submit} className="space-y-5">
                    <Card>
                        <CardHeader>
                            <CardTitle>Passenger information</CardTitle>
                            <CardDescription>Passport numbers are saved in uppercase.</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-5 sm:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="passport_number">Passport number</Label>
                                <Input
                                    id="passport_number"
                                    value={data.passport_number}
                                    onChange={(event) => setData('passport_number', event.target.value.toUpperCase())}
                                    maxLength={20}
                                    required
                                />
                                <InputError message={errors.passport_number} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="full_name">Full name</Label>
                                <Input
                                    id="full_name"
                                    value={data.full_name}
                                    onChange={(event) => setData('full_name', event.target.value)}
                                    maxLength={150}
                                    required
                                />
                                <InputError message={errors.full_name} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Flight and seat</CardTitle>
                            <CardDescription>The seat list updates to match the selected flight.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-5">
                            <div className="grid gap-2">
                                <Label htmlFor="flight_id">Flight</Label>
                                <Select
                                    value={data.flight_id}
                                    onValueChange={(value) => {
                                        setData('flight_id', value);
                                        setData('passenger_seat_id', '');
                                    }}
                                >
                                    <SelectTrigger id="flight_id">
                                        <SelectValue placeholder="Choose a flight" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {flights.map((flight) => (
                                            <SelectItem key={flight.id} value={String(flight.id)}>
                                                {flight.flight_number} · {flight.origin_airport?.code ?? flight.origin_airport_id} →{' '}
                                                {flight.destination_airport?.code ?? flight.destination_airport_id}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.flight_id} />
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="passenger_seat_id">Seat</Label>
                                <Select
                                    value={data.passenger_seat_id || undefined}
                                    onValueChange={(value) => setData('passenger_seat_id', value)}
                                    disabled={!selectedFlight || seats.length === 0}
                                >
                                    <SelectTrigger id="passenger_seat_id">
                                        <SelectValue placeholder="Choose an available seat" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {seats.map((seat) => (
                                            <SelectItem key={seat.id} value={String(seat.id)}>
                                                Seat {seat.seat_number} · {seat.seat_class}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.passenger_seat_id} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Payment</CardTitle>
                            <CardDescription>The total is recalculated from the selected flight fare and baggage fee.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2 sm:max-w-xs">
                                <Label htmlFor="baggage_fee">Baggage fee (PHP)</Label>
                                <Input
                                    id="baggage_fee"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={data.baggage_fee}
                                    onChange={(event) => setData('baggage_fee', event.target.value)}
                                />
                                <InputError message={errors.baggage_fee} />
                            </div>
                            <div className="flex items-center justify-between border-t pt-4 text-lg font-semibold">
                                <span>Total paid</span>
                                <span>{currency(total)}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button asChild variant="outline">
                            <Link href={route('bookings.index')}>Cancel</Link>
                        </Button>
                        <Button type="submit" disabled={processing || !data.passenger_seat_id}>
                            {processing ? 'Saving…' : 'Save changes'}
                        </Button>
                    </div>
                </form>
            </div>
        </AirlineLayout>
    );
}
