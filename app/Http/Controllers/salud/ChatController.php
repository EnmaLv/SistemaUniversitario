<?php

namespace App\Http\Controllers\salud;

use App\Models\Usuario;
use App\Http\Controllers\Controller;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\salud\Conversation;
use App\Models\salud\Message;
use App\Models\salud\Notification as SaludNotification;

class ChatController extends Controller
{
    /* ============================================================
     |  LISTA DE CONTACTOS (solo los que pueden chatear contigo)
     * ============================================================ */
    private function getContactsData()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $userId = $user->id_usuario;
        $isPsicologo = $user->tieneRol(['psicologo', 'administrador']);
        $contacts = $user->obtenerContactosParaChat($userId, $isPsicologo);

        return $contacts->map(function ($contact) use ($userId) {
            $conversation = Conversation::obtenerConversacion($userId, $contact->id_usuario);
            $lastMessage  = $conversation ? Message::obtenerUltimoMensaje($conversation->id) : null;

            $unreadCount = $conversation
                ? DB::table('messages')->where('conversation_id', $conversation->id)
                    ->where('sender_id', $contact->id_usuario)
                    ->whereNull('read_at')
                    ->count()
                : 0;

            return [
                'id'                => $contact->id_usuario,
                'name'              => $contact->name,
                'avatar'            => strtoupper(substr($contact->name, 0, 2)),
                'lastMessage'       => $lastMessage ? $lastMessage->body : 'Inicia una conversación',
                'time'              => $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans() : '',
                'last_message_time' => $lastMessage ? Carbon::parse($lastMessage->created_at)->timestamp : 0,
                'unreadCount'       => $unreadCount,
                'status'            => 'Conectado',
                'is_new'            => false,
            ];
        })->sortByDesc('last_message_time')->values();
    }

    public function index()
    {
        $contactsData = $this->getContactsData();
        return view('chat.index', compact('contactsData'));
    }

    public function fetchContacts()
    {
        return response()->json($this->getContactsData());
    }

    /* ============================================================
     |  BUSCAR USUARIOS (para staff/admin, ignora si hay conversación previa)
     * ============================================================ */
    public function buscarUsuarios(Request $request)
    {
        $q = trim($request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        /** @var Usuario $user */
        $user   = Auth::user();
        $userId = $user->id_usuario;

        // Solo staff puede buscar globalmente
        $roleSlugs = $user->roles
            ->pluck('slug')
            ->map(fn ($s) => strtolower((string) $s))
            ->toArray();

        $isStaff = !in_array('paciente', $roleSlugs, true)
                && !in_array('becario', $roleSlugs, true);

        if (!$isStaff) {
            return response()->json([]);
        }

        $like = '%' . mb_strtolower($q, 'UTF-8') . '%';

        $results = DB::table('usuario')
            ->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
            ->leftJoin('rol_usuario', 'usuario.id_usuario', '=', 'rol_usuario.id_usuario')
            ->leftJoin('rol', 'rol_usuario.id_rol', '=', 'rol.id_rol')
            ->select(
                'usuario.id_usuario as id',
                'usuario.username',
                DB::raw("TRIM(CONCAT(COALESCE(persona.nombre_persona,''),' ',COALESCE(persona.apellido_persona,''))) as name"),
                DB::raw("GROUP_CONCAT(DISTINCT COALESCE(rol.nombre,'') SEPARATOR ', ') as roles")
            )
            ->where('usuario.id_usuario', '!=', $userId)
            ->where(function ($q) use ($like) {
                $q->whereRaw("LOWER(COALESCE(persona.nombre_persona,'')) LIKE ?",   [$like])
                  ->orWhereRaw("LOWER(COALESCE(persona.apellido_persona,'')) LIKE ?", [$like])
                  ->orWhereRaw("LOWER(CONCAT(COALESCE(persona.nombre_persona,''),' ',COALESCE(persona.apellido_persona,''))) LIKE ?", [$like])
                  ->orWhereRaw("LOWER(COALESCE(usuario.username,'')) LIKE ?",       [$like]);
            })
            ->groupBy('usuario.id_usuario', 'usuario.username', 'persona.nombre_persona', 'persona.apellido_persona')
            ->orderBy('persona.nombre_persona')
            ->limit(20)
            ->get();

        // Marcar quiénes ya están en la lista de contactos
        $contactIds = $this->getContactsData()->pluck('id')->toArray();

        return response()->json($results->map(function ($u) use ($contactIds) {
            $firstName    = explode(' ', trim($u->name))[0] ?? '';
            $firstLastName = explode(' ', trim($u->name))[1] ?? '';
            return [
                'id'         => $u->id,
                'name'       => trim($firstName . ' ' . $firstLastName) ?: $u->name,
                'full_name'  => $u->name,
                'avatar'     => strtoupper(mb_substr($u->name, 0, 2)),
                'roles'      => $u->roles,
                'lastMessage'=> 'Inicia una conversación',
                'time'       => '',
                'unreadCount'=> 0,
                'last_message_time' => 0,
                'is_new'     => !in_array($u->id, $contactIds, true),
            ];
        }));
    }

    /* ============================================================
     |  AUTORIZACIÓN CENTRAL
     * ============================================================ */
    private function puedeEscribirA(int $userId, int $targetUserId): bool
    {
        if ($userId === $targetUserId) return false;

        $current = Usuario::with('roles')->find($userId);
        $target  = Usuario::with('roles')->find($targetUserId);
        if (!$current || !$target) return false;

        $currentRoles = $current->roles->pluck('slug')->map(fn ($s) => strtolower((string) $s))->toArray();
        $targetRoles  = $target->roles->pluck('slug')->map(fn ($s) => strtolower((string) $s))->toArray();

        $isPacienteOBecario = in_array('paciente', $currentRoles, true)
                           || in_array('becario', $currentRoles, true);

        // Paciente/becario → solo con psicólogos con cita
        if ($isPacienteOBecario) {
            return DB::table('citas')
                ->where('user_id', $userId)
                ->where('psicologo_id', $targetUserId)
                ->exists();
        }

        $targetEsStaff = !in_array('paciente', $targetRoles, true)
                      && !in_array('becario', $targetRoles, true);

        // Staff ↔ Staff
        if ($targetEsStaff) return true;

        if (in_array('psicologo', $currentRoles, true) || in_array('administrador', $currentRoles, true)) {
            // Admin: puede escribir a cualquiera
            if (in_array('administrador', $currentRoles, true)) {
                return true;
            }
            return DB::table('citas')->where('user_id', $targetUserId)->where('psicologo_id', $userId)->exists();
        }

        return false;
    }

    /* ============================================================
     |  PING / MENSAJES / ENVÍO
     * ============================================================ */
    public function ping(Request $request)
    {
        $request->validate(['chat_activo_user_id' => 'required|integer']);

        /** @var Usuario $user */
        $user   = Auth::user();
        $userId = $user->id_usuario;
        $target = (int) $request->chat_activo_user_id;

        if ($this->puedeEscribirA($userId, $target)) {
            Message::registrarActividadChat($userId, $target);
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'No autorizado'], 403);
    }

    public function fetchMessages($targetUserId)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $userId = $user->id_usuario;

        if (!$this->puedeEscribirA($userId, (int) $targetUserId)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $conversation = Conversation::obtenerOUCrearConversacion($userId, $targetUserId);

        Message::marcarLeidos($conversation->id, $targetUserId);
        SaludNotification::limpiarNotificacionesMensajes($userId, $targetUserId);
        Message::cancelarNotificacionesPendientes($userId, $targetUserId);

        $rawMessages = Conversation::obtenerMensajes($conversation->id);
        $messages = $rawMessages->map(function ($msg) use ($userId) {
            return [
                'id'      => $msg->id,
                'body'    => $msg->body,
                'is_mine' => $msg->sender_id === $userId,
                'time'    => Carbon::parse($msg->created_at)->format('h:i A'),
            ];
        });

        return response()->json([
            'messages'        => $messages,
            'conversation_id' => $conversation->id,
        ]);
    }

    public function sendMessage(Request $request, $targetUserId)
    {
        $request->validate(['body' => 'required|string']);

        /** @var Usuario $user */
        $user   = Auth::user();
        $userId = $user->id_usuario;

        if (!$this->puedeEscribirA($userId, (int) $targetUserId)) {
            return response()->json(['error' => 'No tienes permiso para iniciar esta conversación.'], 403);
        }

        $conversation = Conversation::obtenerOUCrearConversacion($userId, $targetUserId);
        $message      = Message::crearMensaje($conversation->id, $userId, $request->body);

        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            Log::error("Error broadcasting message: " . $e->getMessage());
        }

        return response()->json([
            'id'      => $message->id,
            'body'    => $message->body,
            'is_mine' => true,
            'time'    => Carbon::parse($message->created_at)->format('h:i A'),
        ]);
    }
}