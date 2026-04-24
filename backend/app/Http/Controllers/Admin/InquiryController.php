<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InquiryController extends Controller
{
    public function index()
    {
        $rooms = DB::table('chat_rooms as r')
            ->leftJoin('chat_participants as p', function($join) {
                $join->on('r.id', '=', 'p.room_id')
                     ->where('p.user_type', 'user');
            })
            ->leftJoin('users as u', DB::raw('CAST(p.user_id AS UNSIGNED)'), '=', 'u.id')
            ->where('r.type', '1to1')
            ->where('r.is_active', 1)
            ->select(
                'r.id',
                'r.name',
                'r.created_at',
                'u.name as user_name',
                'u.email as user_email',
                DB::raw('(SELECT COUNT(*) FROM chat_messages WHERE room_id = r.id AND sender_type = "user" AND is_read = 0) as unread_count'),
                DB::raw('(SELECT created_at FROM chat_messages WHERE room_id = r.id ORDER BY created_at DESC LIMIT 1) as last_message_at'),
                DB::raw('(SELECT message FROM chat_messages WHERE room_id = r.id ORDER BY created_at DESC LIMIT 1) as last_message')
            )
            ->orderByRaw('unread_count DESC')
            ->orderBy('last_message_at', 'desc')
            ->get();

        $chatJwtSecret = config('chat.jwt_secret');
        $adminToken = $this->generateAdminJwt($chatJwtSecret);

        return view('admin.inquiries.index', compact('rooms', 'adminToken'));
    }

    private function generateAdminJwt(string $secret): string
    {
        $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'userId'   => 'admin',
            'userType' => 'admin',
            'iat'      => time(),
            'exp'      => time() + 3600,
        ]));
        $header  = rtrim(strtr($header, '+/', '-_'), '=');
        $payload = rtrim(strtr($payload, '+/', '-_'), '=');
        $sig     = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", $secret, true)), '+/', '-_'), '=');
        return "$header.$payload.$sig";
    }
}
