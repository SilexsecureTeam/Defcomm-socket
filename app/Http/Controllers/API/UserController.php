<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Files;
use Firebase\JWT\JWT;
use App\Models\Folders;
use App\Models\Meeting;
use App\Models\FolderFile;
use App\Models\MeetingLog;
use App\Models\ChatCallLog;
use App\Models\ChatLastLog;
use App\Models\ChatMessage;
use App\Models\ContactList;
use App\Models\FilesShares;
use App\Models\ChatSettings;
use App\Models\CompanyGroup;
use App\Models\FileShareLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Mail\MeetingInvitation;
use App\Models\CompanyGroupUser;
use App\Events\PrivateMessageSent;
use App\Http\Services\ChatService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Services\FileUploadService;

class UserController extends Controller
{
    public $current_chat_user, $FileUploadService, $ChatService;

    public function __construct()
    {
        $this->FileUploadService = new FileUploadService();
        $this->ChatService = new ChatService();
    }

    public function file()
    {
        $file = Files::where('uploaded_by', auth()->user()->id)->orderBy('id', 'DESC')->get();

        $data = [];
        foreach ($file as $key => $dt) {
            $data[$key] = [
                'id' => encrypt($dt->id),
                'name' => $dt->name,
                'file' => $dt->file,
                'file_size' => $dt->file_size,
                'file_ext' => $dt->file_ext,
                'uploaded_by' => $dt->user->name,
                'description' => $dt->description,
                'created_at' => $dt->created_at,
                'updated_at' => $dt->updated_at,
                'status' => $dt->status,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record List',
                'data' => $data
            ],
            201
        );
    }

    public function fileOther()
    {
        $file = FilesShares::where('user_id', auth()->user()->id)->where('status', 'access')->orderBy('id', 'DESC')->get();

        $data = [];
        foreach ($file as $key => $dt) {
            $data[$key] = [
                'id' => encrypt($dt->id),
                'file_id' => encrypt($dt->file->id),
                'file_name' => $dt->file->name,
                'file_size' => $dt->file->file_size,
                'file_ext' => $dt->file->file_ext,
                'uploaded_by' => $dt->user->name,
                'shared_by' => $dt->userFrom->name,
                'description' => $dt->file->description,
                'shared_date' => $dt->created_at,
                'file_upload_date' => $dt->file->updated_at,
                'status' => $dt->status,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record List',
                'data' => $data
            ],
            201
        );
    }

    public function fileOtherPending()
    {
        $file = FilesShares::where('user_id', auth()->user()->id)->where('status', 'pending')->orderBy('id', 'DESC')->get();

        $data = [];
        foreach ($file as $key => $dt) {
            $data[$key] = [
                'id' => encrypt($dt->id),
                'file_id' => encrypt($dt->file->id),
                'file_name' => $dt->file->name,
                'file_size' => $dt->file->file_size,
                'file_ext' => $dt->file->file_ext,
                'uploaded_by' => $dt->user->name,
                'shared_by' => $dt->userFrom->name,
                'description' => $dt->file->description,
                'shared_date' => $dt->created_at,
                'file_upload_date' => $dt->file->updated_at,
                'status' => $dt->status,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record List',
                'data' => $data
            ],
            201
        );
    }

    public function fileRequest()
    {
        $file = FilesShares::where('user_id', auth()->user()->id)->where('status', 'block')->orderBy('id', 'DESC')->get();

        $data = [];
        foreach ($file as $key => $dt) {
            $data[$key] = [
                'id' => encrypt($dt->id),
                'file_id' => encrypt($dt->file->id),
                'file_name' => $dt->file->name,
                'file_size' => $dt->file->file_size,
                'file_ext' => $dt->file->file_ext,
                'uploaded_by' => $dt->user->name,
                'shared_by' => $dt->userFrom->name,
                'description' => $dt->file->description,
                'shared_date' => $dt->created_at,
                'file_upload_date' => $dt->file->updated_at,
                'status' => $dt->status,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record List',
                'data' => $data
            ],
            201
        );
    }

