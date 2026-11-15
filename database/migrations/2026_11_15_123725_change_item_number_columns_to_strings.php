<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('item_number')->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('item_number')->default('1')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $menuItemNumbers = DB::table('menu_items')->pluck('item_number')->all();
        $orderItemNumbers = DB::table('order_items')->pluck('item_number')->all();
        $normalizedMenuItemNumbers = array_map(
            fn (string|int $itemNumber): string => $this->normalizeIntegerItemNumber($itemNumber),
            $menuItemNumbers,
        );

        array_map(
            fn (string|int $itemNumber): string => $this->normalizeIntegerItemNumber($itemNumber),
            $orderItemNumbers,
        );

        if (count($normalizedMenuItemNumbers) !== count(array_unique($normalizedMenuItemNumbers))) {
            throw new RuntimeException('Cannot revert item numbers because integer conversion would create duplicates.');
        }

        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedInteger('item_number')->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('item_number')->default(1)->change();
        });
    }

    private function normalizeIntegerItemNumber(string|int $itemNumber): string
    {
        $number = filter_var((string) $itemNumber, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 0,
                'max_range' => 4_294_967_295,
            ],
        ]);

        if ($number === false) {
            throw new RuntimeException('Cannot revert item numbers while string identifiers exist.');
        }

        return (string) $number;
    }
};
