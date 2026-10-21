<?php

namespace App\Services\Salud;

use App\Models\salud\Cita;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\salud\EstadoAnimoDiario;
use App\Models\salud\GrupoHorario;

class PsicologiaHomeService
{
    public function getPacienteData(): array
    {
        $userId = Auth::id();
        $now = Carbon::now();

        $saludo = match (true) {
            $now->hour >= 5 && $now->hour < 12  => 'Buenos días',
            $now->hour >= 12 && $now->hour < 19 => 'Buenas tardes',
            default                             => 'Buenas noches',
        };

        $estadoAnimoHoy = EstadoAnimoDiario::getTodayForUser($userId);

        $proximaCita = DB::table('cita_psicologica')
            ->join('user', 'cita_psicologica.psicologo_use_id', '=', 'user.id_use')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->select('cita_psicologica.*', 'persona.nombres_per as psi_nombres', 'persona.apellidos_per as psi_apellidos')
            ->where('cita_psicologica.paciente_use_id', $userId)
            ->where('cita_psicologica.estado_cita_cps', 'confirmada')
            ->whereNull('cita_psicologica.deleted_at')
            ->where('cita_psicologica.fecha_cps', '>=', $now->format('Y-m-d'))
            ->orderBy('cita_psicologica.fecha_cps', 'asc')
            ->orderBy('cita_psicologica.hora_cps', 'asc')
            ->first();

        if ($proximaCita) {
            $fechaHoraP = Carbon::parse("{$proximaCita->fecha_cps} " . ($proximaCita->hora_cps ?? '00:00:00'));
            if ($fechaHoraP->isPast()) {
                $proximaCita = null;
            } else {
                $proximaCita->psicologo_nombre = $this->formatearNombre($proximaCita->psi_nombres, $proximaCita->psi_apellidos);
            }
        }

        $citaPendiente = DB::table('cita_psicologica')
            ->join('user', 'cita_psicologica.psicologo_use_id', '=', 'user.id_use')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->select('cita_psicologica.*', 'persona.nombres_per as psi_nombres', 'persona.apellidos_per as psi_apellidos')
            ->where('cita_psicologica.paciente_use_id', $userId)
            ->where('cita_psicologica.estado_cita_cps', 'pendiente')
            ->whereNull('cita_psicologica.deleted_at')
            ->orderBy('cita_psicologica.created_at', 'desc')
            ->first();

        if ($citaPendiente) {
            $citaPendiente->psicologo_nombre = $this->formatearNombre($citaPendiente->psi_nombres, $citaPendiente->psi_apellidos);
        }

        $publicaciones = DB::table('publicaciones')
            ->where('estatus', 1)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $notificacionCita = DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\Usuario')
            ->whereNull('read_at')
            ->whereIn('type', [
                'App\Notifications\CitaConfirmedNotification',
                'App\Notifications\CitaRechazadaNotification',
                'App\Notifications\CitaCancelledNotification'
            ])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($notificacionCita) {
            $data = json_decode($notificacionCita->data, true);
            if (($data['type_id'] ?? null) === 'cita_postponed') {
                $notificacionCita = null;
            } elseif (!empty($data['cita_id'])) {
                $citaNotif = DB::table('cita_psicologica')
                    ->where('id_cps', $data['cita_id'])
                    ->where('paciente_use_id', $userId)
                    ->whereNull('deleted_at')
                    ->first();
                if (!$citaNotif || ($citaNotif->fecha_cps && $citaNotif->hora_cps && Carbon::parse("{$citaNotif->fecha_cps} {$citaNotif->hora_cps}")->isPast())) {
                    $notificacionCita = null;
                }
            }
        }

        if (!$notificacionCita && $proximaCita) {
            $notificacionCita = (object)[
                'type' => 'App\Notifications\CitaConfirmedNotification',
                'data' => json_encode([
                    'cita_id' => $proximaCita->id_cps,
                    'body'    => '¡Tienes una cita confirmada!'
                ])
            ];
        }

        $diaActual = Carbon::now()->dayOfWeek;
        $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo(Auth::id());

        $horariosHoy = collect();
        if ($grupoActivo) {
            $horariosHoy = GrupoHorario::obtenerHorariosHoy($grupoActivo->id_gha, $diaActual);
        }

        $confirmadasHoy = Cita::obtenerCitasConfirmadasHoyPorPsicologo(Auth::id(), 3);
        $ultimasConfirmadas = Cita::obtenerUltimasCitasConfirmadasPsicologo(Auth::id(), 5);
        $estadisticasCitas = Cita::obtenerEstadisticasCitasPsicologo(Auth::id());
        $tendenciaPacientes = Cita::obtenerTendenciaSemanalCitasPsicologo(Auth::id(), 4);
        $citasPendientesAntiguas = Cita::obtenerCitasPendientesAntiguasPsicologo(Auth::id(), 5);

        return compact(
            'horariosHoy', 
            'confirmadasHoy', 
            'ultimasConfirmadas', 
            'estadisticasCitas', 
            'tendenciaPacientes', 
            'citasPendientesAntiguas',

            'estadoAnimoHoy',
            'saludo',
            'proximaCita',
            'citaPendiente',
            'publicaciones',
            'notificacionCita'
        );
    }

    private function formatearNombre(?string $nombres, ?string $apellidos): string
    {
        $first = explode(' ', trim($nombres ?? ''))[0] ?? '';
        $last  = explode(' ', trim($apellidos ?? ''))[0] ?? '';
        return trim("{$first} {$last}") ?: 'Psicólogo';
    }
}