<?php

namespace App\Exports\Historias;

use App\Models\salud\CitaNotaEvolucion;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WordExport
{
    public static function generateExpedienteGeneral($paciente, $seccionesPersonalizadas, $enfermedadesVinculadas)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        $section = $phpWord->addSection([
            'marginTop' => 1440, 'marginBottom' => 1440, 'marginLeft' => 1440, 'marginRight' => 1440,
        ]);

        $header = $section->addHeader();
        $header->addImage(public_path('img/encabezado.png'), ['width' => 450, 'alignment' => Jc::CENTER]);
        $footer = $section->addFooter();
        $footer->addImage(public_path('img/pie.png'), ['width' => 450, 'alignment' => Jc::CENTER]);

        self::fillExpedienteGeneralWordSection($section, $paciente, $seccionesPersonalizadas, $enfermedadesVinculadas);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'Expediente_General_' . Str::slug($paciente->name) . '.docx';
        $tempFile = \App\Support\Archivos\Temporales::ruta($fileName);
        $objWriter->save($tempFile);

        return $tempFile;
    }

    public static function generateExpedienteCompleto($paciente, $historia, $seccionesPersonalizadas, $enfermedadesVinculadas, $citasSeleccionadas, $stats, $psicologoName)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        $section = $phpWord->addSection([
            'marginTop' => 1440, 'marginBottom' => 1440, 'marginLeft' => 1440, 'marginRight' => 1440,
        ]);

        $header = $section->addHeader();
        $header->addImage(public_path('img/encabezado.png'), ['width' => 450, 'alignment' => Jc::CENTER]);
        $footer = $section->addFooter();
        $footer->addImage(public_path('img/pie.png'), ['width' => 450, 'alignment' => Jc::CENTER]);

        self::fillExpedienteGeneralWordSection($section, $paciente, $seccionesPersonalizadas, $enfermedadesVinculadas);
        $section->addPageBreak();

        $headerStyle = ['bold' => true, 'size' => 18, 'color' => '111827']; 
        $subHeaderStyle = ['bold' => true, 'size' => 10, 'color' => '4B5563']; 
        $section->addText('NOTAS DE EVOLUCIÓN', $headerStyle, ['alignment' => 'center']);
        $section->addText('Psico-Guía UPTP | Dr. ' . mb_strtoupper($psicologoName) . ' | Psicólogo', $subHeaderStyle, ['alignment' => 'center']);
        $section->addText('', [], []);
        
        self::fillCronologiaSection($section, $citasSeleccionadas);

        $section->addText('_______________________________', [], ['alignment' => 'center']);
        $section->addText('FIRMA Y SELLO DEL PSICÓLOGO', ['bold' => true, 'size' => 10], ['alignment' => 'center']);
        $section->addText('Fecha de Emisión: ' . date('d/m/Y'), ['size' => 9, 'color' => '6B7280'], ['alignment' => 'center']);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = 'Expediente_Completo_' . Str::slug($paciente->name) . '.docx';
        $tempFile = \App\Support\Archivos\Temporales::ruta($fileName);
        $objWriter->save($tempFile);

        return $tempFile;
    }

    public static function generateEvolucion($citasSeleccionadas, $paciente, $historia, $stats, $psicologoName, $tempPath)
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop' => 1440,
            'marginBottom' => 1440,
            'marginLeft' => 1440,
            'marginRight' => 1440,
        ]);

        $header = $section->addHeader();
        $header->addImage(public_path('img/encabezado.png'), ['width' => 450, 'alignment' => Jc::CENTER]);
        $footer = $section->addFooter();
        $footer->addImage(public_path('img/pie.png'), ['width' => 450, 'alignment' => Jc::CENTER]);
        $headerStyle = ['bold' => true, 'size' => 18, 'color' => '111827']; 
        $subHeaderStyle = ['bold' => true, 'size' => 10, 'color' => '4B5563'];
        $section->addText('INFORME CLÍNICO DE EVOLUCIÓN', $headerStyle, ['alignment' => 'center']);
        $section->addText('Psico-Guía UPTP | Dr. ' . mb_strtoupper($psicologoName) . ' | Psicólogo', $subHeaderStyle, ['alignment' => 'center']);
        $section->addText('', [], []);
        $titleStyle = ['bold' => true, 'size' => 13, 'color' => '111827'];
        $section->addText(mb_strtoupper($paciente->name), $titleStyle);
        $section->addText('Sesiones: ' . $stats['realizadas'] . '  |  N° Expediente: ' . $historia->id, ['bold' => true, 'size' => 10, 'color' => '374151']); // Gray 700
        $section->addText('', [], []);


        $section->addText('NOTAS DE SESIÓN - CRONOLOGÍA', ['bold' => true, 'size' => 12, 'color' => '111827']); // Almost black

        self::fillCronologiaSection($section, $citasSeleccionadas);

        $section->addText('', [], []);
        $section->addText('', [], []);

        $section->addText('_______________________________', [], ['alignment' => 'center']);
        $section->addText('FIRMA Y SELLO DEL PSICÓLOGO', ['bold' => true, 'size' => 10], ['alignment' => 'center']);
        $section->addText('Fecha de Emisión: ' . date('d/m/Y'), ['size' => 9, 'color' => '6B7280'], ['alignment' => 'center']);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempPath);

        return $tempPath;
    }

    private static function fillExpedienteGeneralWordSection($section, $paciente, $seccionesPersonalizadas, $enfermedadesVinculadas)
    {
        $section->addText('EXPEDIENTE CLÍNICO GENERAL', ['bold' => true, 'size' => 16, 'color' => '111827'], ['alignment' => 'center']);
        $section->addText('Paciente: ' . $paciente->name, ['bold' => true, 'size' => 12, 'color' => '374151'], ['alignment' => 'center']);
        $section->addTextBreak(1);

        $section->addText('Datos Personales', ['bold' => true, 'size' => 14, 'color' => '111827']);
        $infoTable = $section->addTable(['borderSize' => 0]);
        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Cédula: ' . ($paciente->cedula ?? 'N/A'), ['size' => 10, 'color' => '374151']);
        $infoTable->addCell(4500)->addText('Edad: ' . ($paciente->fecha_nacimiento ? Carbon::parse($paciente->fecha_nacimiento)->age . ' años' : 'N/A'), ['size' => 10, 'color' => '374151']);
        $infoTable->addRow();
        $infoTable->addCell(4500)->addText('Teléfono: ' . ($paciente->telefono ?? 'N/A'), ['size' => 10, 'color' => '374151']);
        $infoTable->addCell(4500)->addText('Carrera/PNF: ' . ($paciente->pnf ?? 'N/A'), ['size' => 10, 'color' => '374151']);
        $section->addTextBreak(1);

        $apaStyle = ['indentation' => ['firstLine' => 720], 'alignment' => Jc::BOTH, 'lineHeight' => 1.5];

        foreach ($seccionesPersonalizadas as $seccion) {
            $section->addText(htmlspecialchars($seccion->titulo), ['bold' => true, 'size' => 14, 'color' => '111827']);
            if ($seccion->descripcion_general) {
                $section->addText(htmlspecialchars($seccion->descripcion_general), ['italic' => true, 'color' => '6B7280'], $apaStyle);
            }
            foreach ($seccion->segmentos as $segmento) {
                $section->addText(htmlspecialchars($segmento->titulo . ':'), ['bold' => true, 'color' => '374151']);
                $textoPlano = strip_tags($segmento->contenido ?? 'Sin contenido registrado.');
                $section->addText(htmlspecialchars($textoPlano), ['color' => '4B5563'], $apaStyle);
                $section->addTextBreak(1);
            }
            $section->addTextBreak(1);
        }
    }

    private static function fillCronologiaSection($section, $citasSeleccionadas)
    {
        $sesionNum = 0;
        foreach ($citasSeleccionadas as $cita) {
            $sesionNum++;
            $fechaStr = $cita->fecha ? $cita->fecha->format('d M Y') : 'Sin fecha';
            $motivo = $cita->motivo ?? 'Consulta General';

            $section->addText('');
            $section->addText('Sesión No. ' . $sesionNum . ' - ' . $motivo . ' (' . $fechaStr . ')', ['bold' => true, 'size' => 11, 'color' => '374151']);

            $notasData = null;
            if ($cita->notas) {
                $notasData = json_decode($cita->notas, true);
                if (json_last_error() !== JSON_ERROR_NONE) $notasData = null;
            }

            $apaStyle = [
                'alignment' => Jc::BOTH,
                'indentation' => ['firstLine' => 720],
                'lineHeight' => 2.0,
                'spaceAfter' => 120
            ];

            $labelFont = ['bold' => true, 'size' => 10, 'color' => '111827'];
            $bodyFont = ['size' => 10, 'color' => '374151'];

            if ($notasData && is_array($notasData)) {
                $camposDinamicos = CitaNotaEvolucion::obtenerPorCita($cita->id);
                
                foreach($camposDinamicos as $campo) {
                    if (!empty(trim($campo->contenido))) {
                        $run = $section->addTextRun($apaStyle);
                        $run->addText(htmlspecialchars($campo->titulo . ': '), $labelFont);
                        $run->addText(htmlspecialchars(trim($campo->contenido)), $bodyFont);
                    }
                }

                if (!empty($notasData['avance_estado'])) {
                    $run = $section->addTextRun($apaStyle);
                    $run->addText('Avance: ', $labelFont);
                    $avanceStr = ucfirst(str_replace('_', ' ', $notasData['avance_estado']));
                    if (!empty($notasData['avance_detalle'])) {
                        $avanceStr .= ' - ' . $notasData['avance_detalle'];
                    }
                    $run->addText(htmlspecialchars($avanceStr), $bodyFont);
                }
            } else {
                $run = $section->addTextRun($apaStyle);
                $run->addText(htmlspecialchars($cita->notas_limpias ?? 'Sin notas registradas.'), ['size' => 10, 'color' => '6B7280', 'italic' => true]);
            }
        }
    }
}
