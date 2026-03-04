<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Files;
use App\Mail\FileShare;
use App\Models\Meeting;
use App\Mail\Invitation;
use App\Models\EventForm;
use App\Mail\EventSentMail;
use App\Models\CompanyUser;
use App\Models\FilesShares;
use App\Models\CompanyGroup;
use App\Models\FileShareLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\CompanyGroupUser;
use App\Models\EventRegistration;
use App\Mail\EventRegistrationMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\FileEncryptorService;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\EventRegistrationsAttendances;
use App\Models\Certificate;
use App\Models\Souvenir;
use App\Mail\CertificateMail;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check() && auth()->user()->role !== 'admin') {
                return response()->json([
                    'status' => '401',
                    'message' => 'Unauthorized access. Admin role required.'
                ], 401);
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $users = User::where('company_id', auth()->user()->CompanyUser->id)->where('role', 'user')->orderBy('name', 'ASC')->get();
        $groups = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->get();
        $file = Files::where('company_id', auth()->user()->CompanyUser->id)->get();
        $fileArchive = Files::where('company_id', auth()->user()->CompanyUser->id)->where('status', "archive")->get();
        $fileActive = Files::where('company_id', auth()->user()->CompanyUser->id)->where('status', "active")->get();
        $events = EventForm::where('user_id', auth()->user()->id)->get();
        $eventIds = $events->pluck('id');

        $certificateCount = Certificate::whereIn('form_id', $eventIds)->count();
        $certificateActiveCount = Certificate::whereIn('form_id', $eventIds)->where('status', 'active')->count();

        $souvenirCount = Souvenir::whereIn('form_id', $eventIds)->count();
        $souvenirActiveCount = Souvenir::whereIn('form_id', $eventIds)->where('status', 'active')->count();

        $file_size = $file->sum('fileSize_num');
        if ($file_size >= 1073741824) {
            $file_size = number_format($file_size / 1073741824, 2) . ' GB';
        } elseif ($file_size >= 1048576) {
            $file_size = number_format($file_size / 1048576, 2) . ' MB';
        } elseif ($file_size >= 1024) {
            $file_size = number_format($file_size / 1024, 2) . ' KB';
        } else {
            $file_size = $file_size . ' byt';
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'users' => $users,
                    'usersCount' => $users->count(),
                    'groupCount' => $groups->count(),
                    'fileCount' => $file->count(),
                    'fileSizeSum' => $file_size,
                    'fileArchiveCount' => $fileArchive->count(),
                    'fileActiveCount' => $fileActive->count(),
                    'eventCount' => $events->count(),
                    'certificateCount' => $certificateCount,
                    'certificateActiveCount' => $certificateActiveCount,
                    'souvenirCount' => $souvenirCount,
                    'souvenirActiveCount' => $souvenirActiveCount,
                ]
            ],
            201
        );
    }

    public function account()
    {
        $users = User::where('company_id', auth()->user()->CompanyUser->id)->where('role', 'user')->orderBy('name', 'ASC')->get();
        $data = [];
        foreach ($users as $usr) {
            $data[] = [
                'id' => encrypt($usr->id),
                'en_id' => encryptHelper($usr->id),

                'name' => $usr->name,
                'username' => $usr->username,
                'email' => $usr->email,
                'email_verified_at' => $usr->email_verified_at,

                'phone' => $usr->phone,
                'address' => $usr->address,
                'country' => $usr->country,
                'dob' => $usr->dob,
                'gender' => $usr->gender,

                'role' => $usr->role,
                'app_role' => $usr->app_role,
                'company_id' => $usr->company_id,

                'status' => $usr->status,
                'status_ndpc' => $usr->statusNdpc,
                'status_app' => $usr->statusApp,
                'access' => $usr->access,

                'is_online' => $usr->is_online,
                'device' => $usr->device,
                'device_type' => $usr->device_type,
                'device_token' => $usr->device_token,

                'enable_2fa' => (bool) $usr->enable_2fa,
                'signal_blocking' => $usr->signal_blocking,
                'remote_management' => $usr->remote_management,
                'encrypted_storage' => $usr->encrypted_storage,
                'self_wipe' => $usr->self_wipe,

                'onboarding_stage' => $usr->onboarding_stage,

                'developer_display_name' => $usr->developer_display_name,
                'website' => $usr->website,
                'rc_number' => $usr->rc_number,
                'tin' => $usr->tin,

                'avatar' => $usr->avatar
                    ? url('/avatar/' . $usr->avatar)
                    : null,

                'selfie' => $usr->selfie
                    ? url('/' . $usr->selfie)
                    : null,

                'rc_doc' => $usr->rc_doc
                    ? url('/' . $usr->rc_doc)
                    : null,

                'tin_doc' => $usr->tin_doc
                    ? url('/' . $usr->tin_doc)
                    : null,

                'id_card_front' => $usr->id_card_front
                    ? url('/' . $usr->id_card_front)
                    : null,

                'id_card_back' => $usr->id_card_back
                    ? url('/' . $usr->id_card_back)
                    : null,

                'comment_app' => $usr->commentApp,

                'created_at' => $usr->created_at,
                'updated_at' => $usr->updated_at,
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

    public function notification()
    {
        $notify = Notification::where('company_id', auth()->user()->company_id)->get();
        $data = [];
        foreach ($notify as $nt) {
            $data[] = [
                'id' => encrypt($nt->id),
                'label' => $nt->label,
                'short_message' => $nt->short_message,
                'body_message' => $nt->body_message,
                'expire' => $nt->expire,
                'status' => $nt->status,
                'icon' => url("/icon/" . $nt->icon),
                'created_at' => $nt->created_at,
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

    public function notificationCreate(Request $request)
    {
        $file_name = null;
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $file_name = time() . "icon." . $file->getClientOriginalExtension();
            $file->move(public_path('icon'), $file_name);
        }

        Notification::create([
            'label' => $request->label,
            'short_message' => $request->short_message,
            'body_message' => $request->body_message,
            'expire' => $request->expire,
            'status' => $request->status,
            'icon' => $file_name,
            'company_id' => auth()->user()->company_id,
        ]);

        // Mail::to($request->email)->send(new Invitation($request->name, $request->email, $encrypt));

        return response()->json(
            [
                'status' => '200',
                'message' => 'Notification successfully added',
                'data' => []
            ],
            201
        );
    }

    public function notificationDelete($id)
    {
        $idUser = decrypt($id);
        Notification::find($idUser)->delete();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Notification successfully removed',
                'data' => []
            ],
            201
        );
    }

    public function notificationEdit(Request $request)
    {
        $idUser = decrypt($request->id);
        $file_name = null;
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $file_name = time() . "icon." . $file->getClientOriginalExtension();
            $file->move(public_path('icon'), $file_name);
            $old_file = Notification::find($idUser)->icon;
            if ($old_file) {
                unlink(public_path('icon') . '/' . $old_file);
            }
        }
        Notification::find($idUser)->update([
            'label' => $request->label,
            'short_message' => $request->short_message,
            'body_message' => $request->body_message,
            'expire' => $request->expire,
            'status' => $request->status,
            'icon' => $file_name,
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Notification successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function accountStatus($id, $status)
    {
        $idUser = decrypt($id);

        $user = User::find($idUser);
        $user->update(['status' => $status]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'User status successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function accountCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
        ]);

        $error = [];
        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $mess) {
                $error[] = $mess;
            }
            return response()->json(
                [
                    'status' => '400',
                    'message' => $error,
                    'data' => []
                ],
                400
            );
        }

        $userCount = User::where('company_id', auth()->user()->CompanyUser->id)->count();

        $user = User::find(auth()->user()->id);

        if ($user->plan_id === null) {
            return response()->json(
                [
                    'status' => '400',
                    'message' => 'You do not have an active plan. Please subscribe',
                    'data' => []
                ],
                400
            );
        }

        if ($userCount >= $user->plan->no_user) {
            return response()->json(
                [
                    'status' => '400',
                    'message' => 'You have reach you limit',
                    'data' => []
                ],
                400
            );
        }

        $otp = rand(1000, 9999);

        $usr = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'otp' => $otp,
            'company_id' => auth()->user()->CompanyUser->id,
            'password' => Hash::make(uniqid()),
            'access_token' => uniqid()
        ]);

        $encrypt = encrypt($request->email);

        Mail::to($request->email)->send(new Invitation($request->name, $request->email, $encrypt, $otp));

        return response()->json(
            [
                'status' => '200',
                'message' => 'User successfully added',
                'data' => []
            ],
            201
        );
    }

    public function meeting()
    {
        $meet = Meeting::where('user_id', auth()->user()->id)->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $meet
            ],
            201
        );
    }

    public function form($status = null)
    {
        $query = EventForm::where('user_id', auth()->user()->id);

        if ($status !== null) {
            $query = $query->where('status', "active");
            if ($status === 'active') {
                // Current date is between started_at and ended_at
                $query->where('started_at', '<=', now())
                    ->where('ended_at', '>=', now());
            } elseif ($status === 'upcoming') {
                // started_at is greater than current time
                $query->where('started_at', '>', now());
            } elseif ($status === 'event') {
                // Current date is greater than ended_at
                $query->where('ended_at', '<', now());
            }
        }

        $event = $query->get();
        $data = [];
        foreach ($event as $ev) {
            $data[] = [
                'id' => encrypt($ev->id),
                'name' => $ev->name,
                'message' => $ev->message,
                'group_id' => $ev->group->name,
                'meeting_id' => $ev->meeting->subject,
                'signup' => $ev->signup,
                'attendance' => $ev->attendance,
                'status' => $ev->status,
                'started_at' => $ev->started_at,
                'ended_at' => $ev->ended_at,
                'created_at' => $ev->created_at,
                'location' => $ev->location,
                'latitude' => $ev->latitude,
                'longitude' => $ev->longitude
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

    public function formApplication($id)
    {
        $event = EventRegistration::where('form_id', decrypt($id))->get();
        $data = [];
        foreach ($event as $ev) {
            $data[] = [
                'id' => encrypt($ev->id),
                'user' => [
                    'id' => encrypt($ev->user->id),
                    'name' => $ev->user->name,
                    'email' => $ev->user->email,
                    'phone' => $ev->user->phone,
                ],
                'form_id' => encrypt($ev->form_id),
                'name' => $ev->name,
                'email' => $ev->email,
                'phone' => $ev->phone,
                'data' => $ev->data,
                'created_at' => $ev->created_at,
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

    public function formAttendance($id)
    {
        $event = EventRegistrationsAttendances::where('form_id', decrypt($id))->get();
        $data = [];
        foreach ($event as $ev) {
            $data[] = [
                'id' => encrypt($ev->id),
                'user' => [
                    'id' => encrypt($ev->user->id),
                    'name' => $ev->user->name,
                    'email' => $ev->user->email,
                    'phone' => $ev->user->phone,
                ],
                'form_id' => encrypt($ev->form_id),
                'comment' => $ev->comment,
                'photo' => $ev->photo,
                'created_at' => $ev->created_at,
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

    public function formCreate(Request $request)
    {
        EventForm::create([
            'name' => $request->name,
            'message' => $request->message,
            'group_id' => $request->group_id ? decrypt($request->group_id) : null,
            'meeting_id' => $request->meeting_id ? decryptHelper($request->meeting_id) : null,
            'signup' => $request->signup ?? "disabled",
            'attendance' => $request->attendance ?? "disabled",
            'status' => $request->status ?? "active",
            'user_id' => auth()->user()->id,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Event form successfully created',
                'data' => []
            ],
            201
        );
    }

    public function formUpdate(Request $request)
    {
        $eventForm = EventForm::find(decrypt($request->id));
        $eventForm->update([
            'name' => $request->name,
            'message' => $request->message,
            'group_id' => $request->group_id ? decrypt($request->group_id) : $eventForm->group_id,
            'meeting_id' => $request->meeting_id ? decryptHelper($request->meeting_id) : $eventForm->meeting_id,
            'signup' => $request->signup,
            'attendance' => $request->attendance,
            'status' => $request->status,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Event form successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function formMail(Request $request)
    {
        $id = decrypt($request->id);
        $user = json_decode($request->users, true);
        $form = EventForm::findOrFail($id);
        $meet = Meeting::find($form->meeting_id);
        if (!empty($user)) {
            foreach ($user as $dt) {
                $usr = EventRegistration::find($dt);
                $qrData = url("/admin/form/attendance/" . encrypt($form->id) . "/" . encrypt($usr->user->id));
                $fileName = null;
                if ($form->attendance == "enabled") {
                    $path = public_path('qr');
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $fileName = time() . '_qr.png';
                    QrCode::format('png')
                        ->size(200)
                        ->margin(1)
                        ->generate($qrData, $path . '/' . $fileName);
                    $fullPath = $path . '/' . $fileName;
                    // $qrCode = base64_encode(file_get_contents($fullPath));
                }
                Mail::to($usr->user->email)->send(new EventRegistrationMail($form, $usr->user, $meet, $fileName, $request->subject, htmlentities($request->message)));
            }
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Event form successfully sent',
                'data' => []
            ],
            201
        );
    }

    public function attendanceUser($id, $userId)
    {
        $data = EventRegistrationsAttendances::updateOrCreate([
            'form_id' => decrypt($id),
            'user_id' => decrypt($userId),
        ], [
            'comment' => "Checked by " . auth()->user()->name,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Attendance recorded successfully',
                'data' => []
            ],
            201
        );
    }

    public function group()
    {
        $groups = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->get();
        $data = [];
        foreach ($groups as $grp) {
            $data[] = [
                'id' => encrypt($grp->id),
                'name' => $grp->name,
                'decription' => $grp->decription,
                'avatar' => $grp->avatar
                    ? url('/group/' . $grp->avatar)
                    : null,
                'company_id' => $grp->company_id,
                'member_count' => CompanyGroupUser::where('group_id', $grp->id)->count(),
                'created_at' => $grp->created_at,
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

    public function groupCreate(Request $request)
    {
        $groupCount = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->count();

        $user = User::find(auth()->user()->id);

        if ($user->plan_id === null) {
            return response()->json(
                [
                    'status' => '400',
                    'message' => 'You do not have an active plan. Please subscribe',
                    'data' => []
                ],
                400
            );
        }

        if ($groupCount >= $user->plan->no_group) {
            return response()->json(
                [
                    'status' => '400',
                    'message' => 'You have reach you limit',
                    'data' => []
                ],
                400
            );
        }

        $file_name = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $file_name = time() . "avatar." . $file->getClientOriginalExtension();
            $file->move(public_path('group'), $file_name);
        }

        CompanyGroup::create([
            'name' => $request->name,
            'decription' => $request->decription,
            'avatar' => $file_name,
            'company_id' => auth()->user()->CompanyUser->id,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Group successfully created',
                'data' => []
            ],
            201
        );
    }

    public function groupUpdate(Request $request)
    {
        $id = decrypt($request->id);
        $file_name = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $file_name = time() . "avatar." . $file->getClientOriginalExtension();
            $file->move(public_path('group'), $file_name);
            $old_file = CompanyGroup::find($id)->avatar;
            if ($old_file) {
                unlink(public_path('group') . '/' . $old_file);
            }
            CompanyGroup::find($id)->update(['avatar' => $file_name]);
        }
        CompanyGroup::find($id)->update([
            'name' => $request->name,
            'decription' => $request->decription,
        ]);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Group successfully updated',
                'data' => []
            ],
            201
        );
    }

    public function member($id)
    {
        $idUser = decrypt($id);
        $member = CompanyGroupUser::where('group_id', $idUser)->get();
        $data = [];
        foreach ($member as $mem) {
            $data[] = [
                'id' => encrypt($mem->id),
                'user' => [
                    'id' => encrypt($mem->user->id),
                    'en_id' => encryptHelper($mem->user->id),

                    'name' => $mem->user->name,
                    'username' => $mem->user->username,
                    'email' => $mem->user->email,
                    'email_verified_at' => $mem->user->email_verified_at,

                    'phone' => $mem->user->phone,
                    'address' => $mem->user->address,
                    'country' => $mem->user->country,
                    'dob' => $mem->user->dob,
                    'gender' => $mem->user->gender,

                    'role' => $mem->user->role,
                    'app_role' => $mem->user->app_role,
                    'company_id' => $mem->user->company_id,

                    'status' => $mem->user->status,
                    'status_ndpc' => $mem->user->statusNdpc,
                    'status_app' => $mem->user->statusApp,
                    'access' => $mem->user->access,

                    'is_online' => $mem->user->is_online,
                    'device' => $mem->user->device,
                    'device_type' => $mem->user->device_type,
                    'device_token' => $mem->user->device_token,

                    'enable_2fa' => (bool) $mem->user->enable_2fa,
                    'signal_blocking' => $mem->user->signal_blocking,
                    'remote_management' => $mem->user->remote_management,
                    'encrypted_storage' => $mem->user->encrypted_storage,
                    'self_wipe' => $mem->user->self_wipe,

                    'onboarding_stage' => $mem->user->onboarding_stage,

                    'developer_display_name' => $mem->user->developer_display_name,
                    'website' => $mem->user->website,
                    'rc_number' => $mem->user->rc_number,
                    'tin' => $mem->user->tin,

                    'avatar' => $mem->user->avatar
                        ? url('/avatar/' . $mem->user->avatar)
                        : null,

                    'selfie' => $mem->user->selfie
                        ? url('/' . $mem->user->selfie)
                        : null,

                    'rc_doc' => $mem->user->rc_doc
                        ? url('/' . $mem->user->rc_doc)
                        : null,

                    'tin_doc' => $mem->user->tin_doc
                        ? url('/' . $mem->user->tin_doc)
                        : null,

                    'id_card_front' => $mem->user->id_card_front
                        ? url('/' . $mem->user->id_card_front)
                        : null,

                    'id_card_back' => $mem->user->id_card_back
                        ? url('/' . $mem->user->id_card_back)
                        : null,

                    'comment_app' => $mem->user->commentApp,

                    'created_at' => $mem->user->created_at,
                    'updated_at' => $mem->user->updated_at,
                ],
                'group_id' => $mem->group_id,
                'company_id' => $mem->company_id,
                'created_at' => $mem->created_at,
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

    public function memberRemove(Request $request)
    {
        $id = decrypt($request->id);
        // $user = json_decode($request->users, true);
        $user = $request->users;
        if (!empty($user)) {
            foreach ($user as $dt) {
                CompanyGroupUser::where('group_id', $id)->where('user_id', decrypt($dt))->first()->delete();
                // Mail::to($request->email)->send(new GroupInvitation($request->name, $request->email, $encrypt, $otp));
            }

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'Group member removed successfully',
                    'data' => []
                ],
                201
            );
        }
        return response()->json(
            [
                'status' => '200',
                'message' => 'Group member not found',
                'data' => []
            ],
            201
        );
    }

    public function memberAdd($id)
    {
        $users = User::where('company_id', auth()->user()->CompanyUser->id)->where('role', 'user')->orderBy('name', 'ASC')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $users
            ],
            201
        );
    }

    public function memberGroupAdd(Request $request)
    {
        $id = decrypt($request->id);
        // $user = json_decode($request->users, true);
        $user = $request->users;
        if (!empty($user)) {
            foreach ($user as $dt) {
                CompanyGroupUser::firstOrCreate([
                    'user_id' => decrypt($dt),
                    'group_id' => $id
                ], [
                    'company_id' => auth()->user()->CompanyUser->id
                ]);
                // Mail::to($request->email)->send(new GroupInvitation($request->name, $request->email, $encrypt, $otp));
            }

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'Group member added successfully',
                    'data' => []
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'Please ensure to select a user',
                'data' => []
            ],
            400
        );
    }

    public function accountBlock(Request $request)
    {
        $user = json_decode($request->users, true);
        if (!empty($user)) {
            foreach ($user as $dt) {
                User::find($dt)->update([
                    'status' => 'block'
                ]);
            }

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'User account deactivated successfully',
                    'data' => []
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'Please ensure to select a user',
                'data' => []
            ],
            400
        );
    }

    public function file()
    {
        $file = Files::where('company_id', auth()->user()->CompanyUser->id)->where('user_type', 'admin')->orderBy('id', 'DESC')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $file
            ],
            201
        );
    }

    public function fileUser()
    {
        $file = Files::where('company_id', auth()->user()->CompanyUser->id)->where('user_type', 'user')->orderBy('id', 'DESC')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $file
            ],
            201
        );
    }

    public function fileRequest()
    {
        $file = FilesShares::where('company_id', auth()->user()->CompanyUser->id)->where('status', 'block')->orderBy('id', 'DESC')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $file
            ],
            201
        );
    }

    public function fileView($id)
    {
        $file = Files::find(decrypt($id));
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $file
            ],
            201
        );
    }

    public function fileDownload($id)
    {
        $file = Files::find(decrypt($id));
        FileShareLog::create(['user_id' => auth()->user()->id, 'file_id' => $file->id, 'company_id' => auth()->user()->company_id]);

        $pathToEncrypted = storage_path(decrypt($file->file));
        $fileExtension = $file->file_ext;
        $pathToDecryptedWatermarked = storage_path('app/decrypted_' . uniqid() . '.' . $fileExtension);
        File::put($pathToDecryptedWatermarked, "");

        $encryptor = new FileEncryptorService();
        $encryptor->decryptAndWatermark(
            $pathToEncrypted,
            $pathToDecryptedWatermarked,
            $fileExtension,
            [
                'watermark_text' => 'Downloaded by: ' . auth()->user()->name,
                // 'watermark_image' => public_path('logo.png')
                "y" => 60,
                "x" => 40
            ]
        );

        return response()->download($pathToDecryptedWatermarked)->deleteFileAfterSend(true);
    }

    public function fileUpload(Request $request)
    {
        $file = $request->file('file');
        $file_ext = $file->getClientOriginalExtension();

        // return dd($file_ext != "pdf" || $file_ext != "PDF");

        // if ($file_ext != "pdf") {
        //     return redirect()->back()->with('error', "Ensure the file is PDF");
        // }

        $file_size = $fileSize = $file->getSize();
        $file_time = time();
        $file_name = $file_time . $file->hashName() . '.enc';

        $originalPath = $file->storeAs('secure/uploads', $file_time . $file->getClientOriginalName());
        $encryptedPath = $file->storeAs('secure/encrypted',  $file_name);

        $encryptor = new FileEncryptorService();
        $encryptor->processAndEncrypt(
            storage_path('app/' . $originalPath),
            storage_path('app/' . $encryptedPath),
            [
                'watermark_text' => 'Uploaded by ' . auth()->user()->name,
                // 'watermark_image' => public_path('logo.png')
            ]
        );

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
            'file' => encrypt("app/secure/encrypted/" . $file_name),
            'file_size' => $file_size,
            'file_ext' => $file_ext,
            'fileSize_num' => $fileSize,
            'company_id' => auth()->user()->CompanyUser->id,
            'uploaded_by' => auth()->user()->id,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'File securely uploaded',
                'data' => []
            ],
            201
        );
    }

    public function fileShareGroup($id)
    {
        $groups = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $groups
            ],
            201
        );
    }

    public function fileShareGroupAdd(Request $request)
    {
        $id = decrypt($request->id);
        $user = json_decode($request->users, true);
        if (!empty($user)) {
            foreach ($user as $dt) {
                FilesShares::firstOrCreate([
                    'group_id' => $dt,
                    'file_id' => $id
                ], [
                    'company_id' => auth()->user()->CompanyUser->id,
                    'is_who' => 'group'
                ]);
                // $usr = User::find($dt);
                // Mail::to($usr->email)->send(new FileShare($usr->name, $usr->email, auth()->user()->CompanyUser->name));
            }

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'File successfully shared',
                    'data' => []
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'Please ensure to select a user',
                'data' => []
            ],
            400
        );
    }

    public function fileShareUser($id)
    {
        $users = User::where('company_id', auth()->user()->CompanyUser->id)->where('role', 'user')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $users
            ],
            201
        );
    }

    public function fileShareUserAdd(Request $request)
    {
        $id = decrypt($request->id);
        $user = json_decode($request->users, true);
        if (!empty($user)) {
            foreach ($user as $dt) {
                FilesShares::firstOrCreate([
                    'user_id' => $dt,
                    'file_id' => $id
                ], [
                    'company_id' => auth()->user()->CompanyUser->id,
                    'user_from' => auth()->user()->id,
                    'is_who' => 'user',
                    'expire_date' => $request->expire_date
                ]);
                $usr = User::find($dt);
                Mail::to($usr->email)->send(new FileShare($usr->name, $usr->email, auth()->user()->CompanyUser->name));
            }

            return response()->json(
                [
                    'status' => '200',
                    'message' => 'File successfully shared',
                    'data' => []
                ],
                201
            );
        }

        return response()->json(
            [
                'status' => '400',
                'message' => 'Please ensure to select a user',
                'data' => []
            ],
            400
        );
    }

    public function fileAccessGroup($id)
    {
        $users = FilesShares::where('file_id', decrypt($id))->where('is_who', 'group')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $users
            ],
            201
        );
    }

    public function fileAccessUser($id)
    {
        $users = FilesShares::where('file_id', decrypt($id))->where('is_who', 'user')->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $users
            ],
            201
        );
    }

    public function fileAccessLog($id)
    {
        $data = FileShareLog::where('file_id', decrypt($id))->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function fileAccessRevoke($id)
    {
        $idUser = decrypt($id);
        FilesShares::find($idUser)->delete();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Access revoke successfully',
                'data' => []
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
                'data' => []
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
                'data' => []
            ],
            201
        );
    }

    public function profile()
    {
        $user = User::find(auth()->user()->id);
        $companyUser = CompanyUser::find(auth()->user()->CompanyUser->id);
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'user' => $user,
                    'companyUser' => $companyUser
                ]
            ],
            201
        );
    }

    public function profileUpload(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $companyUser = CompanyUser::find(auth()->user()->CompanyUser->id);

        if ($request->avatar) {
            $file = $request->file('avatar');
            $file_name = time() . "avatar." . $file->getClientOriginalExtension();
            $file->move(public_path('avatar'), $file_name);

            if ($user->avatar) {
                unlink(public_path($user->avatar));
            }

            $user->update([
                'avatar' => 'avatar/' . $file_name,
            ]);
        }

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'enable_2fa' => $request->enable_2fa ?? 0,
        ]);

        $companyUser->update([
            'name' => $request->name,
        ]);

        return response()->json(
            [
                'status' => '200',
                'message' => 'Profile updated successfully',
                'data' => []
            ],
            201
        );
    }

    // Certificate Management
    public function certificateList($id)
    {
        $formId = decrypt($id);
        $certs = Certificate::where('form_id', $formId)->get();

        $data = [];
        foreach ($certs as $cert) {
            $data[] =  [
                'form_id' => $id,
                "form_name" => $cert->form->name,
                "id" => encrypt($cert->id),
                "form_id" => encrypt($cert->form_id),
                "name" => $cert->name,
                "template" => url('certificates/' . $cert->template),
                "status" => $cert->status,
                "created_at" => $cert->created_at,
            ];
        }

        return response()->json([
            'status' => '200',
            'message' => 'Record listed',
            'data' => $data
        ], 201);
    }

    public function certificateCreate(Request $request)
    {
        $formId = decrypt($request->form_id);
        $file_name = null;
        if ($request->hasFile('template')) {
            $file = $request->file('template');
            $file_name = time() . "_cert." . $file->getClientOriginalExtension();
            $file->move(public_path('certificates'), $file_name);
        }

        Certificate::create([
            'form_id' => $formId,
            'name' => $request->name,
            'template' => $file_name,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'status' => '200',
            'message' => 'Certificate successfully created',
            'data' => []
        ], 201);
    }

    public function certificateUpdate(Request $request)
    {
        $id = decrypt($request->id);
        $cert = Certificate::findOrFail($id);

        if ($request->hasFile('template')) {
            $file = $request->file('template');
            $file_name = time() . "_cert." . $file->getClientOriginalExtension();
            $file->move(public_path('certificates'), $file_name);

            if ($cert->template && file_exists(public_path('certificates/' . $cert->template))) {
                unlink(public_path('certificates/' . $cert->template));
            }
            $cert->template = $file_name;
        }

        $cert->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => '200',
            'message' => 'Certificate successfully updated',
            'data' => []
        ], 201);
    }

    public function certificateDelete($id)
    {
        $cert = Certificate::findOrFail(decrypt($id));
        if ($cert->template && file_exists(public_path('certificates/' . $cert->template))) {
            unlink(public_path('certificates/' . $cert->template));
        }
        $cert->delete();
        return response()->json([
            'status' => '200',
            'message' => 'Certificate successfully deleted',
            'data' => []
        ], 201);
    }

    public function certificateApplicants($id)
    {
        $certId = decrypt($id);
        $cert = Certificate::findOrFail($certId);
        $applicants = EventRegistration::where('form_id', $cert->form_id)->with('user')->get();

        $dataCert = [
            'id' => encrypt($cert->id),
            'form_id' => encrypt($cert->form_id),
            'form_name' => $cert->form->name,
            'name' => $cert->name,
            'template' => url('certificates/' . $cert->template),
            'status' => $cert->status,
            'created_at' => $cert->created_at,
        ];

        $data = [];
        foreach ($applicants as $app) {
            $regStatus = $cert->registrations()->where('event_registration_id', $app->id)->first();
            $data[] = [
                'id' => encrypt($app->id),
                'name' => $app->user->name,
                'email' => $app->user->email,
                'is_collected' => $regStatus ? (bool)$regStatus->pivot->is_collected : false,
                'is_sent' => $regStatus ? (bool)$regStatus->pivot->is_sent : false,
            ];
        }

        return response()->json([
            'status' => '200',
            'message' => 'Record listed',
            'data' => [
                'certificate' => $dataCert,
                'applicants' => $data
            ]
        ], 201);
    }

    public function certificateCollect(Request $request)
    {
        $certId = decrypt($request->cert_id);
        $regId = decrypt($request->reg_id);
        $status = $request->status;

        $cert = Certificate::findOrFail($certId);
        $cert->registrations()->updateExistingPivot($regId, ['is_collected' => $status]);

        if (!$cert->registrations()->where('event_registration_id', $regId)->exists()) {
            $cert->registrations()->attach($regId, ['is_collected' => $status]);
        }

        return response()->json([
            'status' => '200',
            'message' => 'Status successfully updated',
            'data' => []
        ], 201);
    }

    public function certificateMail(Request $request)
    {
        $certId = decrypt($request->cert_id);
        $json = str_replace("'", '"', $request->registrations);
        $registrations = json_decode($json, true);
        if (!$registrations) {
            $registrations = $request->registrations; // Fallback for direct array
        }

        $cert = Certificate::findOrFail($certId);
        $messageBody = $request->message;

        $manager = new ImageManager(new Driver());
        $tempDir = storage_path('app/temp_certs');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        foreach ($registrations as $regEncId) {
            $regId = decrypt($regEncId);
            $registration = EventRegistration::findOrFail($regId);
            $userName = $registration->user->name;

            $img = $manager->read(public_path('certificates/' . $cert->template));
            $img->text($userName, $img->width() / 2, $img->height() / 2, function ($font) {
                $font->file('C:\Windows\Fonts\arial.ttf');
                $font->size(60);
                $font->color('#000');
                $font->align('center');
                $font->valign('middle');
            });

            $tempPath = $tempDir . '/' . uniqid() . '.png';
            $img->save($tempPath);

            Mail::to($registration->user->email)->send(new CertificateMail($userName, $messageBody, $tempPath));

            $cert->registrations()->updateExistingPivot($regId, ['is_sent' => true]);
            if (!$cert->registrations()->where('event_registration_id', $regId)->exists()) {
                $cert->registrations()->attach($regId, ['is_sent' => true]);
            }
        }

        return response()->json([
            'status' => '200',
            'message' => 'Certificates successfully sent',
            'data' => []
        ], 201);
    }

    // Souvenir Management
    public function souvenirList($id)
    {
        $formId = decrypt($id);
        $dt = Souvenir::where('form_id', $formId)->get();
        $data = [];
        foreach ($dt as $d) {
            $data[] =  [
                'form_id' => $id,
                "form_name" => $d->form->name,
                "id" => encrypt($d->id),
                "form_id" => encrypt($d->form_id),
                "name" => $d->name,
                "image" => url('souvenirs/' . $d->image),
                "status" => $d->status,
                "created_at" => $d->created_at,

            ];
        }
        return response()->json([
            'status' => '200',
            'message' => 'Record listed',
            'data' => $data
        ], 201);
    }

    public function souvenirCreate(Request $request)
    {
        $formId = decrypt($request->form_id);
        $file_name = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_name = time() . "_souvenir." . $file->getClientOriginalExtension();
            $file->move(public_path('souvenirs'), $file_name);
        }

        Souvenir::create([
            'form_id' => $formId,
            'name' => $request->name,
            'image' => $file_name,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'status' => '200',
            'message' => 'Souvenir successfully created',
            'data' => []
        ], 201);
    }

    public function souvenirUpdate(Request $request)
    {
        $id = decrypt($request->id);
        $souvenir = Souvenir::findOrFail($id);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $file_name = time() . "_souvenir." . $file->getClientOriginalExtension();
            $file->move(public_path('souvenirs'), $file_name);

            if ($souvenir->image && file_exists(public_path('souvenirs/' . $souvenir->image))) {
                unlink(public_path('souvenirs/' . $souvenir->image));
            }
            $souvenir->image = $file_name;
        }

        $souvenir->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => '200',
            'message' => 'Souvenir successfully updated',
            'data' => []
        ], 201);
    }

    public function souvenirDelete($id)
    {
        $souvenir = Souvenir::findOrFail(decrypt($id));
        if ($souvenir->image && file_exists(public_path('souvenirs/' . $souvenir->image))) {
            unlink(public_path('souvenirs/' . $souvenir->image));
        }
        $souvenir->delete();
        return response()->json([
            'status' => '200',
            'message' => 'Souvenir successfully deleted',
            'data' => []
        ], 201);
    }

    public function souvenirApplicants($id)
    {
        $souvenirId = decrypt($id);
        $souvenir = Souvenir::findOrFail($souvenirId);
        $applicants = EventRegistration::where('form_id', $souvenir->form_id)->with('user')->get();

        $dataSouvenir =  [
            'form_id' => $id,
            "form_name" => $souvenir->form->name,
            "id" => encrypt($souvenir->id),
            "form_id" => encrypt($souvenir->form_id),
            "name" => $souvenir->name,
            "image" => url('souvenirs/' . $souvenir->image),
            "status" => $souvenir->status,
            "created_at" => $souvenir->created_at,

        ];

        $data = [];
        foreach ($applicants as $app) {
            $regStatus = $souvenir->registrations()->where('event_registration_id', $app->id)->first();
            $data[] = [
                'id' => encrypt($app->id),
                'name' => $app->user->name,
                'email' => $app->user->email,
                'is_collected' => $regStatus ? (bool)$regStatus->pivot->is_collected : false,
            ];
        }

        return response()->json([
            'status' => '200',
            'message' => 'Record listed',
            'data' => [
                'souvenir' => $dataSouvenir,
                'applicants' => $data
            ]
        ], 201);
    }

    public function souvenirCollect(Request $request)
    {
        $souvenirId = decrypt($request->souvenir_id);
        $regId = decrypt($request->reg_id);
        $status = $request->status;

        $souvenir = Souvenir::findOrFail($souvenirId);
        $souvenir->registrations()->updateExistingPivot($regId, ['is_collected' => $status]);

        if (!$souvenir->registrations()->where('event_registration_id', $regId)->exists()) {
            $souvenir->registrations()->attach($regId, ['is_collected' => $status]);
        }

        return response()->json([
            'status' => '200',
            'message' => 'Status successfully updated',
            'data' => []
        ], 201);
    }
}
