import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AirlineLayout from '@/layouts/airline-layout';
import type { AirportData } from '@/types/airline';

export default function FlightCreate({ airports }: { airports: AirportData[] }) {
    const { data, setData, post, processing, errors } = useForm({
        flight_number: '',
        origin_airport_id: '',
        destination_airport_id: '',
        departure_time: '',
        base_fare: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(route('flights.store'));
    };

    return (
        <AirlineLayout>
            <Head title="Add flight" />
            <div className="mx-auto max-w-3xl space-y-6">
                <div>
                    <p className="text-primary text-sm font-semibold">Flight operations</p>
                    <h1 className="mt-1 text-3xl font-semibold tracking-tight">Add a flight</h1>
                    <p className="text-muted-foreground mt-2">Create a schedule and generate its available seat inventory.</p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Flight details</CardTitle>
                        <CardDescription>Airport codes are saved in uppercase.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-5">
                            <div className="grid gap-2">
                                <Label htmlFor="flight_number">Flight number</Label>
                                <Input
                                    id="flight_number"
                                    value={data.flight_number}
                                    onChange={(event) => setData('flight_number', event.target.value.toUpperCase())}
                                    maxLength={10}
                                    required
                                />
                                <InputError message={errors.flight_number} />
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="origin_airport_id">Origin airport</Label>
                                    <Select
                                        value={data.origin_airport_id || undefined}
                                        onValueChange={(value) => {
                                            setData('origin_airport_id', value);
                                            if (value === data.destination_airport_id) setData('destination_airport_id', '');
                                        }}
                                    >
                                        <SelectTrigger id="origin_airport_id">
                                            <SelectValue placeholder="Select origin" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {airports.map((airport) => (
                                                <SelectItem key={airport.code} value={airport.code}>
                                                    {airport.code} — {airport.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.origin_airport_id} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="destination_airport_id">Destination airport</Label>
                                    <Select
                                        value={data.destination_airport_id || undefined}
                                        onValueChange={(value) => setData('destination_airport_id', value)}
                                    >
                                        <SelectTrigger id="destination_airport_id">
                                            <SelectValue placeholder="Select destination" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {airports
                                                .filter((airport) => airport.code !== data.origin_airport_id)
                                                .map((airport) => (
                                                    <SelectItem key={airport.code} value={airport.code}>
                                                        {airport.code} — {airport.name}
                                                    </SelectItem>
                                                ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.destination_airport_id} />
                                </div>
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="departure_time">Departure time</Label>
                                    <Input
                                        id="departure_time"
                                        type="datetime-local"
                                        value={data.departure_time}
                                        onChange={(event) => setData('departure_time', event.target.value)}
                                        required
                                    />
                                    <InputError message={errors.departure_time} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="base_fare">Base fare (PHP)</Label>
                                    <Input
                                        id="base_fare"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={data.base_fare}
                                        onChange={(event) => setData('base_fare', event.target.value)}
                                        required
                                    />
                                    <InputError message={errors.base_fare} />
                                </div>
                            </div>

                            <div className="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:justify-end">
                                <Button asChild variant="outline">
                                    <Link href={route('flights.index')}>Cancel</Link>
                                </Button>
                                <Button type="submit" disabled={processing || airports.length < 2}>
                                    {processing ? 'Saving…' : 'Save flight'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AirlineLayout>
    );
}
