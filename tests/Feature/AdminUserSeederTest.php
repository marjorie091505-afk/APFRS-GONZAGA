<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_a_single_hashed_verified_admin_when_repeated(): void
    {
        config([
            'airline.admin.name' => 'System Administrator',
            'airline.admin.email' => 'admin@airline.com',
            'airline.admin.password' => 'test-admin-password',
        ]);

        $this->seed(DatabaseSeeder::class);
        $firstAdminId = User::where('email', 'admin@airline.com')->value('id');

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@airline.com')->firstOrFail();

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($firstAdminId, $admin->id);
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('test-admin-password', $admin->password));
        $this->assertNotNull($admin->email_verified_at);
    }
}
