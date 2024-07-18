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
        Schema::create('persoon_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('personenID');
            $table->unsignedBigInteger('documentenID');
            $table->timestamps();
            $table->primary(['personenID', 'documentenID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persoon_documents');
    }
};
