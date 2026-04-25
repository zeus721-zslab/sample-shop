<?php

namespace App\Http\Controllers;

use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function __construct(private JwtService $jwt) {}

    /**
     * 채팅 소켓 토큰 발급 (로그인 사용자 전용)
     */
    public function token(Request $request)
    {
        $user   = $request->user();
        $secret = config('chat.jwt_secret');

        $token = $this->jwt->userChatToken((string) $user->id, $secret);

        return response()->json(['token' => $token]);
    }

    /**
     * 채팅방 조회 또는 생성 (1:1 문의)
     */
    public function findOrCreateRoom(Request $request)
    {
        $user   = $request->user();
        $userId = (string) $user->id;

        $room = DB::table('chat_rooms as r')
            ->join('chat_participants as p', 'r.id', '=', 'p.room_id')
            ->where('r.type', '1to1')
            ->where('r.is_active', 1)
            ->where('p.user_id', $userId)
            ->where('p.user_type', 'user')
            ->select('r.*')
            ->first();

        if (!$room) {
            $roomId = DB::table('chat_rooms')->insertGetId([
                'type'       => '1to1',
                'name'       => $user->name . '님의 문의',
                'is_active'  => 1,
                'created_at' => now(),
            ]);

            DB::table('chat_participants')->insert([
                'room_id'   => $roomId,
                'user_id'   => $userId,
                'user_type' => 'user',
                'joined_at' => now(),
            ]);

            $room = DB::table('chat_rooms')->find($roomId);
        }

        $unread = DB::table('chat_messages')
            ->where('room_id', $room->id)
            ->where('sender_type', 'admin')
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'room'   => $room,
            'unread' => $unread,
        ]);
    }

    /**
     * 채팅방 메시지 목록
     */
    public function messages(Request $request, int $roomId)
    {
        $user = $request->user();

        $participant = DB::table('chat_participants')
            ->where('room_id', $roomId)
            ->where('user_id', (string) $user->id)
            ->where('user_type', 'user')
            ->first();

        if (!$participant) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $messages = DB::table('chat_messages')
            ->where('room_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    /**
     * 미읽은 메시지 수 조회
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $rooms = DB::table('chat_participants')
            ->where('user_id', (string) $user->id)
            ->where('user_type', 'user')
            ->pluck('room_id');

        $count = DB::table('chat_messages')
            ->whereIn('room_id', $rooms)
            ->where('sender_type', 'admin')
            ->where('is_read', 0)
            ->count();

        return response()->json(['unread' => $count]);
    }
}
