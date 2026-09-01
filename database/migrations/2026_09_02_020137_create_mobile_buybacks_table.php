<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobileBuybacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /**
     * A buyback re-acquires a previously-sold unit from a customer: it
     * records who sold it back and at what price/condition (audit trail),
     * while the unit row itself (mobile_units) gets updated in place —
     * new cost/selling price, condition, seller info, status back to
     * in_stock — so it reappears in Units/POS exactly like a fresh
     * purchase. The original MobileSale is left untouched.
     */
    public function up()
    {
        Schema::create('mobile_buybacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('mobile_unit_id');
            $table->unsignedBigInteger('mobile_sale_id')->nullable();
            $table->unsignedBigInteger('user_id');

            $table->string('seller_name');
            $table->string('seller_cnic')->nullable();
            $table->string('seller_phone')->nullable();
            $table->string('seller_address')->nullable();
            $table->text('seller_description')->nullable();

            $table->string('battery')->nullable();
            $table->integer('battery_cycle')->nullable();
            $table->boolean('has_box')->default(false);

            $table->decimal('buyback_price', 12, 2);
            $table->decimal('new_selling_price', 12, 2);

            $table->enum('payment_method', ['counter', 'bank']);
            $table->unsignedBigInteger('mobile_bank_id')->nullable();

            $table->timestamp('buyback_date');
            $table->timestamps();

            $table->engine = 'InnoDB'; // this server's default_storage_engine is MyISAM, which can't be a foreign key target

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('mobile_unit_id')->references('id')->on('mobile_units')->onDelete('cascade');
            $table->foreign('mobile_sale_id')->references('id')->on('mobile_sales')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mobile_bank_id')->references('id')->on('mobile_banks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobile_buybacks');
    }
}
