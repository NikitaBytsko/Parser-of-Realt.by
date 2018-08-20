<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficeObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('office_objects', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('code');
            $table->integer('price');
            $table->json('contact');
            $table->json('location');
            $table->json('options');
            $table->json('conditions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('office_objects');
    }
}
