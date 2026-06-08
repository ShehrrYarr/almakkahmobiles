<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeldOrdersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('held_orders')) {
            return;
        }

        Schema::create('held_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_mobile', 20)->nullable();
            $table->text('comment')->nullable();
            $table->json('cart_items');
            $table->timestamp('held_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('held_orders');
    }
}
