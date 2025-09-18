<?php
// database/migrations/xxxx_add_pdf_columns_to_proyecto_revista.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('proyecto_revista', function (Blueprint $t) {
      $t->string('PDF_PATH')->nullable();         // si guardas local (/storage/...)
      $t->string('PDF_URL', 500)->nullable();     // si usas OneDrive (webUrl/share)
      $t->string('DOI')->nullable();              // opcional: para enlazar a doi.org
      // si usarás el agente:
      $t->enum('METADATA_STATUS', ['pending','ok','warn','error'])->default('pending');
      $t->tinyInteger('METADATA_CONFIDENCE')->nullable();
      $t->json('RAW_METADATA')->nullable();
    });
  }
  public function down(): void {
    Schema::table('proyecto_revista', function (Blueprint $t) {
      $t->dropColumn(['PDF_PATH','PDF_URL','DOI','METADATA_STATUS','METADATA_CONFIDENCE','RAW_METADATA']);
    });
  }
};
