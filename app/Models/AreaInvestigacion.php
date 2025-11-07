<?php
// app/Models/AreaInvestigacion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaInvestigacion extends Model
{
    protected $table = 'area_investigacion';
    protected $primaryKey = 'ID_AREA';
    public $timestamps = false;
    protected $fillable = ['NOMBRE_AREA','DESCRIPCION'];
}
