<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AirportOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_airports_are_available_when_creating_a_flight(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get('/flights/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('flights/create')
                ->has('airports', 4)
                ->where('airports.0.code', 'CEB')
                ->where('airports.2.code', 'MNL')
            );
    }
}
