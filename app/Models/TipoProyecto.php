<?php
// app/Models/TipoProyecto.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProyecto extends Model
{
    protected $table = 'tipos_proyecto';
    protected $primaryKey = 'ID_TIPO';
    public $timestamps = false;

    protected $fillable = ['NOMBRE_TIPO','DESCRIPCION'];
}