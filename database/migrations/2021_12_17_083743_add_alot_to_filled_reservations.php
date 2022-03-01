<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlotToFilledReservations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('filled_reservations', function (Blueprint $table) {
            $table->string('checkout_date')->nullable();
            $table->string('confirmed_date')->nullable();
            $table->integer('confirmed_by')->nullable();
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
