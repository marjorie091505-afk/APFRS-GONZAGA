<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AirportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AirportOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_airports_are_available_when_creating_a_flight(): void
    {
        $this->seed(AirportSeeder::class);
        $this->actingAs(User::factory()->create());

        $this->get('/flights/create')
            ->assertOk()
            ->assertSee('MNL - Ninoy Aquino International Airport')
            ->assertSee('CEB - Mactan-Cebu International Airport');
    }
}
