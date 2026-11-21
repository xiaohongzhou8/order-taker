import re

file_path = 'app/Http/Controllers/OrderController.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# The print method starts at line 116 and ends at 191.
# But let's use regex to find it to be safer.

pattern = r'public function print\(Request \$request, int \$order\): Response\n    \{.*?\}\s*$'

# Wait, the method might not be at the end of the file. 
# Let's use a more specific pattern.

pattern = r'(public function print\(Request \$request, int \$order\): Response\s*\{)(.*?)(\s*\})'

# Since the file is not huge, we can use DOTALL.
# But we want to match the print method specifically.

# Let's just use the exact text of the method I want to replace.
# I'll get the current content of the method.

new_print_method = """    public function print(Request $request, int $order): Response
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

        $text = fn (string $value): string => preg_replace('/[\\x00-\\x1F\\x7F]/', ' ', $value);
        $money = fn ($value): string => 'EUR '.number_format((float) $value, 2, '.', '');
        $queue = $isKitchenPrint ? config('services.printer.kitchen_queue') : config('services.printer.queue');
        $width = 42;
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
        $fullCut = "\\x1dV\\x00";

        try {
            $result = Process::timeout(15)
                ->input(implode("\\n", $lines)."\\n\\n\\n\\n\\n\\n".$fullCut)
                ->run(['lp', '-d', $queue, '-o', 'raw']);

            if ($result->failed()) {
                report(new \\RuntimeException('Raw printer submission failed: '.$result->errorOutput()));

                return response()->json(['message' => 'Unable to send the receipt to the printer.'], 502);
            }
        } catch (\\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Unable to send the receipt to the printer.'], 502);
        }

        return response()->noContent();
    }"""

# I'll use the current content of the print method to replace it.
# I'll just read the file and replace the lines from 116 to 191.

with open(file_path, 'r', encoding='utf-8') as f:
    lines = f.readlines()

# The print method is from 116 to 191. 
# In 0-indexed, it's 115 to 190.

# Let's verify the lines.
print(f"Line 116: {lines[115].strip()}")
print(f"Line 191: {lines[190].strip()}")

# If it matches, replace.
if lines[115].strip().startswith('public function print') and lines[190].strip() == '}':
    new_lines = lines[:115] + new_print_method.split('\n') + lines[191:]
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(new_lines) + '\n')
    print("Success!")
else:
    print("Failure! The lines don't match the expected print method.")
