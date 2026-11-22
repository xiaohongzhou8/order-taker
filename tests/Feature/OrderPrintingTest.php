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

    public function test_user_can_submit_their_order_as_a_receipt(): void
    {
        $printedFile = null;
        $printData = null;
        Process::fake(function (PendingProcess $process) use (&$printedFile, &$printData) {
            $printedFile = $process->command[9] ?? null;
            $printData = is_string($printedFile) ? file_get_contents($printedFile) : false;

            return Process::result();
        });
        config(['services.printer.queue' => 'receipt-printer']);
        $user = User::factory()->create();
        $order = $user->orders()->create([
            'order_number' => 'ORD-1',
            'customer_name' => "小明\x1b",
            'price' => '17.00',
            'description' => '不要洋葱',
        ]);
        $order->items()->create([
            'item_number' => 'A1',
            'name' => '鸡肉卷',
            'quantity' => 2,
            'price' => '8.50',
        ]);

        $this->actingAs($user)->postJson("/orders/{$order->id}/print")->assertNoContent();

        Process::assertRan(function (PendingProcess $process) use (&$printedFile): bool {
            return is_string($printedFile)
                && $process->command === ['lp', '-d', 'receipt-printer', '-o', 'cpi=16', '-o', 'page-left=0', '-o', 'page-right=0', $printedFile];
        });
        $this->assertIsString($printedFile);
        $this->assertFileDoesNotExist($printedFile);
        $this->assertIsString($printData);
        $this->assertStringStartsWith(str_repeat('=', 40)."\n", $printData);
        $this->assertStringContainsString('Order: ORD-1', $printData);
        $this->assertStringContainsString('Name:  小明 ', $printData);
        $this->assertStringContainsString('鸡肉卷', $printData);
        $this->assertStringContainsString('EUR 17.00', $printData);
        $this->assertStringContainsString('Comments: 不要洋葱', $printData);
        $this->assertStringNotContainsString("\x1b", $printData);
        $this->assertStringEndsWith("\n\n\n\n\n\n", $printData);
    }

    public function test_user_can_send_chinese_menu_names_to_the_kitchen_printer(): void
    {
        $printedFile = null;
        $printData = null;
        Process::fake(function (PendingProcess $process) use (&$printedFile, &$printData) {
            $printedFile = $process->command[9] ?? null;
            $printData = is_string($printedFile) ? file_get_contents($printedFile) : false;

            return Process::result();
        });
        config(['services.printer.kitchen_queue' => 'kitchen-printer']);
        $menuItem = MenuItem::query()->create([
            'item_number' => '17a',
            'name' => 'Loempia met kip',
            'cn_name' => '鸡卷六七八九',
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

        Process::assertRan(function (PendingProcess $process) use (&$printedFile): bool {
            return is_string($printedFile)
                && $process->command === ['lp', '-d', 'kitchen-printer', '-o', 'cpi=14', '-o', 'page-left=0', '-o', 'page-right=0', $printedFile];
        });
        $this->assertIsString($printedFile);
        $this->assertFileDoesNotExist($printedFile);
        $this->assertIsString($printData);
        $this->assertStringContainsString('KITCHEN ORDER', $printData);
        $this->assertStringContainsString('鸡卷六七八', $printData);
        $this->assertStringNotContainsString('九', $printData);
        $this->assertStringNotContainsString('Loempia met kip', $printData);
        foreach (explode("\n", $printData) as $printLine) {
            $this->assertLessThanOrEqual(40, mb_strwidth($printLine, 'UTF-8'));
        }
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
