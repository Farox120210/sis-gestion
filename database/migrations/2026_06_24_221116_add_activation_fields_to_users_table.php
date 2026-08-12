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
        Schema::table('users', function (Blueprint $table) {
            // Hacemos que la contraseña sea nullable
            $table->string('password')->nullable()->change();
            
            // Agregamos los campos para el flujo de activación
            $table->string('status')->default('inactive')->after('email');
            $table->string('activation_token')->nullable()->unique()->after('status');
            $table->timestamp('activation_token_expires_at')->nullable()->after('activation_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertimos la contraseña a no nullable (requiere un valor)
            $table->string('password')->nullable(false)->change();
            
            // Eliminamos los campos agregados
            $table->dropColumn(['status', 'activation_token', 'activation_token_expires_at']);
        });
    }
};
