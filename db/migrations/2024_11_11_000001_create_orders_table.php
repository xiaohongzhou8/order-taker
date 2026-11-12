<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique(); // e.g., ORD-2024-001
            $table->string('customer_name');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered'])->default('pending');
            $table->json('notes')->nullable(); // Order number, name, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
