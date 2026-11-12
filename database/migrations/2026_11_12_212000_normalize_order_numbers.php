<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $orders = DB::table('orders')->orderBy('id')->get(['id']);

        foreach ($orders as $order) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update(['order_number' => 'LEGACY-'.$order->id]);
        }

        foreach ($orders as $index => $order) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update(['order_number' => 'ORD-'.($index + 1)]);
        }
    }

    public function down(): void
    {
        // Order numbers cannot be restored because their previous values are not retained.
    }
};
