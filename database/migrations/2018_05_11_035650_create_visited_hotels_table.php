<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitedHotelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visited_hotels', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('hotel_id')->unsigned();
            $table->double('rating', 8, 2);
            $table->timestamps();

            $table->primary(['user_id', 'hotel_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('hotel_id')->references('id')->on('hotels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visited_hotels');
    }
}
