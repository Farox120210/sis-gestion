<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Activamos todos los usuarios existentes que ya tienen contraseña establecida
        User::whereNotNull('password')
            ->where('password', '!=', '')
            ->update(['status' => 'active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir esto de forma destructiva, ya que los usuarios activos deben permanecer activos.
    }
};
