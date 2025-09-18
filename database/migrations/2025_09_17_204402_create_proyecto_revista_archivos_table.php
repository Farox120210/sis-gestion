<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('proyecto_revista_archivos', function (Blueprint $t) {
    $t->bigIncrements('ID_ARCHIVO');
    $t->integer('ID_PROYECTO_REVISTA'); // ← en lugar de unsignedBigInteger

    $t->string('NOMBRE_ORIGINAL', 255)->nullable();
    $t->string('DISK', 50)->default('public');
    $t->string('PATH', 500)->nullable();
    $t->string('URL', 1000)->nullable();
    $t->string('MIME_TYPE', 100)->nullable();
    $t->unsignedBigInteger('SIZE_BYTES')->nullable();
    $t->string('HASH_SHA256', 64)->nullable();
    $t->string('TIPO', 30)->nullable();
    $t->unsignedInteger('VERSION')->nullable();
    $t->string('LICENCIA', 100)->nullable();
    $t->timestamp('FECHA_SUBIDA')->useCurrent();

    // índice + FK
    $t->index('ID_PROYECTO_REVISTA');
    $t->foreign('ID_PROYECTO_REVISTA')
      ->references('ID_PROYECTO_REVISTA')
      ->on('proyecto_revista')
      ->cascadeOnDelete();
});
  }

  public function down(): void {
    Schema::dropIfExists('proyecto_revista_archivos');
  }
};