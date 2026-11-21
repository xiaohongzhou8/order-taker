<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class OrderPrintingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_their_order_as_a_raw_receipt(): void
    {
        Process::fake();
        config(['services.printer.queue' => 'receipt-printer']);
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'order_number' => 'ORD-1',
            'customer_name' => "Alice\x1b",
            'price' => '17.00',
            'description' => 'No onions',
        ]);
        $order->items()->create([
            'item_number' => 'A1',
            'name' => 'Wrap',
            'quantity' => 2,
            'price' => '8.50',
        ]);

        $this->actingAs($user)->postJson("/orders/{$order->id}/print")->assertNoContent();

        Process::assertRan(function (PendingProcess $process): bool {
            return $process->command === ['lp', '-d', 'receipt-printer', '-o', 'raw']
                && str_contains($process->input, 'Order: ORD-1')
                && str_contains($process->input, 'Name:  Alice')
                && str_contains($process->input, 'Wrap')
                && str_contains($process->input, 'EUR 17.00')
                && str_contains($process->input, 'Comments: No onions')
                && ! str_contains($process->input, "\x1b")
                && str_ends_with($process->input, "\n\n\n\n\n\n\x1dV\x00");
        });
    }

    public function test_user_can_send_chinese_menu_names_to_the_kitchen_printer(): void
    {
        Process::fake();
        config(['services.printer.kitchen_queue' => 'kitchen-printer']);
        $menuItem = MenuItem::query()->create([
            'item_number' => '17a',
            'name' => 'Loempia met kip',
            'cn_name' => '鸡卷',
            'price' => '5.50',
        ]);
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'order_number' => 'ORD-KITCHEN',
            'customer_name' => 'Alice',
            'price' => '5.50',
        ]);
        $order->items()->create([
            'item_number' => $menuItem->item_number,
            'name' => $menuItem->name,
            'quantity' => 1,
            'price' => '5.50',
        ]);

        $this->actingAs($user)
            ->postJson("/orders/{$order->id}/print", ['destination' => 'kitchen'])
            ->assertNoContent();

        Process::assertRan(function (PendingProcess $process): bool {
            return $process->command === ['lp', '-d', 'kitchen-printer', '-o', 'raw']
                && str_contains($process->input, 'KITCHEN ORDER')
                && str_contains($process->input, '鸡卷')
                && ! str_contains($process->input, 'Loempia met kip');
        });
    }

    public function test_printing_requires_authentication_and_order_ownership(): void
    {
        Process::fake();
        $owner = User::factory()->create();
        $order = $owner->orders()->create(['order_number' => 'ORD-2', 'customer_name' => 'Alice']);

        $this->postJson("/orders/{$order->id}/print")->assertUnauthorized();
        $this->actingAs(User::factory()->create())
            ->postJson("/orders/{$order->id}/print")
            ->assertNotFound();

        Process::assertNothingRan();
    }

    public function test_printer_failure_is_reported_to_the_user(): void
    {
        Process::fake(fn () => Process::result(errorOutput: 'Printer unavailable', exitCode: 1));
        $user = User::factory()->create();
        $order = $user->orders()->create(['order_number' => 'ORD-3', 'customer_name' => 'Alice']);

        $this->actingAs($user)
            ->postJson("/orders/{$order->id}/print")
            ->assertStatus(502)
            ->assertJsonPath('message', 'Unable to send the receipt to the printer.');
    }
}
