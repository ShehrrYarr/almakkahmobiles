<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotebooksTable extends Migration
{
    /**
     * A single shared spreadsheet-style notebook. Only one row ever exists;
     * the whole grid is stored as a JSON array of rows (each an array of cell values).
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('notebooks')) {
            return;
        }

        Schema::create('notebooks', function (Blueprint $table) {
            $table->id();
            $table->json('data')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notebooks');
    }
}
