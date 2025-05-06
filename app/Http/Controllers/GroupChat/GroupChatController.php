<?php

namespace App\Http\Controllers\GroupChat;

use App\Http\Controllers\Controller;
use App\Models\GroupChat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupChatController extends Controller
{
    // Get all group chats for the authenticated user
    public function index()
    {
        $groupChats = Auth::user()->groupChats()->with('sport')->get();

        return response()->json([
            'status' => 'success',
            'group_chats' => $groupChats
        ]);
    }

    // get all the users on the groupchats
    public function users($id)
    {
        // return group member on selected sports with sport category performance
        // $groupChatUsers = GroupChat::where('id',$id)->with(['users','sport.categories.performances'])->get();
        $groupChatUsers = GroupChat::where('id', $id)->with([
                'users',
                'sport.categories.performances' => function ($query) {
                    $query->whereDate('recorded_at', today());
                }
            ])
            ->get();

        return response()->json([
            'status' => 'success',
            'group_users' => $groupChatUsers
        ]);
    }

    // Get messages for a specific group chat
    public function show($id)
    {
        $groupChat = GroupChat::with(['messages.sender'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'group_chat' => $groupChat
        ]);
    }

    // Send a message in a group chat
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // max 10MB
        ]);

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $message = Message::create([
            'group_chat_id' => $id,
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'attachment' => $attachmentPath, // store file path (nullable column)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }
}
