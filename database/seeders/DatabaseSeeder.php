<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ],
        );

        $menuItems = [
            ['item_number' => 1, 'name' => 'Classic Cheeseburger', 'price' => 12.50],
            ['item_number' => 2, 'name' => 'Bacon Cheeseburger', 'price' => 14.50],
            ['item_number' => 3, 'name' => 'Grilled Chicken Sandwich', 'price' => 13.00],
            ['item_number' => 4, 'name' => 'Crispy Chicken Burger', 'price' => 12.75],
            ['item_number' => 5, 'name' => 'Margherita Pizza', 'price' => 11.50],
            ['item_number' => 6, 'name' => 'Pepperoni Pizza', 'price' => 13.50],
            ['item_number' => 7, 'name' => 'Chicken Caesar Salad', 'price' => 10.50],
            ['item_number' => 8, 'name' => 'Loaded Fries', 'price' => 7.50],
            ['item_number' => 9, 'name' => 'Onion Rings', 'price' => 5.50],
            ['item_number' => 10, 'name' => 'Tomato Soup', 'price' => 6.00],
            ['item_number' => 11, 'name' => 'Fish and Chips', 'price' => 15.00],
            ['item_number' => 12, 'name' => 'Chicken Tenders', 'price' => 10.00],
            ['item_number' => 13, 'name' => 'Beef Tacos', 'price' => 11.00],
            ['item_number' => 14, 'name' => 'Vegetable Wrap', 'price' => 9.50],
            ['item_number' => 15, 'name' => 'Macaroni and Cheese', 'price' => 8.50],
            ['item_number' => 16, 'name' => 'Garlic Bread', 'price' => 4.50],
            ['item_number' => 17, 'name' => 'Chocolate Cake', 'price' => 6.50],
            ['item_number' => 18, 'name' => 'Apple Pie', 'price' => 5.50],
            ['item_number' => 19, 'name' => 'Soft Drink', 'price' => 2.50],
            ['item_number' => 20, 'name' => 'Bottled Water', 'price' => 2.00],
        ];

        foreach ($menuItems as $menuItem) {
            MenuItem::query()->updateOrCreate(
                ['item_number' => $menuItem['item_number']],
                $menuItem,
            );
        }

        $orders = [
            [
                'order_number' => 'ORD-1',
                'customer_name' => 'Ava Thompson',
                'price' => 129.95,
                'description' => 'Wireless headphones and protective case',
                'items' => [
                    ['item_number' => 1, 'name' => 'Wireless headphones', 'quantity' => 1, 'price' => 99.95],
                    ['item_number' => 2, 'name' => 'Protective case', 'quantity' => 1, 'price' => 30.00],
                ],
            ],
            [
                'order_number' => 'ORD-2',
                'customer_name' => 'Noah Williams',
                'price' => 74.50,
                'description' => 'Coffee maker replacement filters',
                'items' => [
                    ['item_number' => 1, 'name' => 'Paper coffee filters', 'quantity' => 2, 'price' => 12.25],
                    ['item_number' => 2, 'name' => 'Reusable coffee filter', 'quantity' => 1, 'price' => 50.00],
                ],
            ],
            [
                'order_number' => 'ORD-3',
                'customer_name' => 'Mia Garcia',
                'price' => 249.00,
                'description' => 'Desk lamp and ergonomic keyboard',
                'items' => [
                    ['item_number' => 1, 'name' => 'Adjustable desk lamp', 'quantity' => 1, 'price' => 89.00],
                    ['item_number' => 2, 'name' => 'Ergonomic keyboard', 'quantity' => 1, 'price' => 160.00],
                ],
            ],
            [
                'order_number' => 'ORD-4',
                'customer_name' => 'Liam Anderson',
                'price' => 32.50,
                'description' => 'Two burgers with a side and drinks',
                'items' => [
                    ['item_number' => 2, 'name' => 'Bacon Cheeseburger', 'quantity' => 1, 'price' => 14.50],
                    ['item_number' => 8, 'name' => 'Loaded Fries', 'quantity' => 1, 'price' => 7.50],
                    ['item_number' => 19, 'name' => 'Soft Drink', 'quantity' => 2, 'price' => 2.50],
                    ['item_number' => 9, 'name' => 'Onion Rings', 'quantity' => 1, 'price' => 5.50],
                ],
            ],
            [
                'order_number' => 'ORD-5',
                'customer_name' => 'Sophia Martinez',
                'price' => 29.50,
                'description' => 'Pizza meal for one',
                'items' => [
                    ['item_number' => 6, 'name' => 'Pepperoni Pizza', 'quantity' => 1, 'price' => 13.50],
                    ['item_number' => 16, 'name' => 'Garlic Bread', 'quantity' => 1, 'price' => 4.50],
                    ['item_number' => 17, 'name' => 'Chocolate Cake', 'quantity' => 1, 'price' => 6.50],
                    ['item_number' => 19, 'name' => 'Soft Drink', 'quantity' => 2, 'price' => 2.50],
                ],
            ],
            [
                'order_number' => 'ORD-6',
                'customer_name' => 'Oliver Brown',
                'price' => 33.00,
                'description' => 'Fish and chips with dessert',
                'items' => [
                    ['item_number' => 11, 'name' => 'Fish and Chips', 'quantity' => 1, 'price' => 15.00],
                    ['item_number' => 9, 'name' => 'Onion Rings', 'quantity' => 1, 'price' => 5.50],
                    ['item_number' => 18, 'name' => 'Apple Pie', 'quantity' => 1, 'price' => 5.50],
                    ['item_number' => 19, 'name' => 'Soft Drink', 'quantity' => 2, 'price' => 2.50],
                    ['item_number' => 20, 'name' => 'Bottled Water', 'quantity' => 1, 'price' => 2.00],
                ],
            ],
            [
                'order_number' => 'ORD-7',
                'customer_name' => 'Emma Wilson',
                'price' => 47.00,
                'description' => 'Family taco and chicken meal',
                'items' => [
                    ['item_number' => 13, 'name' => 'Beef Tacos', 'quantity' => 2, 'price' => 11.00],
                    ['item_number' => 12, 'name' => 'Chicken Tenders', 'quantity' => 1, 'price' => 10.00],
                    ['item_number' => 8, 'name' => 'Loaded Fries', 'quantity' => 1, 'price' => 7.50],
                    ['item_number' => 19, 'name' => 'Soft Drink', 'quantity' => 3, 'price' => 2.50],
                ],
            ],
            [
                'order_number' => 'ORD-8',
                'customer_name' => 'James Taylor',
                'price' => 27.50,
                'description' => 'Light lunch with a sweet finish',
                'items' => [
                    ['item_number' => 7, 'name' => 'Chicken Caesar Salad', 'quantity' => 1, 'price' => 10.50],
                    ['item_number' => 14, 'name' => 'Vegetable Wrap', 'quantity' => 1, 'price' => 9.50],
                    ['item_number' => 18, 'name' => 'Apple Pie', 'quantity' => 1, 'price' => 5.50],
                    ['item_number' => 20, 'name' => 'Bottled Water', 'quantity' => 1, 'price' => 2.00],
                ],
            ],
            [
                'order_number' => 'ORD-9',
                'customer_name' => 'Isabella Thomas',
                'price' => 36.25,
                'description' => 'Comfort food dinner',
                'items' => [
                    ['item_number' => 15, 'name' => 'Macaroni and Cheese', 'quantity' => 2, 'price' => 8.50],
                    ['item_number' => 4, 'name' => 'Crispy Chicken Burger', 'quantity' => 1, 'price' => 12.75],
                    ['item_number' => 20, 'name' => 'Bottled Water', 'quantity' => 1, 'price' => 2.00],
                    ['item_number' => 16, 'name' => 'Garlic Bread', 'quantity' => 1, 'price' => 4.50],
                ],
            ],
        ];

        $demoCustomers = collect($orders)->pluck('customer_name');
        $user->orders()
            ->whereIn('customer_name', $demoCustomers)
            ->get()
            ->each(fn ($order) => $order->update(['order_number' => 'SEED-'.$order->id]));

        foreach ($orders as $order) {
            $items = $order['items'];
            unset($order['items']);
            $savedOrder = $user->orders()->where('customer_name', $order['customer_name'])->first();

            if ($savedOrder) {
                $savedOrder->update($order);
            } else {
                $savedOrder = $user->orders()->create($order);
            }

            /*
             * Demo items are intentionally reset so the seeder remains repeatable.
             */
            $savedOrder->items()->delete();
            $savedOrder->items()->createMany($items);
        }
    }
}
