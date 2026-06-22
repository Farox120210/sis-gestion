<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('investigador', function (Blueprint $table) {
            $table->string('cod_orcid', 20)->nullable();
            $table->string('grado_academico', 100)->nullable();
            $table->string('institucion', 150)->nullable();
        });
    }

    public function down()
    {
        Schema::table('investigador', function (Blueprint $table) {
            $table->dropColumn(['cod_orcid', 'grado_academico', 'institucion']);
        });
    }
};

