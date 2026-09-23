<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        return view('pages.chat.index');
    }

    public function fetchConversations()
    {
        $conversations = Conversation::with(['customer', 'latestMessage'])
            ->withCount(['messages as unread_count' => function($query) {
                $query->where('is_read', false)->where('sender_type', 'customer');
            }])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->orderByDesc('created_at')
                    ->limit(1)
            )
            ->get();

        return response()->json($conversations);
    }

    public function fetchMessages($id)
    {
        // Mark customer messages as read when admin opens the conversation
        Message::where('conversation_id', $id)
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where('conversation_id', $id)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();
        return response()->json($messages);
    }

    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($id);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id() ?? 1, // Fallback to 1 if not strictly authenticated
            'sender_type' => 'agent',
            'text' => $request->text,
        ]);

        // Broadcast to Pusher (Task 3 implemented here)
        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }
}
