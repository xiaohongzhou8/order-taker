<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function create(): Response
    {
        return redirect()->route('dashboard');
    }

    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'description' => ['nullable', 'string', 'max:5000'],
            'items' => ['nullable', 'array'],
            'items.*.item_number' => ['required', 'string', 'max:255', 'exists:menu_items,item_number'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $menuItems = MenuItem::query()
            ->whereIn('item_number', collect($validated['items'] ?? [])->pluck('item_number'))
            ->get()
            ->keyBy('item_number');
        $items = collect($validated['items'] ?? [])->map(function (array $item) use ($menuItems): array {
            $menuItem = $menuItems->get($item['item_number']);

            return [
                'item_number' => $menuItem->item_number,
                'name' => $menuItem->name,
                'quantity' => $item['quantity'],
                'price' => $menuItem->price,
            ];
        });

        $order = $request->user()->orders()->create([
            ...$validated,
            'price' => $items->sum(fn (array $item): float => (float) $item['price'] * $item['quantity']) ?: $validated['price'],
            'order_number' => $this->generateOrderNumber(),
        ]);
        $order->items()->createMany($items->all());

        if ($request->expectsJson()) {
            return response()->json(['order' => $this->orderPayload($order->fresh('items'))], 201);
        }

        return redirect()->route('dashboard')
            ->with('success', "Order {$order->order_number} created.");
    }

    public function destroy(Request $request, int $order): Response
    {
        $ownedOrder = $request->user()->orders()->whereKey($order)->firstOrFail();
        $ownedOrder->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect()->route('dashboard');
    }

    public function update(Request $request, int $order): Response
    {
        $ownedOrder = $request->user()->orders()->whereKey($order)->firstOrFail();
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $ownedOrder->update($validated);

        return response()->json([
            'order' => $ownedOrder->only(['id', 'order_number', 'customer_name', 'price', 'description', 'notes']),
        ]);
    }

    public function show(Request $request, int $order): Response
    {
        $ownedOrder = $request->user()->orders()
            ->with(['items' => fn ($query) => $query->orderBy('item_number')])
            ->whereKey($order)
            ->firstOrFail();

        return response()->json([
            'order' => [
                ...$ownedOrder->only([
                    'id',
                    'order_number',
                    'customer_name',
                    'price',
                    'description',
                    'notes',
                ]),
                'items' => $ownedOrder->items->map(fn ($item) => $item->only([
                    'id',
                    'item_number',
                    'name',
                    'quantity',
                    'price',
                ]))->values(),
            ],
        ]);
    }

    public function print(Request $request, int $order): Response
    {
        $validated = $request->validate([
            'destination' => ['sometimes', 'in:kitchen'],
        ]);
        $isKitchenPrint = ($validated['destination'] ?? null) === 'kitchen';
        $ownedOrder = $request->user()->orders()
            ->with(['items' => fn ($query) => $query->orderBy('item_number')])
            ->whereKey($order)
            ->firstOrFail();
        $cnNames = $isKitchenPrint
            ? MenuItem::query()
                ->whereIn('item_number', $ownedOrder->items->pluck('item_number'))
                ->pluck('cn_name', 'item_number')
            : collect();

        $text = fn (string $value): string => preg_replace('/[\x00-\x1F\x7F]/', ' ', $value);
        $money = fn ($value): string => 'EUR '.number_format((float) $value, 2, '.', '');
        $queue = $isKitchenPrint ? config('services.printer.kitchen_queue') : config('services.printer.queue');
        $width = 40;
        $lines = [
            str_repeat('=', $width),
            $isKitchenPrint ? 'KITCHEN ORDER' : 'FOOD ORDER',
            str_repeat('=', $width),
            'Order: '.$text($ownedOrder->order_number),
            'Name:  '.$text($ownedOrder->customer_name),
            str_repeat('-', $width),
            'Qty  Nr   Item              Total',
            str_repeat('-', $width),
        ];

        foreach ($ownedOrder->items as $item) {
            // Determine item name (using Chinese name for kitchen orders)
            $itemName = $isKitchenPrint
                ? (string) $cnNames->get($item->item_number, '未知')
                : $text($item->name);

            $itemName = mb_strimwidth($itemName, 0, 20, '', 'UTF-8');
            $itemNameWidth = mb_strwidth($itemName, 'UTF-8');

            $lines[] = str_pad((string) $item->quantity, 3, ' ', STR_PAD_LEFT).'  '
                .str_pad($text($item->item_number), 3, ' ', STR_PAD_LEFT).'  '
                .$itemName.str_repeat(' ', max(0, 20 - $itemNameWidth)).' '
                .str_pad($money($item->price * $item->quantity), 9, ' ', STR_PAD_LEFT);
        }

        if ($ownedOrder->items->isEmpty()) {
            $lines[] = '  No items';
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = 'TOTAL:'.str_pad($money($ownedOrder->price), $width - 6, ' ', STR_PAD_LEFT);

        if ($ownedOrder->description) {
            $lines[] = 'Comments: '.$text($ownedOrder->description);
        }

        $lines[] = str_repeat('=', $width);
        $printData = implode("\n", $lines)."\n\n\n\n\n\n";

        try {
            $result = Process::timeout(15)
                ->input($printData)
                ->run(['lp', '-d', $queue, '-o', 'cpi=16', '-o', 'page-left=0', '-o', 'page-right=0']);

            if ($result->failed()) {
                report(new \RuntimeException('Printer submission failed: '.$result->errorOutput()));

                return response()->json(['message' => 'Unable to send the receipt to the printer.'], 502);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Unable to send the receipt to the printer.'], 502);
        }

        return response()->noContent();
    }

    public function updateItem(Request $request, int $order, int $item): Response
    {
        $ownedOrder = $request->user()->orders()->whereKey($order)->firstOrFail();
        $orderItem = $ownedOrder->items()->whereKey($item)->firstOrFail();
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $orderItem->update($validated);
        $this->refreshOrderTotal($ownedOrder);

        return $this->orderDetailsResponse($ownedOrder->fresh('items'));
    }

    public function addItem(Request $request, int $order): Response
    {
        $ownedOrder = $request->user()->orders()->whereKey($order)->firstOrFail();
        $validated = $request->validate([
            'item_number' => ['required', 'string', 'max:255', 'exists:menu_items,item_number'],
        ]);
        $menuItem = MenuItem::query()
            ->where('item_number', $validated['item_number'])
            ->firstOrFail();
        $orderItem = $ownedOrder->items()->firstOrNew([
            'item_number' => $menuItem->item_number,
        ]);
        $orderItem->name = $menuItem->name;
        $orderItem->price = $menuItem->price;
        $orderItem->quantity = ($orderItem->quantity ?? 0) + 1;
        $orderItem->save();
        $this->refreshOrderTotal($ownedOrder);

        return $this->orderDetailsResponse($ownedOrder->fresh('items'));
    }

    public function destroyItem(Request $request, int $order, int $item): Response
    {
        $ownedOrder = $request->user()->orders()->whereKey($order)->firstOrFail();
        $ownedOrder->items()->whereKey($item)->firstOrFail()->delete();
        $this->refreshOrderTotal($ownedOrder);

        return $this->orderDetailsResponse($ownedOrder->fresh('items'));
    }

    private function refreshOrderTotal(Order $order): void
    {
        $order->update([
            'price' => $order->items()
                ->selectRaw('COALESCE(SUM(quantity * price), 0) as total')
                ->value('total'),
        ]);
    }

    private function orderDetailsResponse(Order $order): Response
    {
        return response()->json(['order' => $this->orderPayload($order)]);
    }

    private function orderPayload(Order $order): array
    {
        return [
            ...$order->only([
                'id',
                'order_number',
                'customer_name',
                'price',
                'description',
                'notes',
            ]),
            'items' => $order->items->map(fn (OrderItem $item) => $item->only([
                'id',
                'item_number',
                'name',
                'quantity',
                'price',
            ]))->values()->all(),
        ];
    }

    private function generateOrderNumber(): string
    {
        $lastNumber = Order::query()
            ->pluck('order_number')
            ->map(fn (string $orderNumber): int => (int) str_replace('ORD-', '', $orderNumber))
            ->max();

        return 'ORD-'.($lastNumber + 1);
    }
}
