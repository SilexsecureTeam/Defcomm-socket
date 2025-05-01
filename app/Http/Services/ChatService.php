<?php

namespace App\Http\Services;

use App\Models\ChatLastLog;
use App\Models\ChatMessage;
use App\Events\GroupMessageSent;
use App\Events\PrivateMessageSent;
use App\Events\MessageSent;

class ChatService
{
    public function submitChat($current_chat_user_type, $current_chat_user, $userLastLog, $message, $is_file)
    {
        $userLog = $userLastLog ?? uniqid();

        if ($userLastLog) {
            $userLog = $userLastLog;
        } else {
            $userLog = $current_chat_user_type == 'user' ? uniqid() : $current_chat_user->id;
        }

        ChatLastLog::updateOrCreate([
            'user_id' => auth()->user()->id,
            'user_to' => $current_chat_user_type == 'user' ? $current_chat_user : null,
            'group_to' => $current_chat_user_type == 'group' ? $current_chat_user : $userLog,
        ], [
            'unseen_count' => null,
            'user_group' => $current_chat_user_type,
            'is_file' => $is_file,
            'last_message' => encrypt($message)
        ]);

        if ($current_chat_user_type == 'user') {
            ChatLastLog::updateOrCreate([
                'user_id' => $current_chat_user,
                'user_to' => auth()->user()->id,
                'group_to' => $userLog,
            ], [
                'unseen_count' => null,
                'user_group' => $current_chat_user_type,
                'is_file' => $is_file,
                'last_message' => encrypt($message)
            ]);
        }

        ChatMessage::create([
            'user_id' => auth()->user()->id,
            'user_to' => $current_chat_user_type == 'user' ? $current_chat_user : null,
            'group_to' => $current_chat_user_type == 'group' ? $current_chat_user : $userLog,
            'reference_chat' => null,
            'user_group' => $current_chat_user_type,
            'is_file' => $is_file,
            'file_type' => 'other',
            'is_read' => 'no',
            'is_important' => 'no',
            'is_forward' => 'no',
            'is_star' => 'no',
            'view_once' => 'no',
            'expire_time' => null,
            'message' => encrypt($message)
        ]);

        
        if ($current_chat_user_type == 'group') {
            broadcast(new GroupMessageSent(auth()->user()->id, $current_chat_user, $message))->toOthers();
        } else {
            broadcast(new PrivateMessageSent(auth()->user()->id, $current_chat_user, $message))->toOthers();
        }

        return [auth()->user()->id, $userLog, $current_chat_user, $message, $current_chat_user_type];
    }
}