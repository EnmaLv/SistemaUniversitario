<?php

namespace App\Exports\Salud;

use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Genera el Excel de un listado de Salud a partir de la definición
 * que entrega ListadoSaludService (la misma que usa el PDF).
 */
class SaludListadoExcelExport
{
    private const ACENTO = '1F4E3D';
    private const GRIS = '6B7280';
    private const BORDE = 'E5E7EB';

    /**
     * @param array $reporte Definición del listado (columnas, filas, resumen, anexo)
     * @param array $meta    periodo, generado y filtros (pares [etiqueta, valor])
     * @return string Ruta del archivo temporal .xlsx
     */
    public static function generate(array $reporte, array $meta): string
    {
        $libro = new Spreadsheet();
        $libro->getProperties()
            ->setTitle($reporte['titulo'])
            ->setSubject('Módulo de Salud')
            ->setCreator('Módulo de Salud');
        $libro->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $resumen = implode('   |   ', array_map(fn($par) => "{$par[0]}: {$par[1]}", $reporte['resumen']));

        $hoja = $libro->getActiveSheet();

        $ultimaFila = self::escribirHoja(
            $hoja,
            $reporte['hoja'],
            $reporte['titulo'],
            self::lineasEncabezado($meta, $resumen),
            $reporte['columnas'],
            $reporte['filas'],
            $reporte['vacio']
        );

        // Tabla anexa (totales por medicamento)
        if (!empty($reporte['anexo'])) {
            self::escribirAnexo($hoja, $reporte['anexo'], $reporte['columnas'], $ultimaFila + 3);
        }

        $hoja->setSelectedCell('A1');

        $ruta = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('salud_listado_', true) . '.xlsx';
        (new Xlsx($libro))->save($ruta);
        $libro->disconnectWorksheets();

        return $ruta;
    }

    /**
     * Líneas informativas que van sobre la tabla.
     */
    private static function lineasEncabezado(array $meta, ?string $resumen = null): array
    {
        $filtros = array_map(fn($par) => "{$par[0]}: {$par[1]}", $meta['filtros'] ?? []);

        return array_values(array_filter([
            'Período: ' . ($meta['periodo'] ?? '—'),
            'Filtros: ' . ($filtros ? implode('; ', $filtros) : 'ninguno'),
            $resumen,
            'Generado: ' . ($meta['generado'] ?? '—'),
        ]));
    }