    public function fileUpload(Request $request)
    {
        $file = $request->file('file');
        $file_ext = $file->getClientOriginalExtension();

        // return dd($file_ext != "pdf" || $file_ext != "PDF");

        if ($file_ext != "pdf") {
            return response()->json(
                [
                    'status' => '400',
                    'message' => 'Ensure the file is PDF',
                    'data' => null
                ],
                401
            );
        }

        $file_size = $fileSize = $file->getSize();
        $file_name = time() . "secure." . $file->getClientOriginalExtension();
        $file->move(public_path('secure'), $file_name);

        if ($file_size >= 1073741824) {
            $file_size = number_format($file_size / 1073741824, 2) . ' GB';
        } elseif ($file_size >= 1048576) {
            $file_size = number_format($file_size / 1048576, 2) . ' MB';
        } elseif ($file_size >= 1024) {
            $file_size = number_format($file_size / 1024, 2) . ' KB';
        } else {
            $file_size = $file_size . ' bytes';
        }

        Files::create([
            'name' => $request->name,
            'description' => $request->description,
            'file' => $file_name,
            'file_size' => $file_size,
            'file_ext' => $file_ext,
            'fileSize_num' => $fileSize,
            'company_id' => auth()->user()->company_id,
            'uploaded_by' => auth()->user()->id,
            'user_type' => 'user'
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'File Securely uploaded',
                'data' => $file
            ],
            201
        );
    }

