<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_archivo_principal_to_proyecto_revista.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('proyecto_revista', function (Blueprint $t) {
      $t->unsignedBigInteger('ID_ARCHIVO_PRINCIPAL')->nullable()->after('ANIO_PUBLICACION');
    });

    Schema::table('proyecto_revista', function (Blueprint $t) {
      $t->foreign('ID_ARCHIVO_PRINCIPAL')
        ->references('ID_ARCHIVO')->on('proyecto_revista_archivos')
        ->nullOnDelete();
    });
  }

  public function down(): void {
    Schema::table('proyecto_revista', function (Blueprint $t) {
      $t->dropForeign(['ID_ARCHIVO_PRINCIPAL']);
      $t->dropColumn('ID_ARCHIVO_PRINCIPAL');
    });
  }
};
