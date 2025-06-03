<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\Meeting;
use App\Models\MeetingLog;
use App\Events\MessageSent;
use App\Models\ChatCallLog;
use App\Models\ChatLastLog;
use App\Models\ChatMessage;
use App\Models\CompanyGroup;
use App\Mail\MeetingInvitation;
use App\Events\GroupMessageSent;
use App\Models\CompanyGroupUser;
use App\Events\PrivateMessageSent;
use Illuminate\Support\Facades\Mail;

class ChatService
{
    public function submitChat($current_chat_user_type, $current_chat_user, $userLastLog, $message, $is_file, $mss_type = 'text')
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

        $chatmss = ChatMessage::create([
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

        if ($mss_type == 'call') {
            ChatCallLog::create([
                'send_user_id' => auth()->user()->id,
                'recieve_user_id' => $current_chat_user,
                'chatbtw' => $current_chat_user_type,
                'mss_id' => $chatmss->id,
            ]);
        }


        if ($current_chat_user_type == 'group') {
            broadcast(new GroupMessageSent(auth()->user()->id, $current_chat_user, $message))->toOthers();
        } else {
            broadcast(new PrivateMessageSent(auth()->user()->id, $current_chat_user, [
                'state' => 'message',
                'user' => encrypt($current_chat_user),
                'name' => $chatmss->userTo->name,
                'message' => $message,
                'data' => $chatmss
            ]))->toOthers();
        }

        return [
            'userlog' => $userLog,
            'recieve_user_id' => encrypt($current_chat_user),
            'chat_message' => $message,
            'current_chat_user_type' => $current_chat_user_type,
            'mss_chat' => [
                "id" => encrypt($chatmss->id),
                "user_id" => encrypt($chatmss->user_id),
                "user_to" => encrypt($chatmss->user_to),
                "group_to" => $chatmss->group_to,
                "reference_chat" => $chatmss->reference_chat,
                "user_group" => $chatmss->user_group,
                "is_file" => $chatmss->is_file,
                "file_type" => $chatmss->file_type,
                "is_read" => $chatmss->is_read,
            ]
        ];
    }


    public function meetingInvitationGroup($meetings_id, $group_id)
    {
        $meet = Meeting::find(decrypt($meetings_id));
        $group = CompanyGroupUser::where('group_id', decrypt($group_id))->get();
        foreach ($group as $value) {
            MeetingLog::updateOrCreate([
                'meetings_id' => $meet->id,
                'user_id' => $value->user_id,
            ], [
                'join_status' => 'invite'
            ]);

            $usr = User::find($value->user_id);
            if ($usr) {
                Mail::to($usr->email)->send(new MeetingInvitation($usr->name, $usr->email, $meet));
            }
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $meet
            ],
            201
        );
    }

    public function meetingInvitation($meetings_id, $users)
    {
        $meet = Meeting::find(decrypt($meetings_id));
        $json = str_replace("'", '"', $users);
        $array = json_decode($json, true);
        foreach ($array as $value) {
            MeetingLog::updateOrCreate([
                'meetings_id' => $meet->id,
                'user_id' => decrypt($value),
            ], [
                'join_status' => 'invite'
            ]);

            $usr = User::find(decrypt($value));
            if ($usr) {
                Mail::to($usr->email)->send(new MeetingInvitation($usr->name, $usr->email, $meet));
            }
        }

        return $meet;
    }
}
