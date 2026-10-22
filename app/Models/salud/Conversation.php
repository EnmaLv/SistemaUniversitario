<?php

namespace App\Models\salud;

use Illuminate\Support\Facades\DB;

class Conversation
{
    public static function obtenerUsuarioUno($userOneId)
    {
        return DB::table('user')->where('id_use', $userOneId)->first();
    }

    public static function obtenerUsuarioDos($userTwoId)
    {
        return DB::table('user')->where('id_use', $userTwoId)->first();
    }

    public static function obtenerMensajes($conversationId)
    {
        return DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->get();
    }

    public static function obtenerConversacion($userId, $targetUserId)
    {
        return DB::table('conversations')
            ->where(function($query) use ($userId, $targetUserId) {
                $query->where('user_one_id', $userId)->where('user_two_id', $targetUserId);
            })->orWhere(function($query) use ($userId, $targetUserId) {
                $query->where('user_one_id', $targetUserId)->where('user_two_id', $userId);
            })->first();
    }

    public static function obtenerOUCrearConversacion($userId, $targetUserId)
    {
        $conv = self::obtenerConversacion($userId, $targetUserId);
        if (!$conv) {
            try {
                DB::beginTransaction();
                $id = DB::table('conversations')->insertGetId([
                    'user_one_id' => $userId,
                    'user_two_id' => $targetUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::commit();
                $conv = self::obtenerConversacion($userId, $targetUserId);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        return $conv;
    }
}
