<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    /**
     * A shop is a separately-branded Mobile-section location with its own
     * login URL (/shop/{slug}/login). The Accessory section is unrelated
     * to this table entirely — it stays the single, un-scoped setup it's
     * always been.
     */
    public function up()
    {
        if (Schema::hasTable('shops')) {
            return;
        }

        Schema::create('shops', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // this server's default_storage_engine is MyISAM, which can't be a foreign key target
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shops');
    }
}
