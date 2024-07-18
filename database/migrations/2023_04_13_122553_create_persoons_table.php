<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('persoons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('familie1ID');
            $table->unsignedBigInteger('familie2ID');
            $table->unsignedBigInteger('ouder1ID')->default(null)->nullable();
            $table->unsignedBigInteger('ouder2ID')->default(null)->nullable();
            $table->string('voornamen')->default(null)->nullable();
            $table->string('roepnaam')->default(null)->nullable();
            $table->string('naam')->default(null)->nullable();
            $table->enum('sex', ['M','V'])->default('M');
            $table->date('geborenop')->default(null)->nullable();
            $table->unsignedBigInteger('geborenplaatsID')->default(null)->nullable();
            $table->date('gestorvenop')->default(null)->nullable();
            $table->unsignedBigInteger('gestorvenplaatsID')->default(null)->nullable();
            $table->text('info')->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persoons');
    }
};
