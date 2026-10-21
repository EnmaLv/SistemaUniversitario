<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

class PublicacionReaccionController extends Controller
{
    public function toggle($publicacionId)
    {
        /** @var Usuario $user */
        $userId = Auth::user()->id_usuario ?? Auth::id();

        $publicacion = DB::table('publicaciones')->where('id', $publicacionId)->first();
        if (!$publicacion) {
            return response()->json(['error' => 'Publicación no encontrada'], 404);
        }

        $reaccion = DB::table('publicacion_reacciones')
            ->where('publicacion_id', $publicacionId)
            ->where('paciente_id', $userId)
            ->first();

        if ($reaccion) {
            DB::table('publicacion_reacciones')->where('id', $reaccion->id)->delete();
            $status = 'removed';
        } else {
            DB::table('publicacion_reacciones')->insert([
                'publicacion_id' => $publicacionId,
                'paciente_id' => $userId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $status = 'added';
        }

        $totalLikes = DB::table('publicacion_reacciones')
            ->where('publicacion_id', $publicacionId)
            ->count();

        $notificacionesPrevias = DB::table('notifications')
            ->where('notifiable_id', $publicacion->psicologo_id)
            ->where('type', 'App\\Notifications\\ReaccionPublicacionNotification')
            ->get();
            
        foreach ($notificacionesPrevias as $notif) {
            $data = json_decode($notif->data, true);
            if (isset($data['publicacion_id']) && $data['publicacion_id'] == $publicacionId) {
                DB::table('notifications')->where('id', $notif->id)->delete();
            }
        }

        if ($totalLikes > 0) {
            $ultimaReaccion = DB::table('publicacion_reacciones')
                ->where('publicacion_id', $publicacionId)
                ->orderBy('created_at', 'desc')
                ->first();
                
            $ultimoUsuario = Usuario::with('persona')->where('id_usuario', $ultimaReaccion->paciente_id)->first();
            $nombreUltimo = ($ultimoUsuario && $ultimoUsuario->persona) ? $ultimoUsuario->persona->nombres_per : 'Un usuario';

            if ($totalLikes == 1) {
                $mensaje = "{$nombreUltimo} le dio me gusta a tu aviso.";
            } else {
                $otros = $totalLikes - 1;
                $mensaje = "{$nombreUltimo} y {$otros} personas más le dieron me gusta a tu aviso.";
            }

            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\ReaccionPublicacionNotification',
                'notifiable_type' => 'App\\Models\\Usuario',
                'notifiable_id' => $publicacion->psicologo_id,
                'data' => json_encode([
                    'type_id' => 'reaccion_aviso',
                    'body' => $mensaje,
                    'url' => route('admin.psicologia.maestros.publicaciones.index'),
                    'publicacion_id' => $publicacionId
                ]),
                'read_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        return response()->json([
            'status' => $status,
            'total_likes' => $totalLikes
        ]);
    }
}
