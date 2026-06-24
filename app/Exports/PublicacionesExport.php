<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PublicacionesExport implements FromCollection, WithHeadings, WithDrawings, WithCustomStartCell, WithEvents
{
    protected $tipo;
    protected $q;

    public function __construct($tipo = null, $q = null)
    {
        $this->tipo = $tipo;
        $this->q    = $q;
    }

    /**
     * Obtiene la colección de datos directamente de la base de datos
     */
    public function collection()
    {
        $query = DB::table('proyecto_revista as pr')
            ->join('proyecto as p', 'p.ID_PROYECTO', '=', 'pr.ID_PROYECTO')
            ->join('revistas as r', 'r.ID_REVISTA', '=', 'pr.ID_REVISTA')
            ->leftJoin('area_investigacion as a', 'a.ID_AREA', '=', 'p.ID_AREA')
            ->leftJoin('grupos_investigacion as g', 'g.ID_GRUPO', '=', 'p.ID_GRUPO')
            ->leftJoin('proyecto_investigador as pi', 'pi.ID_PROYECTO', '=', 'p.ID_PROYECTO')
            ->leftJoin('investigador as i', 'i.ID_INVESTIGADOR', '=', 'pi.ID_INVESTIGADOR')
            ->leftJoin('persona as per', 'per.ID_PERSONA', '=', 'i.ID_PERSONA')
            ->leftJoin('tipos_proyecto as tp', 'tp.ID_TIPO', '=', 'p.ID_TIPO');

        // Aplicar filtro por tipo de investigación
        if (!empty($this->tipo)) {
            $query->where('tp.NOMBRE_TIPO', $this->tipo);
        }

        // Aplicar filtro de búsqueda de texto general
        if (!empty($this->q)) {
            $search = $this->q;
            $query->where(function ($sub) use ($search) {
                $sub->where(DB::raw("CONCAT(per.NOMBRES, ' ', per.APELLIDOS)"), 'LIKE', '%' . $search . '%')
                    ->orWhere('g.NOMBRE_GRUPO', 'LIKE', '%' . $search . '%')
                    ->orWhere('a.NOMBRE_AREA', 'LIKE', '%' . $search . '%')
                    ->orWhere('p.TITULO', 'LIKE', '%' . $search . '%')
                    ->orWhere('r.NOMBRE_REVISTA', 'LIKE', '%' . $search . '%');
            });
        }

        return $query
            ->selectRaw('
                p.TITULO as titulo,
                COALESCE(GROUP_CONCAT(DISTINCT CONCAT(per.NOMBRES, " ", per.APELLIDOS) SEPARATOR "; "), "—") as autores,
                r.NOMBRE_REVISTA as revista,
                pr.ANIO_PUBLICACION as anio_publicacion,
                COALESCE(a.NOMBRE_AREA, "—") as area,
                COALESCE(g.NOMBRE_GRUPO, "—") as grupo,
                COALESCE(tp.NOMBRE_TIPO, "Sin tipo") as tipo,
                p.KEYWORDS as keywords
            ')
            ->groupBy(
                'pr.ID_PROYECTO_REVISTA',
                'p.TITULO',
                'g.NOMBRE_GRUPO',
                'a.NOMBRE_AREA',
                'r.NOMBRE_REVISTA',
                'pr.ANIO_PUBLICACION',
                'tp.NOMBRE_TIPO',
                'p.KEYWORDS'
            )
            ->orderByDesc('pr.ID_PROYECTO_REVISTA')
            ->get();
    }

    /**
     * Encabezados de las columnas de la tabla de datos
     */
    public function headings(): array
    {
        return [
            'Título de la Publicación',
            'Autores',
            'Revista',
            'Año',
            'Área de Investigación',
            'Grupo de Investigación',
            'Tipo de Investigación',
            'Palabras Clave',
        ];
    }

    /**
     * Celda de inicio personalizada para dejar espacio al encabezado institucional
     */
    public function startCell(): string
    {
        return 'A7';
    }

    /**
     * Incrusta el logotipo de la UNASAM en el reporte Excel
     */
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo UNASAM');
        $drawing->setDescription('Logo institucional de la UNASAM');
        
        $logoPath = public_path('dist/img/LOGO_UNASAM-removebg-preview.png');
        if (file_exists($logoPath)) {
            $drawing->setPath($logoPath);
        }
        
        $drawing->setHeight(80);
        $drawing->setCoordinates('B2'); // Celda B2 para posicionar el logo en el área superior izquierda
        return $drawing;
    }

    /**
     * Registro de eventos para aplicar estilos detallados al libro Excel
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Unir celdas para los títulos institucionales en la parte superior
                $sheet->mergeCells('C2:G2');
                $sheet->mergeCells('C3:G3');
                $sheet->mergeCells('C4:G4');
                $sheet->mergeCells('C5:G5');
                
                // Establecer valores del encabezado
                $sheet->setCellValue('C2', 'UNIVERSIDAD NACIONAL SANTIAGO ANTÚNEZ DE MAYOLO');
                $sheet->setCellValue('C3', 'VICERRECTORADO DE INVESTIGACIÓN');
                $sheet->setCellValue('C4', 'SISTEMA DE GESTIÓN DE INVESTIGACIONES');
                $sheet->setCellValue('C5', 'REPORTE INSTITUCIONAL DE PUBLICACIONES CIENTÍFICAS');
                
                // Estilo para los títulos (Tipografías, colores institucionales y tamaños)
                $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF071444')); // Azul marino UNASAM
                $sheet->getStyle('C3')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF2A7BCA')); // Azul secundario
                $sheet->getStyle('C4')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF555555')); // Gris oscuro
                $sheet->getStyle('C5')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFC69214')); // Dorado UNASAM
                
                // Dimensionar las alturas de las filas de cabecera
                $sheet->getRowDimension(1)->setRowHeight(15);
                $sheet->getRowDimension(2)->setRowHeight(25);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(18);
                $sheet->getRowDimension(5)->setRowHeight(18);
                $sheet->getRowDimension(6)->setRowHeight(20);
                $sheet->getRowDimension(7)->setRowHeight(28); // Fila de cabecera de la tabla de datos
                
                // Obtener el rango de cabecera de la tabla (Fila 7)
                $highestColumn = $sheet->getHighestColumn();
                $tableHeaderRange = 'A7:' . $highestColumn . '7';
                
                // Aplicar estilo de fondo azul marino y texto blanco en la fila 7
                $sheet->getStyle($tableHeaderRange)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF071444');
                
                $sheet->getStyle($tableHeaderRange)->getFont()
                    ->setBold(true)
                    ->setSize(11)
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                
                $sheet->getStyle($tableHeaderRange)->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                
                // Estilo general de los datos de la tabla (Bordes y alineación)
                $highestRow = $sheet->getHighestRow();
                $tableDataRange = 'A7:' . $highestColumn . $highestRow;
                
                // Añadir bordes grises finos a toda la tabla de datos
                $sheet->getStyle($tableDataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->getColor()->setARGB('FFCCCCCC');
                
                // Alineaciones de columnas específicas
                $sheet->getStyle('D8:D' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); // Año centrado
                $sheet->getStyle('G8:G' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); // Tipo centrado
                
                // Ajustar automáticamente el ancho de todas las columnas según el contenido
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
