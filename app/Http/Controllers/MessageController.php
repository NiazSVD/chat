<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User; 
use Illuminate\Http\Request;

class MessageController extends Controller
{

    public function index()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        return view('message', compact('users'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:255',
        ]);

        $message = Message::create([
            'user_id' => auth()->id(),
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['success' => true]);
    }

    public function getMessages($userId)
    {

        $messages = Message::where(function($q) use ($userId) {
            $q->where('user_id', auth()->id())
            ->where('receiver_id', $userId);
        })->orWhere(function($q) use ($userId) {
                    $q->where('user_id', $userId)
                    ->where('receiver_id', auth()->id());
                })
                ->orderBy('created_at', 'asc')
                ->get();



        Message::where('user_id', $userId)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
 
        return response()->json($messages);
    }



    public function markAsRead(Request $request)
    {
        $senderId = $request->sender_id;
        $receiverId = auth()->id();

        Message::where('user_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }



}
