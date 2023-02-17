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
        Schema::create('deployed_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id'); //foreign key from items table 'id'
            $table->integer('deployed_qty');
            $table->integer('available_qty');
            $table->timestamps();


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
        Schema::dropIfExists('deployed_items');
    }
};
