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
        Schema::create('request', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('office');
            $table->string('request_by');
            $table->string('request_to');
            $table->string('status')->default('not completed');
            $table->timestamp('created_at')->useCurrent()->format('h:i:s A');
            $table->timestamp('updated_at')->useCurrent()->format('h:i:s A');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::create('request_items', function (Blueprint $table) {
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('stock_id');
            $table->integer('quantity');
            $table->string('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent()->format('h:i:s A');
            $table->timestamp('updated_at')->useCurrent()->format('h:i:s A');

            $table->foreign('request_id')
                ->references('id')
                ->on('request')
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->onDelete('cascade');

            $table->foreign('stock_id')
                ->references('id')
                ->on('item_stocks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request');
    }
};
