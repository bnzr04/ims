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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            /* Users: 0=>User, 1=>Admin, 2=>Manager */
            $table->tinyInteger('type');
            /* if User = Manager(2) */
            $table->tinyInteger('dept')->nullable();
            $table->rememberToken();
            $table->timestamp('created_at')->useCurrent()->format('h:i:s A');
            $table->timestamp('updated_at')->useCurrent()->format('h:i:s A');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
