<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
    Schema::create('lokasi', function (Blueprint $table) {
        $table->id();
        $table->string('nama_lokasi');
        $table->string('koordinat')->nullable();
        $table->integer('target_harian');
        $table->string('status')->default('Aktif');
        $table->timestamps();
    });
}

};
