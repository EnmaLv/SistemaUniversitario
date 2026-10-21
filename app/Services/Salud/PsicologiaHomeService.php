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

        $proximaCita = DB::table('citas')
            ->join('user', 'citas.psicologo_id', '=', 'user.id_use')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->select('citas.*', 'persona.nombres_per as psi_nombres', 'persona.apellidos_per as psi_apellidos')
            ->where('citas.user_id', $userId)
            ->where('citas.estado', 'confirmada')
            ->where('citas.fecha', '>=', $now->format('Y-m-d'))
            ->orderBy('citas.fecha', 'asc')
            ->orderBy('citas.hora', 'asc')
            ->first();

        if ($proximaCita) {
            $fechaHoraP = Carbon::parse("{$proximaCita->fecha} " . ($proximaCita->hora ?? '00:00:00'));
            if ($fechaHoraP->isPast()) {
                $proximaCita = null;
            } else {
                $proximaCita->psicologo_nombre = $this->formatearNombre($proximaCita->psi_nombres, $proximaCita->psi_apellidos);
            }
        }

        $citaPendiente = DB::table('citas')
            ->join('user', 'citas.psicologo_id', '=', 'user.id_use')
            ->join('persona', 'user.per_id', '=', 'persona.id_per')
            ->select('citas.*', 'persona.nombres_per as psi_nombres', 'persona.apellidos_per as psi_apellidos')
            ->where('citas.user_id', $userId)
            ->where('citas.estado', 'pendiente')
            ->orderBy('citas.created_at', 'desc')
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
                $citaNotif = DB::table('citas')->where('id', $data['cita_id'])->first();
                if (!$citaNotif || ($citaNotif->fecha && $citaNotif->hora && Carbon::parse("{$citaNotif->fecha} {$citaNotif->hora}")->isPast())) {
                    $notificacionCita = null;
                }
            }
        }

        if (!$notificacionCita && $proximaCita) {
            $notificacionCita = (object)[
                'type' => 'App\Notifications\CitaConfirmedNotification',
                'data' => json_encode([
                    'cita_id' => $proximaCita->id,
                    'body'    => '¡Tienes una cita confirmada!'
                ])
            ];
        }

        $diaActual = Carbon::now()->dayOfWeek;
        $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo(Auth::id());

        $horariosHoy = collect();
        if ($grupoActivo) {
            $horariosHoy = GrupoHorario::obtenerHorariosHoy($grupoActivo->id, $diaActual);
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