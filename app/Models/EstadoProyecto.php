<?php
// app/Models/EstadoProyecto.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoProyecto extends Model
{
    protected $table = 'estado_proyecto';
    protected $primaryKey = 'ID_ESTADO';
    public $timestamps = false;
    protected $fillable = ['NOMBRE_ESTADO','DESCRIPCION'];
}


