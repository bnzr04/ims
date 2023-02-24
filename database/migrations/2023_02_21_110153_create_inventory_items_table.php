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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('category');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2);
            $table->integer('useful_life')->nullable();
            $table->string('depreciation_method')->nullable();
            $table->decimal('depreciation_rate', 5, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->decimal('depreciation_expense', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
};
