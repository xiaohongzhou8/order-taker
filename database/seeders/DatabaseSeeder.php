<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $menuItems = $this->loadMenuItems();

        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ],
        );

        foreach ($menuItems as $menuItem) {
            MenuItem::query()->updateOrCreate(
                ['item_number' => $menuItem['item_number']],
                $menuItem,
            );
        }

        MenuItem::query()
            ->whereNotIn('item_number', array_column($menuItems, 'item_number'))
            ->delete();
    }

    /**
     * @return list<array{item_number: string, name: string, cn_name: string, price: int|float}>
     */
    private function loadMenuItems(): array
    {
        $contents = file_get_contents(database_path('seeders/menu-items.json'));

        if ($contents === false) {
            throw new RuntimeException('Unable to read the local menu items file.');
        }

        $menuItems = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($menuItems) || ! array_is_list($menuItems) || $menuItems === []) {
            throw new RuntimeException('The local menu items file must contain a non-empty JSON list.');
        }

        $itemNumbers = [];

        foreach ($menuItems as $menuItem) {
            if (
                ! is_array($menuItem)
                || ! is_string($menuItem['item_number'] ?? null)
                || trim($menuItem['item_number']) === ''
                || ! is_string($menuItem['name'] ?? null)
                || trim($menuItem['name']) === ''
                || ! is_string($menuItem['cn_name'] ?? null)
                || trim($menuItem['cn_name']) === ''
                || (! is_int($menuItem['price'] ?? null) && ! is_float($menuItem['price'] ?? null))
            ) {
                throw new RuntimeException('The local menu items file contains an invalid menu item.');
            }

            if (isset($itemNumbers[$menuItem['item_number']])) {
                throw new RuntimeException('The local menu items file contains a duplicate item number.');
            }

            $itemNumbers[$menuItem['item_number']] = true;
        }

        return $menuItems;
    }
}
