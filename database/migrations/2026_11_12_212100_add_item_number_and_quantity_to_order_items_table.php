<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('item_number')->default(1)->after('order_id');
            $table->unsignedInteger('quantity')->default(1)->after('name');
        });

        $orderIds = DB::table('order_items')->distinct()->pluck('order_id');
        foreach ($orderIds as $orderId) {
            $items = DB::table('order_items')->where('order_id', $orderId)->orderBy('id')->get(['id']);
            foreach ($items as $index => $item) {
                DB::table('order_items')->where('id', $item->id)->update([
                    'item_number' => $index + 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['item_number', 'quantity']);
        });
    }
};
