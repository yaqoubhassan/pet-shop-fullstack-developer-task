<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_status_id');
            $table->unsignedBigInteger('payment_id');
            $table->uuid('uuid')->unique();
            $table->json('products')->comment('This field contains an array of products UUIDs');
            $table->json('address')->comment(
                'This field contains an object including the billing and shipping address'
            );
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->timestamp('shipped_at')->nullable();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_status_id')->references('id')->on('order_statuses')->onDelete('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
