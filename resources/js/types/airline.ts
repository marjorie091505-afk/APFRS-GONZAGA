export interface AirportData {
    code: string;
    name: string;
    city: string;
    country: string;
}

export interface PassengerSeatData {
    id: number;
    flight_id: number;
    seat_number: string;
    seat_class: string;
    is_available: boolean;
}

export interface FlightData {
    id: number;
    flight_number: string;
    origin_airport?: AirportData | null;
    destination_airport?: AirportData | null;
    origin_airport_id: string;
    destination_airport_id: string;
    departure_time: string;
    base_fare: number | string;
    available_seats_count?: number;
    available_seats?: PassengerSeatData[];
    passenger_seats?: PassengerSeatData[];
}

export interface PassengerData {
    id: number;
    passport_number: string;
    full_name: string;
}

export interface BookingData {
    id: number;
    booking_reference: string;
    passenger_id: number;
    flight_id: number;
    passenger_seat_id: number | null;
    seat_class: string;
    baggage_fee: number | string;
    total_paid: number | string;
    passenger?: PassengerData | null;
    flight?: FlightData | null;
    seat?: PassengerSeatData | null;
    created_at: string;
}

export interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

export interface DashboardStats {
    flights: number;
    bookings: number;
    passengers: number;
    available_seats: number;
    revenue: number | string;
    upcoming_flights: number;
}