    public function fileShare(Request $request)
    {
        $id = decrypt($request->id);
        $user = json_decode($request->users, true);
        if (!empty($user)) {
            foreach ($user as $dt) {
                FilesShares::firstOrCreate([
                    'user_id' => $dt,
                    'file_id' => $id,
                ], [
                    'company_id' => auth()->user()->company_id,
                    'user_from' => auth()->user()->id,
                    'is_who' => 'user',
                    'status' => 'block'
                ]);
                // $usr = User::find($dt);
                // Mail::to($usr->email)->send(new FileShare($usr->name, $usr->email, auth()->user()->name));
            }

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'File successfully shared',
                    'data' => null
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'Please ensure to select a user',
                'data' => null
            ],
            401
        );
    }

    public function fileView($id)
    {
        $file = Files::find(decrypt($id));
        FileShareLog::create(['user_id' => auth()->user()->id, 'file_id' => $file->id, 'company_id' => auth()->user()->company_id]);
        return view('admin.fileView', [
            'file' => $file,
            'user' => auth()->user()
        ]);
    }

    public function fileViewUrl($id)
    {
        $file = Files::find(decrypt($id));
        FileShareLog::create(['user_id' => auth()->user()->id, 'file_id' => $file->id, 'company_id' => auth()->user()->company_id]);
        return response()->json(
            [
                'status' => '200',
                'url' => route('user.com.file.view', ['id' => encrypt($file->id), 'user' => encrypt(auth()->user()->id)]),
                'data' => null
            ],
            201
        );
    }

    public function fileAccept($id)
    {
        $idUser = decrypt($id);
        FilesShares::find($idUser)->update(['status' => 'access']);
        return response()->json(
            [
                'status' => '200',
                'message' => 'File accepted successfully',
                'data' => null
            ],
            201
        );
    }

    public function fileDecline($id)
    {
        $idUser = decrypt($id);
        FilesShares::find($idUser)->delete();
        return response()->json(
            [
                'status' => '200',
                'message' => 'File decline successfully',
                'data' => null
            ],
            201
        );
    }

    public function group()
    {
        $group = CompanyGroupUser::where('user_id', auth()->user()->id)->where('status', 'joined')->orderBy('id', 'DESC')->get();

        $data = [];
        foreach ($group as $key => $dt) {
            $data[$key] = [
                'id' => encrypt($dt->id),
                'company_name' => $dt->companyUser->name,
                'group_id' => encrypt($dt->companyGroup->id),
                'group_name' => $dt->companyGroup->name,
                'join_date' => $dt->join_date,
                'invitation_date' => $dt->created_at,
                'hide_my_detail' => $dt->hide,
                'status' => $dt->status,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record List',
                'data' => $data
            ],
            201
        );
    }

    public function groupPendig()
    {
        $group = CompanyGroupUser::where('user_id', auth()->user()->id)->where('status', 'pending')->orderBy('id', 'DESC')->get();

        $data = [];
        foreach ($group as $key => $dt) {
            $data[$key] = [
                'id' => encrypt($dt->id),
                'company_name' => $dt->companyUser->name,
                'group_name' => $dt->companyGroup->name,
                'join_date' => $dt->join_date,
                'invitation_date' => $dt->created_at,
                'hide_my_detail' => $dt->hide,
                'status' => $dt->status,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record List',
                'data' => $data
            ],
            201
        );
    }

    public function groupAccept($id)
    {
        $idUser = decrypt($id);
        CompanyGroupUser::find($idUser)->update(['status' => 'joined']);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Group accepted successfully',
                'data' => null
            ],
            201
        );
    }

    public function groupDecline($id)
    {
        $idUser = decrypt($id);
        CompanyGroupUser::find($idUser)->delete();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Group decline successfully',
                'data' => null
            ],
            201
        );
    }

    public function profile()
    {
        $user = User::find(auth()->user()->id);
        $data = [
            'id' => $user->id,
            $user
        ];
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record List',
                'data' => $user
            ],
            201
        );
    }

    public function profileUpload(Request $request)
    {
        $user = User::find(auth()->user()->id);

        if ($request->avatar) {
            $file = $request->file('avatar');
            $file_name = time() . "avatar." . $file->getClientOriginalExtension();
            $file->move(public_path('avatar'), $file_name);

            if ($user->avatar) {
                try {
                    unlink(public_path($user->avatar));
                } catch (\Exception $e) {
                    // Optionally log the error
                }
            }

            $user->update([
                'avatar' => 'avatar/' . $file_name,
            ]);
        }

        if ($request->name) {
            $user->update(['name' => $request->name]);
        }

        if ($request->recover_mail) {
            $user->update(['recover_mail' => $request->recover_mail]);
        }

        if ($request->phone) {
            $user->update(['phone' => $request->phone]);
        }

        if ($request->address) {
            $user->update(['address' => $request->address]);
        }

        if ($request->enable_2fa) {
            $user->update(['enable_2fa' => $request->enable_2fa]);
        }

        if ($request->device_token) {
            $user->update(['device_token' => $request->device_token]);
        }

        if ($request->device_type) {
            $user->update(['device_type' => $request->device_type]);
        }

        if ($request->pin) {
            $user->update(['pin' => encrypt($request->pin)]);
        }

        if ($request->onboarding_stage) {
            $user->update(['onboarding_stage' => $request->onboarding_stage]);
        }

        if ($request->username) {
            $user->update(['username' => $request->username]);
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Profile updated successfully',
                'data' => null
            ],
            201
        );
    }

    public function contact()
    {
        $record = ContactList::where('user_id', auth()->user()->id)->get();

        $data = [];
        foreach ($record as $key => $dt) {
            $data[$key] = [
                'id' => encrypt($dt->id),
                'contact_id_encrypt' => encrypt($dt->userLink->id),
                'contact_id' => $dt->userLink->id,
                'contact_name' => $dt->userLink->name,
                'contact_email' => $dt->userLink->email,
                'contact_phone' => $dt->userLink->phone,
                'contact_status' => $dt->userLink->status,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record list',
                'data' => $data
            ],
            201
        );
    }

    public function contactAdd($id)
    {
        $idUser = decrypt($id);

        ContactList::firstOrCreate([
            'user_id' => auth()->user()->id,
            'user_link' => $idUser
        ], [
            'status' => 'active'
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Contact successfully saved',
                'data' => null
            ],
            201
        );
    }

    public function contactRemove($id)
    {
        $idUser = decrypt($id);
        ContactList::find($idUser)->delete();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Contact successfully removed',
                'data' => null
            ],
            201
        );
    }

    public function chatCallLog()
    {
        $datas = ChatCallLog::where('send_user_id', auth()->user()->id)->orWhere('recieve_user_id', auth()->user()->id)->orderBy('created_at', 'ASC')->get();

        $data = [];
        foreach ($datas as $dt) {
            $data[] = [
                'send_user_id' => encrypt($dt->send_user_id),
                'send_user_name' => $dt->userSender->name,
                'send_user_phone' => $dt->userSender->phone,
                'send_user_email' => $dt->userSender->email,
                'recieve_user_id' => encrypt($dt->recieve_user_id),
                'recieve_user_name' => $dt->userReciever->name,
                'recieve_user_phone' => $dt->userReciever->phone,
                'recieve_user_email' => $dt->userReciever->email,
                'call_st' => $dt->call_st,
                'created_at' => $dt->created_at,
                'mss_id' => $dt->mss_id,
                'call_duration' => $dt->call_duration,
                'call_state' => $dt->call_state,
                'chatbtw' => $dt->chatbtw,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function chatHistory()
    {
        $record = ChatLastLog::where('user_id', auth()->user()->id)->join('users', 'users.id', '=', 'chat_last_logs.user_to')->orderBy('users.name', 'ASC')->get();

        $data = [];
        foreach ($record as $key => $dt) {
            $data[$key] = [
                'id' => encrypt($dt->id),
                'chat_id' => $dt->group_to,
                'chat_user_to_id' => $dt->userTo->id,
                'chat_user_to_name' => $dt->userTo->name,
                'is_file' => $dt->is_file,
                'last_message' => $dt->last_message,
                'chat_user_type' => $dt->user_group,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function chatMessages($chat_user_id, $chat_user_type)
    {
        $this->current_chat_user = $chat_user_id;

        $thisuserLastLog = $chat_user_type == 'user' ? ChatLastLog::where('user_id', auth()->user()->id)->where('user_to', $chat_user_id)->first() : ChatLastLog::where('group_to', $chat_user_id)->first();

        $userLastLog = $thisuserLastLog ? $thisuserLastLog->group_to : null;

        $record = ChatMessage::where(function ($query) {
            $query->where('user_id', $this->current_chat_user)
                ->orWhere('group_to', $this->current_chat_user)
                ->orWhere('user_to', $this->current_chat_user);
        })->where(function ($query) {
            $query->where('user_id', auth()->user()->id)
                ->orWhere('group_to', auth()->user()->id)
                ->orWhere('user_to', auth()->user()->id);
        })->orderBy('created_at', 'ASC')->get();

        $data = [];
        foreach ($record as $key => $dt) {
            $data[$key] = [
                'id' => $dt->id,
                'id_en' => encrypt($dt->id),
                'is_my_chat' => $dt->user_id == auth()->user()->id ? 'yes' : 'no',
                'user_id' => $dt->user_id,
                'user_to' => $dt->user_to,
                'user_to_name' => $dt->userTo->name,
                'group_to' => $dt->group_to,
                'chat_user_type' => $dt->user_group,
                'is_file' => $dt->is_file,
                'file_type' => $dt->file_type,
                'is_read' => $dt->is_read,
                'is_important' => $dt->is_important,
                'is_forward' => $dt->is_forward,
                'is_star' => $dt->is_star,
                'view_once' => $dt->view_once,
                'mss_type' => $dt->mss_type,
                'expire_time' => $dt->expire_time,
                'message' => decrypt($dt->message),
                'deleted_at' => $dt->deleted_at,
                'created_at' => $dt->created_at,
                'updated_at' => $dt->updated_at,
            ];
        }

        $chat_meta = [
            'chat_user_id' => $chat_user_id,
            'chat_user_id_en' => encrypt($chat_user_id),
            'chat_id' => $userLastLog,
            'chat_user_type' => $chat_user_type
        ];

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'chat_meta' => $chat_meta,
                'data' => $data
            ],
            201
        );
    }

    public function getmeeting()
    {
        $datas = Meeting::where('user_id', auth()->user()->id)->get();

        $data = [];
        foreach ($datas as $dt) {
            $data[] = [
                'id' => encrypt($dt->id),
                'meeting_link' => $dt->meeting_link,
                'meeting_id' => $dt->meeting_id,
                'subject' => $dt->subject,
                'title' => $dt->title,
                'agenda' => $dt->agenda,
                'startdatetime' => $dt->startdatetime,
                'duration' => $dt->duration,
                'number_join' => $dt->number_join,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function getmeetingDetail($id)
    {
        $datas = Meeting::find(decrypt($id));

        $data = [
            'id' => encrypt($datas->id),
            'meeting_link' => $datas->meeting_link,
            'meeting_id' => $datas->meeting_id,
            'subject' => $datas->subject,
            'title' => $datas->title,
            'agenda' => $datas->agenda,
            'startdatetime' => $datas->startdatetime,
            'duration' => $datas->duration,
            'number_join' => $datas->number_join,
        ];

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function getmeetingid($id, $type)
    {
        $datas = Meeting::where('group_user_id', decrypt($id))->where('group_user', $type)->get();

        $data = [];
        foreach ($datas as $dt) {
            $data[] = [
                'id' => encrypt($dt->id),
                'group_user_id' => encrypt($dt->group_user_id),
                'group_user' => $dt->group_user,
                'meeting_link' => $dt->meeting_link,
                'meeting_id' => $dt->meeting_id,
                'subject' => $dt->subject,
                'title' => $dt->title,
                'agenda' => $dt->agenda,
                'startdatetime' => $dt->startdatetime,
                'duration' => $dt->duration,
                'number_join' => $dt->number_join,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function meetingInvitationlist()
    {
        $datas = MeetingLog::where('user_id', auth()->user()->id)->where('user_type', 'participant')->get();

        $data = [];
        foreach ($datas as $dt) {
            $data[] = [
                'id' => encrypt($dt->meeting->id),
                'meeting_id' => encrypt($dt->meeting->id),
                'meeting_link' => $dt->meeting->meeting_link,
                'meeting_id' => $dt->meeting->meeting_id,
                'subject' => $dt->meeting->subject,
                'title' => $dt->meeting->title,
                'agenda' => $dt->meeting->agenda,
                'startdatetime' => $dt->meeting->startdatetime,
                'duration' => $dt->meeting->duration,
                'number_join' => $dt->meeting->number_join,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function folderFile(Request $request)
    {
        $data = FolderFile::updateOrCreate([
            'user_id' => auth()->user()->id,
            'folder_id' => decrypt($request->folder_id),
            'file_id' => decrypt($request->file_id),
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function folderCreate(Request $request)
    {
        $data = Folders::updateOrCreate([
            'user_id' => auth()->user()->id,
            'name' => $request->name,
            'rel' => $request->rel ? decrypt($request->rel) : null,
        ], [
            'description' => $request->description,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function folderUpdate(Request $request)
    {
        $data = Folders::find(decrypt($request->id));
        $data->update([
            'name' => $request->name,
            'rel' => $request->rel ? decrypt($request->rel) : $data->rel,
            'description' => $request->description,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function folderdelete($id)
    {
        $data = Folders::find(decrypt($id))->delete();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record delete',
                'data' => $data
            ],
            201
        );
    }

    public function folderget()
    {
        $folder = Folders::where('user_id', auth()->user()->id)->whereNull('rel')->get();
        $data = [];

        foreach ($folder as $fl) {
            $data[] = [
                'id' => encrypt($fl->id),
                'name' => $fl->name,
                'description' => $fl->description,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function foldergetId($id)
    {
        $folder = Folders::where('user_id', auth()->user()->id)->where('rel', decrypt($id))->get();
        $data = [];

        foreach ($folder as $fl) {
            $data[] = [
                'id' => encrypt($fl->id),
                'name' => $fl->name,
                'description' => $fl->description,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function messagesTyping(Request $request)
    {
        broadcast(new PrivateMessageSent(auth()->user()->id, $request->current_chat_user, [
            'state' => $request->typing,
            'user' => encrypt($request->current_chat_user),
            'message' => '',
            'name' => '',
            'data' => ''
        ]))->toOthers();

        return true;
    }

    public function meetingCreate(Request $request)
    {
        $data = Meeting::create([
            'user_id' => auth()->user()->id,
            'meeting_link' => $request->meeting_link,
            'meeting_id' => $request->meeting_id,
            'subject' => $request->subject,
            'title' => $request->title,
            'agenda' => $request->agenda,
            'startdatetime' => $request->startdatetime,
        ]);

        if ($request->group_user == "users" && $request->group_user_id) {
            $this->ChatService->meetingInvitation(encrypt($data->id), $request->group_user_id);
        }

        if ($request->group_user == "group" && $request->group_user_id) {
            $this->ChatService->meetingInvitationGroup(encrypt($data->id), $request->group_user_id);
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'id' => encrypt($data->id),
                    'meeting_link' => $data->meeting_link . '/' . encrypt($data->id),
                    'meeting_id' => $data->meeting_id,
                    'subject' => $data->subject,
                    'title' => $data->title,
                    'agenda' => $data->agenda,
                    'startdatetime' => $data->startdatetime,
                ]
            ],
            201
        );
    }

    public function meetingUpdate(Request $request)
    {
        $data = Meeting::find(decrypt($request->id));

        if ($request->meeting_link) {
            $data->update(['meeting_link' => $request->meeting_link]);
        }

        if ($request->meeting_id) {
            $data->update(['meeting_id' => $request->meeting_id]);
        }

        if ($request->subject) {
            $data->update(['subject' => $request->subject]);
        }

        if ($request->title) {
            $data->update(['title' => $request->title]);
        }

        if ($request->agenda) {
            $data->update(['agenda' => $request->agenda]);
        }

        if ($request->startdatetime) {
            $data->update(['startdatetime' => $request->startdatetime]);
        }

        if ($request->duration) {
            $data->update(['duration' => $request->duration]);
        }

        if ($request->number_join) {
            $data->update(['number_join' => $request->number_join]);
        }

        if ($request->status) {
            $data->update(['status' => $request->status]);
        }

        $data = Meeting::find(decrypt($request->id));

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function meetingInvitation(Request $request)
    {
        $data = $this->ChatService->meetingInvitation($request->meetings_id, $request->users);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function meetingInvitationJoin($id)
    {
        $meet = Meeting::find(decrypt($id));
        $user = MeetingLog::where('meetings_id', $meet->id)->where('user_id', auth()->user()->id)->first();
        if ($user->join_status == 'invite') {
            if ($meet->status == 'start') {
                $meet->update(['number_join' => $meet->number_join + 1]);
                $user->update(['join_status' => 'joined']);
            }
            if ($meet->status == 'end') {
                $user->update(['join_status' => 'close']);
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

    public function meetingInvitationGroup(Request $request)
    {
        $data = $this->ChatService->meetingInvitationGroup($request->meetings_id, $request->group_id);
        return $data;
    }

    public function sendMessageCall(Request $request)
    {
        // return [auth()->user()->id, decrypt($request->recieve_user_id)];
        $calllog = ChatCallLog::where('mss_id', decrypt($request->mss_id))->first();

        if($calllog){
            if ($request->call_duration) {
                $calllog->update(['call_duration' => $request->call_duration]);
            }

            if ($request->call_state) {
                $calllog->update(['call_state' => $request->call_state]);
            }

            broadcast(new PrivateMessageSent(auth()->user()->id, $calllog->userReciever->id, [
                'state' => 'callUpdate',
                'user' => encrypt($calllog->userReciever->id),
                'sender' => [
                    'id' => $calllog->userSender->id,
                    'id_en' => encrypt($calllog->userSender->id),
                    'name' => $calllog->userSender->name,
                    'phone' => $calllog->userSender->phone,
                    'email' => $calllog->userSender->email,
                ],
                'receiver' => [
                    'id' => $calllog->userReciever->id,
                    'id_en' => encrypt($calllog->userReciever->id),
                    'name' => $calllog->userReciever->name,
                    'phone' => $calllog->userReciever->phone,
                    'email' => $calllog->userReciever->email,
                ],
                'call' => [
                    "chat_id" => encrypt($calllog->chatMess->id),
                    "call_duration" => $request->call_duration,
                    "call_state" => $request->call_state
                ]
            ]))->toOthers();

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'call log updated',
                    'data' => $calllog,
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'No log found',
                'data' => null
            ],
            401
        );

    }

    public function sendMessage(Request $request)
    {
        if ($request->message) {

            $message = "";
            if ($request->is_file == "yes") {
                $file = $this->FileUploadService->submitFile($request);

                if ($file['status'] == false) {
                    return response()->json(
                        [
                            'status' => '400',
                            'message' => $file['message'],
                            'data' => null
                        ],
                        401
                    );
                }
                $message = $file['data']['file'];
            } else {
                $message = $request->message;
            }

            $ret = $this->ChatService->submitChat(
                $request->current_chat_user_type,
                $request->current_chat_user,
                $request->chat_id,
                $message,
                $request->is_file,
                $request->mss_type
            );
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Message Sent',
                'data' => $ret
            ],
            201
        );
    }

    public function setting(Request $request)
    {
        if ($request->hide_message) {
            ChatSettings::updateOrCreate(['user_id' => auth()->user()->id], [
                'hide_message' => $request->hide_message,
            ]);
        }
        if ($request->hide_message_style) {
            ChatSettings::updateOrCreate(['user_id' => auth()->user()->id], [
                'hide_message_style' => $request->hide_message_style,
            ]);
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Setting updated',
                'data' => null
            ],
            201
        );
    }

    public function groupMember($id)
    {
        $idUser = decrypt($id);
        $record = CompanyGroupUser::where('group_id', $idUser)->where('user_id', '!=', auth()->user()->id)->where('status', 'joined')->get();
        $group = CompanyGroup::find($idUser);

        $data = [];
        foreach ($record as $key => $dt) {
            $data[$key] = [
                'id' => $dt->id,
                'join_date' => $dt->join_date,
                'hide_member_detail' => $dt->hide,
                'member_id_encrpt' => encrypt($dt->user_id),
                'member_id' => $dt->user_id,
                'member_name' => $dt->user->name,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'group_meta' => $group,
                'data' => $data
            ],
            201
        );
    }

    public function notification()
    {
        $data = Notification::where('status', 'active')->where('company_id', auth()->user()->company_id)->get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function meetingTokenGen()
    {
        $VIDEOSDK_API_KEY = "533374080782aa04f1f129fa3837589694170ccac5a34a7281b8f058c8f0445b";
        $VIDEOSDK_SECRET_KEY = "83f2a350-a7c2-42cf-8447-18dc0b7cbb48";

        $issuedAt = new \DateTimeImmutable();
        $expire = $issuedAt->modify('+2 hours')->getTimestamp();

        $payload = [
            'apikey' => $VIDEOSDK_API_KEY,
            'permissions' => ['allow_join', 'allow_mod'],
            'version' => 2,
            'roomId' => '2kyv-gzay-64pg',
            'participantId' => 'lxvdplwt',
            'roles' => ['crawler'],
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expire,
        ];

        $jwt = JWT::encode($payload, $VIDEOSDK_SECRET_KEY, 'HS256');

        return response()->json(['token' => $jwt]);
    }
}
