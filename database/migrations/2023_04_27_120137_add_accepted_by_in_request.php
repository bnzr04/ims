<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request', function (Blueprint $table) {
            $table->integer('accepted_by_user_id')->nullable()->after('status');
            $table->string('accepted_by_user_name')->nullable()->after('accepted_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request', function (Blueprint $table) {
            $table->dropColumn('accepted_by_user_id');
            $table->dropColumn('accepted_by_user_name');
        });
    }
};
