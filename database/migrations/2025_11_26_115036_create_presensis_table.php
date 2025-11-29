<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresensisTable extends Migration
{
    public function up()
{
    Schema::create('presensis', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->boolean('status')->default(false);
        $table->dateTime('tanggal');
        $table->timestamps();
    });
}

    public function down()
    {
        Schema::dropIfExists('presensis');
    }
};