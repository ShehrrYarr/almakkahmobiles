<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileSalesTable extends Migration
{
    /**
     * No status/approved_at/approved_by — mobile sales finalize immediately,
     * no pending->approved review step. client_ref is the offline-sync
     * idempotency key from day one (see accessory sales' client_ref fix).
     */
    public function up()
    {
        if (Schema::hasTable('mobile_sales')) {
            return;
        }

        Schema::create('mobile_sales', function (Blueprint $table) {
            $table->id();
            $table->string('client_ref', 64)->nullable()->unique();
            $table->unsignedBigInteger('mobile_vendor_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_mobile')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('pay_amount', 12, 2)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('sale_date');
            $table->timestamps();

            $table->foreign('mobile_vendor_id')->references('id')->on('mobile_vendors')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_sales');
    }
}
