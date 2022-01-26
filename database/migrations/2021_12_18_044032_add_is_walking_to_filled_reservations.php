<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsWalkingToFilledReservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('filled_reservations', function (Blueprint $table) {
            $table->bigInteger('male');
            $table->bigInteger('female');
            $table->bigInteger('kids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('filled_reservations', function (Blueprint $table) {
            //
        });
    }
}