    /**
     * Escribe el título, las líneas informativas y la tabla principal.
     *
     * @return int Última fila ocupada por la tabla
     */
    private static function escribirHoja(
        Worksheet $hoja,
        string $nombreHoja,
        string $titulo,
        array $lineas,
        array $columnas,
        array $filas,
        string $mensajeVacio
    ): int {
        $hoja->setTitle($nombreHoja);

        $ultimaColumna = Coordinate::stringFromColumnIndex(count($columnas));

        // ── Título y líneas informativas ──
        $hoja->setCellValue('A1', $titulo);
        $hoja->mergeCells("A1:{$ultimaColumna}1");
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB(self::ACENTO);
        $hoja->getRowDimension(1)->setRowHeight(24);

        $fila = 2;
        foreach ($lineas as $linea) {
            $hoja->setCellValueExplicit("A{$fila}", $linea, DataType::TYPE_STRING);
            $hoja->mergeCells("A{$fila}:{$ultimaColumna}{$fila}");
            $hoja->getStyle("A{$fila}")->getFont()->getColor()->setRGB(self::GRIS);
            $fila++;
        }

        // ── Encabezados de la tabla ──
        $filaEncabezado = $fila + 1;

        foreach ($columnas as $i => $columna) {
            $letra = Coordinate::stringFromColumnIndex($i + 1);
            $hoja->setCellValueExplicit("{$letra}{$filaEncabezado}", $columna['titulo'], DataType::TYPE_STRING);
            $hoja->getColumnDimension($letra)->setWidth($columna['excel']);
        }

        $hoja->getStyle("A{$filaEncabezado}:{$ultimaColumna}{$filaEncabezado}")->applyFromArray(self::estiloEncabezado());
        $hoja->getRowDimension($filaEncabezado)->setRowHeight(22);

        // ── Datos ──
        $filaActual = $filaEncabezado + 1;

        if (!$filas) {
            $hoja->setCellValue("A{$filaActual}", $mensajeVacio);
            $hoja->mergeCells("A{$filaActual}:{$ultimaColumna}{$filaActual}");
            $hoja->getStyle("A{$filaActual}")->getFont()->setItalic(true)->getColor()->setRGB(self::GRIS);
        }

        foreach ($filas as $datos) {
            foreach ($columnas as $i => $columna) {
                $celda = Coordinate::stringFromColumnIndex($i + 1) . $filaActual;
                self::escribirCelda($hoja, $celda, $datos[$columna['clave']] ?? null, $columna['tipo']);
            }
            $filaActual++;
        }

        $ultimaFila = max($filaActual - 1, $filaEncabezado + 1);

        if ($filas) {
            $rangoDatos = 'A' . ($filaEncabezado + 1) . ":{$ultimaColumna}{$ultimaFila}";
            $hoja->getStyle($rangoDatos)->applyFromArray(self::estiloDatos());

            foreach ($columnas as $i => $columna) {
                $letra = Coordinate::stringFromColumnIndex($i + 1);
                $rango = $letra . ($filaEncabezado + 1) . ':' . $letra . $ultimaFila;

                if ($columna['tipo'] === 'fecha') {
                    $hoja->getStyle($rango)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                    $hoja->getStyle($rango)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                } elseif (in_array($columna['tipo'], ['numero', 'entero'], true)) {
                    $hoja->getStyle($rango)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $hoja->getStyle($letra . $filaEncabezado)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            }

            // Filtros y encabezado fijo para trabajar la tabla
            $hoja->setAutoFilter("A{$filaEncabezado}:{$ultimaColumna}{$ultimaFila}");
        }

        $hoja->freezePane('A' . ($filaEncabezado + 1));

        // ── Impresión ──
        $hoja->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $hoja->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($filaEncabezado, $filaEncabezado);
        $hoja->getHeaderFooter()->setOddFooter('&L' . $titulo . '&RPágina &P de &N');

        return $ultimaFila;
    }

    /**
     * Escribe la tabla anexa debajo de la principal.
     *
     * Cada columna del anexo se ubica bajo la columna principal del mismo dato
     * (el total queda debajo de "Cantidad"); las que no existen arriba van a continuación.
     */
    private static function escribirAnexo(Worksheet $hoja, array $anexo, array $columnasPrincipales, int $filaTitulo): void
    {
        // ── Posición de cada columna ──
        $indicePorClave = array_flip(array_column($columnasPrincipales, 'clave'));
        $posiciones = [];
        $sinPareja = [];

        foreach ($anexo['columnas'] as $columna) {
            if (isset($indicePorClave[$columna['clave']])) {
                $posiciones[$indicePorClave[$columna['clave']] + 1] = $columna;
            } else {
                $sinPareja[] = $columna;
            }
        }

        $siguiente = $posiciones ? max(array_keys($posiciones)) + 1 : 1;
        foreach ($sinPareja as $columna) {
            $posiciones[$siguiente++] = $columna;
        }
        ksort($posiciones);

        $primera = Coordinate::stringFromColumnIndex(min(array_keys($posiciones)));
        $ultima = Coordinate::stringFromColumnIndex(max(array_keys($posiciones)));

        // ── Título ──
        $hoja->setCellValueExplicit("{$primera}{$filaTitulo}", $anexo['titulo'], DataType::TYPE_STRING);
        $hoja->getStyle("{$primera}{$filaTitulo}")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB(self::ACENTO);

        // ── Encabezados ──
        $filaEncabezado = $filaTitulo + 1;
        foreach ($posiciones as $indice => $columna) {
            $letra = Coordinate::stringFromColumnIndex($indice);
            $hoja->setCellValueExplicit("{$letra}{$filaEncabezado}", $columna['titulo'], DataType::TYPE_STRING);
        }
        $hoja->getStyle("{$primera}{$filaEncabezado}:{$ultima}{$filaEncabezado}")->applyFromArray(self::estiloEncabezado());
        $hoja->getRowDimension($filaEncabezado)->setRowHeight(30);   // "Cantidad total" ocupa dos líneas

        // ── Datos ──
        $fila = $filaEncabezado + 1;
        foreach ($anexo['filas'] as $datos) {
            foreach ($posiciones as $indice => $columna) {
                $celda = Coordinate::stringFromColumnIndex($indice) . $fila;
                self::escribirCelda($hoja, $celda, $datos[$columna['clave']] ?? null, $columna['tipo']);
            }
            $fila++;
        }
        $ultimaFila = $fila - 1;

        $hoja->getStyle("{$primera}" . ($filaEncabezado + 1) . ":{$ultima}{$ultimaFila}")->applyFromArray(self::estiloDatos());

        foreach ($posiciones as $indice => $columna) {
            if (in_array($columna['tipo'], ['numero', 'entero'], true)) {
                $letra = Coordinate::stringFromColumnIndex($indice);
                $hoja->getStyle("{$letra}{$filaEncabezado}:{$letra}{$ultimaFila}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }

        // ── Acceso directo desde la parte superior de la hoja ──
        $filaEnlace = self::filaLibreSobreLaTabla($hoja);
        if ($filaEnlace) {
            $hoja->setCellValueExplicit("A{$filaEnlace}", "{$anexo['titulo']}: ir a la fila {$filaTitulo}", DataType::TYPE_STRING);
            $hoja->getCell("A{$filaEnlace}")->getHyperlink()
                ->setUrl("sheet://'" . $hoja->getTitle() . "'!{$primera}{$filaTitulo}");
            $hoja->getStyle("A{$filaEnlace}")->getFont()->setUnderline(true)->getColor()->setRGB(self::ACENTO);
        }
    }

    /**
     * Fila en blanco que queda entre las líneas informativas y los encabezados.
     */
    private static function filaLibreSobreLaTabla(Worksheet $hoja): ?int
    {
        $panel = $hoja->getFreezePane();   // p. ej. "A8": la tabla empieza en la fila 7
        if (!$panel) {
            return null;
        }

        $fila = (int) preg_replace('/\D/', '', $panel) - 2;

        return $fila > 1 && $hoja->getCell("A{$fila}")->getValue() === null ? $fila : null;
    }

    private static function estiloEncabezado(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::ACENTO]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];
    }

    private static function estiloDatos(): array
    {
        return [
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
            'borders' => [
                'horizontal' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::BORDE]],
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::ACENTO]],
            ],
        ];
    }

    private static function escribirCelda(Worksheet $hoja, string $celda, mixed $valor, string $tipo): void
    {
        if ($valor === null || $valor === '') {
            return;
        }

        if ($tipo === 'fecha' && $valor instanceof DateTimeInterface) {
            // Fecha real de Excel (permite ordenar y filtrar por fecha)
            $hoja->setCellValue($celda, Date::PHPToExcel($valor->format('Y-m-d')));
            return;
        }

        if ($tipo === 'numero') {
            $hoja->setCellValueExplicit($celda, round((float) $valor, 2), DataType::TYPE_NUMERIC);
            return;
        }

        if ($tipo === 'entero') {
            $hoja->setCellValueExplicit($celda, (int) $valor, DataType::TYPE_NUMERIC);
            return;
        }

        // Texto explícito: evita que Excel convierta cédulas o códigos de lote en números
        $hoja->setCellValueExplicit($celda, (string) $valor, DataType::TYPE_STRING);
    }
}
