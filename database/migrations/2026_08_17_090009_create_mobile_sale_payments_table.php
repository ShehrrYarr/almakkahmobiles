<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileSalePaymentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mobile_sale_payments')) {
            return;
        }

        Schema::create('mobile_sale_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_sale_id');
            $table->string('method'); // counter | bank
            $table->unsignedBigInteger('mobile_bank_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('reference_no')->nullable();
            $table->string('notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('mobile_sale_id')->references('id')->on('mobile_sales')->onDelete('cascade');
            $table->foreign('mobile_bank_id')->references('id')->on('mobile_banks')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mobile_sale_payments');
    }
}
