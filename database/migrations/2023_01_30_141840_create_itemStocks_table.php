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
        Schema::create('item_stocks', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('item_id');
            $table->integer('stock_qty');
            $table->date('exp_date');
            $table->string('mode_acquisition');
            $table->timestamp('created_at')->useCurrent()->format('h:i:s A');
            $table->timestamp('updated_at')->useCurrent()->format('h:i:s A');

            //Foreign key itemId from item table
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
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
        Schema::dropIfExists('item_stocks');
    }
};
