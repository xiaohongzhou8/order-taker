<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_shows_login_to_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertViewIs('login');
    }

    public function test_the_application_redirects_authenticated_users_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirectToRoute('dashboard');
    }

    public function test_guest_can_view_login_and_register_pages(): void
    {
        $this->get('/login')->assertOk()->assertViewIs('login');
        $this->get('/register')->assertOk()->assertViewIs('register');
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirectToRoute('login');
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_dashboard_requires_authentication_and_loads_orders(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'order_number' => '1001',
            'customer_name' => 'Example Customer',
        ]);

        $this->get('/dashboard')->assertRedirectToRoute('login');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertViewIs('layouts.dashboard')
            ->assertSee($order->customer_name);
    }

    public function test_dashboard_filters_orders_by_selected_date(): void
    {
        $user = User::factory()->create();
        $todayOrder = $user->orders()->create([
            'order_number' => 'ORD-TODAY',
            'customer_name' => 'Today Customer',
        ]);
        $yesterdayOrder = $user->orders()->create([
            'order_number' => 'ORD-YESTERDAY',
            'customer_name' => 'Yesterday Customer',
        ]);
        $todayOrder->forceFill(['created_at' => Carbon::parse('2026-11-12 10:00:00')])->save();
        $yesterdayOrder->forceFill(['created_at' => Carbon::parse('2026-11-11 10:00:00')])->save();

        $this->actingAs($user)
            ->get('/dashboard?date=2026-11-11')
            ->assertOk()
            ->assertSee('Yesterday Customer')
            ->assertDontSee('Today Customer');

        $newestOrder = $user->orders()->create([
            'order_number' => 'ORD-NEWEST',
            'customer_name' => 'Newest Customer',
        ]);
        $oldestOrder = $user->orders()->create([
            'order_number' => 'ORD-OLDEST',
            'customer_name' => 'Oldest Customer',
        ]);
        $newestOrder->forceFill(['created_at' => Carbon::parse('2026-11-11 18:00:00')])->save();
        $oldestOrder->forceFill(['created_at' => Carbon::parse('2026-11-11 08:00:00')])->save();

        $this->actingAs($user)
            ->get('/dashboard?date=2026-11-11')
            ->assertSeeInOrder(['Newest Customer', 'Oldest Customer']);
    }

    public function test_logout_requires_post_and_ends_the_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/logout')->assertMethodNotAllowed();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirectToRoute('home');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_create_an_order(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/orders', [
            'customer_name' => 'New Customer',
            'price' => '45.99',
            'description' => 'First order',
        ]);

        $response->assertRedirectToRoute('dashboard');
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_name' => 'New Customer',
            'description' => 'First order',
        ]);
    }

    public function test_dashboard_order_creation_supports_single_page_requests(): void
    {
        $user = User::factory()->create();
        MenuItem::create([
            'item_number' => 1,
            'name' => 'Classic Cheeseburger',
            'price' => '12.50',
        ]);

        $this->actingAs($user)
            ->get('/orders/create', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertRedirectToRoute('dashboard');

        $this->actingAs($user)
            ->postJson('/orders', [
                'customer_name' => 'SPA Customer',
                'price' => '29.95',
                'description' => 'Created without a page reload',
                'items' => [
                    ['item_number' => 1, 'quantity' => 2],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('order.customer_name', 'SPA Customer')
            ->assertJsonPath('order.price', '25.00')
            ->assertJsonPath('order.items.0.name', 'Classic Cheeseburger')
            ->assertJsonPath('order.items.0.quantity', 2);
    }

    public function test_new_order_can_start_as_an_empty_order_details_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/orders', [
                'customer_name' => 'New order',
                'price' => 0,
            ])
            ->assertCreated()
            ->assertJsonPath('order.customer_name', 'New order')
            ->assertJsonPath('order.price', '0.00')
            ->assertJsonCount(0, 'order.items');
    }

    public function test_user_can_add_a_menu_item_to_an_order_by_number(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'order_number' => 'ORD-ITEM',
            'customer_name' => 'Item Customer',
        ]);
        MenuItem::create([
            'item_number' => 7,
            'name' => 'Chicken Wrap',
            'price' => '8.50',
        ]);

        $this->actingAs($user)
            ->postJson("/orders/{$order->id}/items", ['item_number' => 7])
            ->assertOk()
            ->assertJsonPath('order.items.0.item_number', 7)
            ->assertJsonPath('order.items.0.quantity', 1)
            ->assertJsonPath('order.price', '8.50');

        $this->actingAs($user)
            ->postJson("/orders/{$order->id}/items", ['item_number' => 7])
            ->assertOk()
            ->assertJsonPath('order.items.0.quantity', 2)
            ->assertJsonPath('order.price', '17.00');
    }

    public function test_authenticated_user_can_delete_their_order(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'order_number' => 'ORD-DELETE',
            'customer_name' => 'Delete Customer',
        ]);

        $this->actingAs($user)
            ->deleteJson("/orders/{$order->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_authenticated_user_can_view_their_order_details(): void
    {
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'order_number' => 'ORD-VIEW',
            'customer_name' => 'View Customer',
            'price' => '89.50',
            'description' => 'Order description',
            'notes' => ['Call before delivery'],
        ]);
        $order->items()->createMany([
            ['item_number' => 20, 'name' => 'Example product', 'price' => '25.00'],
            ['item_number' => 10, 'name' => 'Example add-on', 'price' => '10.50'],
        ]);

        $this->actingAs($user)
            ->getJson("/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('order.customer_name', 'View Customer')
            ->assertJsonPath('order.price', '89.50')
            ->assertJsonPath('order.description', 'Order description')
            ->assertJsonPath('order.items.0.name', 'Example add-on')
            ->assertJsonPath('order.items.0.item_number', 10)
            ->assertJsonPath('order.items.0.quantity', 1)
            ->assertJsonPath('order.items.1.price', '25.00')
            ->assertJsonPath('order.items.1.item_number', 20);

        $firstItem = $order->items()->where('item_number', 10)->firstOrFail();
        $secondItem = $order->items()->where('item_number', 20)->firstOrFail();

        $this->actingAs($user)
            ->patchJson("/orders/{$order->id}/items/{$firstItem->id}", [
                'quantity' => 3,
            ])
            ->assertOk()
            ->assertJsonPath('order.items.0.quantity', 3)
            ->assertJsonPath('order.price', '56.50');

        $this->actingAs($user)
            ->deleteJson("/orders/{$order->id}/items/{$secondItem->id}")
            ->assertOk()
            ->assertJsonCount(1, 'order.items');

        $this->actingAs($user)
            ->patchJson("/orders/{$order->id}", [
                'customer_name' => 'Updated Name',
                'description' => 'Updated comments',
            ])
            ->assertOk()
            ->assertJsonPath('order.customer_name', 'Updated Name')
            ->assertJsonPath('order.description', 'Updated comments');
    }
}
