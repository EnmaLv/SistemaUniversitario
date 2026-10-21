<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Message
{
    public static function obtenerUltimoMensaje($conversationId)
    {
        return DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->latest('created_at')
            ->first();
    }

    public static function marcarLeidos($conversationId, $senderId)
    {
        try {
            DB::beginTransaction();
            $res = DB::table('messages')
                ->where('conversation_id', $conversationId)
                ->where('sender_id', $senderId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            DB::commit();
            return $res;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function crearMensaje($conversationId, $senderId, $body)
    {
        try {
            DB::beginTransaction();
            $id = DB::table('messages')->insertGetId([
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'body' => $body,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $msg = DB::table('messages')->where('id', $id)->first();
            DB::commit();
            return $msg;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function obtenerConversacion($conversationId)
    {
        return DB::table('conversations')->where('id', $conversationId)->first();
    }

    public static function obtenerRemitente($senderId)
    {
        return DB::table('user')->where('id_use', $senderId)->first();
    }

    public static function registrarActividadChat($userId, $chatActivoId)
    {
        return DB::table('user')->where('id_use', $userId)->update([
            'ultima_actividad_chat_use' => now(),
            'chat_activo_use_id' => $chatActivoId,
        ]);
    }

    public static function usuarioEstaActivoEnChat($userId, $senderId)
    {
        $user = DB::table('user')->where('id_use', $userId)->first();
        if (!$user || !$user->ultima_actividad_chat_use || !$user->chat_activo_use_id) {
            return false;
        }

        if ($user->chat_activo_use_id == $senderId) {
            $actividad = Carbon::parse($user->ultima_actividad_chat_use);
            if (now()->diffInSeconds($actividad) <= 60) {
                return true;
            }
        }
        return false;
    }

    public static function programarNotificacionCorreo($senderId, $receiverId)
    {
        $pendiente = DB::table('chat_notificaciones_pendientes')
            ->where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('estado', 'pendiente')
            ->first();

        if ($pendiente) {
            DB::table('chat_notificaciones_pendientes')
                ->where('id', $pendiente->id)
                ->update([
                    'cantidad_mensajes' => $pendiente->cantidad_mensajes + 1,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('chat_notificaciones_pendientes')->insert([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'cantidad_mensajes' => 1,
                'programado_para' => now()->addMinutes(2), // 2 minutos de espera
                'estado' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public static function cancelarNotificacionesPendientes($receiverId, $senderId)
    {
        return DB::table('chat_notificaciones_pendientes')
            ->where('receiver_id', $receiverId)
            ->where('sender_id', $senderId)
            ->where('estado', 'pendiente')
            ->update([
                'estado' => 'cancelada',
                'updated_at' => now(),
            ]);
    }

    public static function obtenerNotificacionesParaEnviar()
    {
        return DB::table('chat_notificaciones_pendientes')
            ->whereIn('estado', ['pendiente', 'error'])
            ->where('programado_para', '<=', now())
            ->get();
    }

    public static function actualizarEstadoNotificacion($id, $estado)
    {
        return DB::table('chat_notificaciones_pendientes')
            ->where('id', $id)
            ->update([
                'estado' => $estado,
                'updated_at' => now(),
            ]);
    }
}
