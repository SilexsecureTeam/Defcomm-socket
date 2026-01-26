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

class AdminController extends Controller
{

    public function dashboard()
    {
        $users = User::where('company_id', auth()->user()->CompanyUser->id)->where('role', 'user')->orderBy('name', 'ASC')->get();
        $groups = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->get();
        $file = Files::where('company_id', auth()->user()->CompanyUser->id)->get();
        $fileArchive = Files::where('company_id', auth()->user()->CompanyUser->id)->where('status', "archive")->get();
        $fileActive = Files::where('company_id', auth()->user()->CompanyUser->id)->where('status', "active")->get();

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
                ]
            ],
            201
        );
    }

    public function account()
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

    public function notification()
    {
        $notify = Notification::where('company_id', auth()->user()->company_id)->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $notify
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

        $error = "";
        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $mess) {
                $error .= "$mess <br>";
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

    public function form()
    {
        $data = EventForm::where('user_id', auth()->user()->id)->get();
        $groups = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->get();
        $meet = Meeting::where('user_id', auth()->user()->id)->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => [
                    'forms' => $data,
                    'groups' => $groups,
                    'meetings' => $meet
                ]
            ],
            201
        );
    }

    public function formApplication($id)
    {
        $data = EventRegistration::where('form_id', decrypt($id))->get();
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
        $data = EventRegistrationsAttendances::where('form_id', decrypt($id))->get();
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
        EventForm::find(decrypt($request->id))->update([
            'name' => $request->name,
            'message' => $request->message,
            'group_id' => decrypt($request->group_id),
            'meeting_id' => $request->meeting_id ? decryptHelper($request->meeting_id) : null,
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
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $groups
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

        CompanyGroup::create([
            'name' => $request->name,
            'decription' => $request->decription,
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

    public function member($id)
    {
        $idUser = decrypt($id);
        $member = CompanyGroupUser::where('group_id', $idUser)->get();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $member
            ],
            201
        );
    }

    public function memberRemove($id)
    {
        $idUser = decrypt($id);
        CompanyGroupUser::find($idUser)->delete();
        return response()->json(
            [
                'status' => '200',
                'message' => 'Group member successfully removed',
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
        $user = json_decode($request->users, true);
        if (!empty($user)) {
            foreach ($user as $dt) {
                CompanyGroupUser::firstOrCreate([
                    'user_id' => $dt,
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
}
