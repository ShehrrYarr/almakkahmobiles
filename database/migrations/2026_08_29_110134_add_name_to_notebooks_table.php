<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNameToNotebooksTable extends Migration
{
    /**
     * Adds a name so multiple notebooks can coexist. The existing row (the
     * one live in production) is backfilled as "Notebook 1" — its `data`
     * column, holding real user content, is never touched.
     */
    public function up()
    {
        if (Schema::hasColumn('notebooks', 'name')) {
            return;
        }

        Schema::table('notebooks', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::table('notebooks')->orderBy('id')->get(['id'])->each(function ($row, $i) {
            DB::table('notebooks')->where('id', $row->id)->update([
                'name' => 'Notebook ' . ($i + 1),
            ]);
        });
    }

    public function down()
    {
        Schema::table('notebooks', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
