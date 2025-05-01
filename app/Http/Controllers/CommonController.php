<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Files;
use App\Models\ChatSettings;
use Illuminate\Http\Request;
use App\Http\Services\ChatService;
use App\Http\Services\FileUploadService;

class CommonController extends Controller
{
    public $FileUploadService, $ChatService;

    public function __construct()
    {
        $this->FileUploadService = new FileUploadService();
        $this->ChatService = new ChatService();
    }

    public function submitChatSetting(Request $request)
    {
        ChatSettings::updateOrCreate(['user_id' => auth()->user()->id], [
            'hide_message_style' => $request->hide_message_style,
            'hide_message' => $request->hide_message,
        ]);

        return redirect()->back()->with('success', "Chat setting updated successfully");
    }

    public function submitChatFile(Request $request)
    {
        $userLog = $request->userLastLog ?? uniqid();
        $file = $this->FileUploadService->submitFile($request);
        $this->ChatService->submitChat(
            $request->current_chat_user_type,
            $request->current_chat_user,
            $userLog,
            $file['data']['id'],
            'yes'
            );

        return redirect()->back()->with('success', "File Securely uploaded");
    }

    public function fileView($id, $user)
    {
        $file = Files::find(decrypt($id));
        return view('admin.fileView', [
            'file' => $file,
            'user' => User::find(decrypt($user)),
        ]);
    }
}
