<?php
// app/Models/Proyecto.php
namespace App\Models;
use App\Models\TipoProyecto;
use App\Models\EstadoProyecto;
use App\Models\AreaInvestigacion;
use App\Models\Investigador;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $table = 'proyecto';            // tu tabla real
    protected $primaryKey = 'ID_PROYECTO';
    public $timestamps = true;                // tienes created_at / updated_at

    protected $fillable = [
        'TITULO','DESCRIPCION_PROYECTO','ANIO','FECHA_INICIO','FECHA_FIN','PRESUPUESTO',
        'ID_AUTOR','ID_AREA','ID_ESTADO','ID_GRUPO','ID_TIPO',
    ];

    public function tipoProyecto(){ return $this->belongsTo(TipoProyecto::class,'ID_TIPO','ID_TIPO'); }
    public function estado(){ return $this->belongsTo(EstadoProyecto::class,'ID_ESTADO','ID_ESTADO'); }
    public function area(){ return $this->belongsTo(AreaInvestigacion::class,'ID_AREA','ID_AREA'); }
    public function autor(){ return $this->belongsTo(Investigador::class,'ID_AUTOR','ID_INVESTIGADOR'); }
}


