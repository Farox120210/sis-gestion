<?php
// app/Models/Investigador.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investigador extends Model
{
    protected $table = 'investigador';
    protected $primaryKey = 'ID_INVESTIGADOR';
    public $timestamps = false;
    protected $fillable = ['ID_PERSONA','TIPO_INVESTIGADOR','CORREO_INSTITUCIONAL'];
}
