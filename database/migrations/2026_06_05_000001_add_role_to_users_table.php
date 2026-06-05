<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRoleToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'salesman'])->default('salesman')->after('is_active');
        });

        // Populate role from existing is_admin column
        DB::table('users')->where('is_admin', 1)->update(['role' => 'admin']);
        DB::table('users')->where(function ($q) {
            $q->where('is_admin', 0)->orWhereNull('is_admin');
        })->update(['role' => 'salesman']);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
}
