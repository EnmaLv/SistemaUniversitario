<?php

namespace App\Models\salud;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public static function obtenerPorIdYUsuario($id, $userId)
    {
        return DB::table('notifications')
            ->where('id', $id)
            ->where('notifiable_id', $userId)
            ->first();
    }

    public static function obtenerNotificacionesRecientes($userId)
    {
        $fechaLimite = now()->subMonth();

        $notificaciones = DB::table('notifications')
            ->where('notifiable_type', Usuario::class)
            ->where('notifiable_id', $userId)
            ->where('created_at', '>=', $fechaLimite)
            ->orderBy('created_at', 'desc')
            ->get();

        $psicologos = Usuario::all()->filter(fn($usuario) => $usuario->tieneRol('psicologo'));

        $replacements = [];
        foreach ($psicologos as $psi) {
            $nombres = trim($psi->persona->nombre_persona ?? '');
            $apellidos = trim($psi->persona->apellido_persona ?? '');
            $fullName = trim($nombres . ' ' . $apellidos);
            if ($fullName) {
                $firstName = explode(' ', $nombres)[0] ?? '';
                $firstLastName = explode(' ', $apellidos)[0] ?? '';
                $shortName = trim($firstName . ' ' . $firstLastName);
                $replacements[$fullName] = $shortName;
            }
        }

        return $notificaciones->map(function ($notif) use ($replacements) {
            $data = is_string($notif->data) ? json_decode($notif->data, true) : $notif->data;

            if (is_array($data)) {
                if (isset($data['body'])) {
                    foreach ($replacements as $full => $short) {
                        $data['body'] = str_replace($full, $short, $data['body']);
                    }
                }
                if (isset($data['psicologo_name'])) {
                    foreach ($replacements as $full => $short) {
                        if ($data['psicologo_name'] === $full) {
                            $data['psicologo_name'] = $short;
                        }
                    }
                }
            }

            $notif->data = $data;
            return $notif;
        });
    }

    public static function obtenerConteoNoLeidas($userId)
    {
        return DB::table('notifications')
            ->where('notifiable_type', Usuario::class)
            ->where('notifiable_id', $userId)
            ->where('created_at', '>=', now()->subMonth())
            ->whereNull('read_at')
            ->count();
    }

    public static function marcarComoLeida($id)
    {
        return DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);
    }

    public static function marcarTodasComoLeidas($userId)
    {
        return DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function limpiarNotificacionesMensajes($userId, $targetUserId)
    {
        try {
            DB::beginTransaction();
            $notifications = DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->where('type', 'App\Notifications\NewMessageNotification')
                ->whereNull('read_at')
                ->get();

            foreach ($notifications as $notification) {
                $data = json_decode($notification->data, true);
                if (($data['sender_id'] ?? null) == $targetUserId) {
                    DB::table('notifications')
                        ->where('id', $notification->id)
                        ->update(['read_at' => now(), 'updated_at' => now()]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}