<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_imports_local_menu_items_and_removes_items_not_in_the_file(): void
    {
        MenuItem::query()->create([
            'item_number' => 'stale-test-item',
            'name' => 'Stale item',
            'price' => '1.00',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('menu_items', 177);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '1',
            'name' => 'Soep Country Side',
            'price' => '5.00',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '184a',
            'name' => 'CHINESE RIJSTTAFEL (3p)',
            'price' => '64.20',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '17a',
            'name' => 'Loempia met kip',
            'cn_name' => '鸡卷',
            'price' => '5.50',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '17b',
            'name' => 'Grote loempia vegetarisch',
            'price' => '5.00',
        ]);
        $this->assertDatabaseHas('menu_items', [
            'item_number' => '17c',
            'name' => 'Loempia met garnalen',
            'price' => '6.50',
        ]);
        $this->assertDatabaseMissing('menu_items', ['item_number' => 'stale-test-item']);
    }

    public function test_seeder_does_not_create_orders_or_modify_existing_orders(): void
    {
        $existingUser = User::factory()->create();
        $existingOrder = $existingUser->orders()->create([
            'order_number' => 'ORD-1',
            'customer_name' => 'Customer order',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('orders', [
            'id' => $existingOrder->id,
            'order_number' => 'ORD-1',
            'customer_name' => 'Customer order',
        ]);
        $this->assertDatabaseCount('orders', 1);
    }
}
