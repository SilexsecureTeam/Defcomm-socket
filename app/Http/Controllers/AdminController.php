<?php

namespace App\Http\Controllers;

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

        return view('admin.dashboard', [
            'option' => "account",
            'users' => $users,
            'usersCount' => $users->count(),
            'groupCount' => $groups->count(),
            'fileCount' => $file->count(),
            'fileSizeSum' => $file_size,
            'fileArchiveCount' => $fileArchive->count(),
            'fileActiveCount' => $fileActive->count(),
        ]);
    }

    public function account()
    {
        $users = User::where('company_id', auth()->user()->CompanyUser->id)->where('role', 'user')->orderBy('name', 'ASC')->get();
        return view('admin.account', [
            'option' => "account",
            'users' => $users,
        ]);
    }

    public function notification()
    {
        $notify = Notification::where('company_id', auth()->user()->company_id)->get();
        return view('admin.notification', [
            'notify' => $notify,
        ]);
    }

    public function notificationCreate(Request $request)
    {
        $file_name = null;
        if($request->hasFile('icon')) {
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

        return redirect()->back()->with('success', "Notification successfully added");
    }

    public function notificationDelete($id)
    {
        $idUser = decrypt($id);
        Notification::find($idUser)->delete();
        return redirect()->back()->with('success', "Notification successfully removed");
    }

    public function notificationEdit(Request $request)
    {
        $idUser = decrypt($request->id);
        $file_name = null;
        if($request->hasFile('icon')) {
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
        return redirect()->back()->with('success', "Notification successfully updated");
    }

    public function accountStatus($id, $status)
    {
        $idUser = decrypt($id);

        $user = User::find($idUser);
        $user->update(['status' => $status]);

        return redirect()->back()->with('success', "User status successfully updated");
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
            return redirect()->back()->with('error', $error);
        }

        $userCount = User::where('company_id', auth()->user()->CompanyUser->id)->count();

        $user = User::find(auth()->user()->id);

        if ($user->plan_id === null) {
            return redirect()->back()->with('error', "You do not have an active plan. Please subscribe");
        }

        if ($userCount >= $user->plan->no_user) {
            return redirect()->back()->with('error', "You have reach you limit");
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

        return redirect()->back()->with('success', "User successfully added");
    }

    public function meeting()
    {
        $meet = Meeting::where('user_id', auth()->user()->id)->get();
        return view('admin.meeting', [
            'option' => "Meetings",
            'meet' => $meet,
        ]);
    }

    public function form()
    {
        $data = EventForm::where('user_id', auth()->user()->id)->get();
        $groups = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->get();
        $meet = Meeting::where('user_id', auth()->user()->id)->get();
        return view('admin.form', [
            'option' => "Event Form",
            'data' => $data,
            'groups' => $groups,
            'meet' => $meet,
        ]);
    }

    public function formApplication($id)
    {
        $data = EventRegistration::where('form_id', decrypt($id))->get();
        return view('admin.formApplication', [
            'option' => "Event Application",
            'id' => $id,
            'data' => $data
        ]);
    }

    public function formAttendance($id)
    {
        $data = EventRegistrationsAttendances::where('form_id', decrypt($id))->get();
        return view('admin.formAttendance', [
            'option' => "Event Attendance",
            'data' => $data
        ]);
    }

    public function formCreate(Request $request)
    {
        EventForm::create([
            'name' => $request->name,
            'message' => $request->message,
            'group_id' => $request->group_id ? decrypt($request->group_id) : null,
            'meeting_id' => $request->meeting_id ? decryptHelper($request->meeting_id) : null,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'started_at' => $request->started_at,
            'ended_at' => $request->ended_at,
            'signup' => $request->signup ?? "disabled",
            'attendance' => $request->attendance ?? "disabled",
            'status' => $request->status ?? "active",
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', "Event form successfully created");
    }

    public function formUpdate(Request $request)
    {
        $eventForm = EventForm::find(decrypt($request->id));
        $eventForm->update([
            'name' => $request->name,
            'message' => $request->message,
            'group_id' => $request->group_id ? decrypt($request->group_id) : $eventForm->group_id,
            'meeting_id' => $request->meeting_id ? decryptHelper($request->meeting_id) : $eventForm->meeting_id,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'started_at' => $request->started_at,
            'ended_at' => $request->ended_at,
            'signup' => $request->signup,
            'attendance' => $request->attendance,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', "Event form successfully created");
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

        return redirect()->back()->with('success', "Event form successfully created");
    }

    public function attendanceUser($id, $userId)
    {
        $data = EventRegistrationsAttendances::updateOrCreate([
            'form_id' => decrypt($id),
            'user_id' => decrypt($userId),
        ], [
            'comment' => "Checked by " . auth()->user()->name,
        ]);

        return redirect()->back()->with('tab', '');
    }

    public function group()
    {
        $groups = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->get();
        return view('admin.group', [
            'option' => "group",
            'groups' => $groups
        ]);
    }

    public function groupCreate(Request $request)
    {
        $groupCount = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->count();

        $user = User::find(auth()->user()->id);

        if ($user->plan_id === null) {
            return redirect()->back()->with('error', "You do not have an active plan. Please subscribe");
        }

        if ($groupCount >= $user->plan->no_group) {
            return redirect()->back()->with('error', "You have reach you limit");
        }

        CompanyGroup::create([
            'name' => $request->name,
            'decription' => $request->decription,
            'company_id' => auth()->user()->CompanyUser->id,
        ]);

        return redirect()->back()->with('success', "Group successfully created");
    }

    public function groupUpdate(Request $request)
    {
        $idUser = decrypt($request->id);
        CompanyGroup::find($idUser)->update([
            'name' => $request->name,
            'decription' => $request->decription,
        ]);

        return redirect()->back()->with('success', "Group successfully updated");
    }

    public function member($id)
    {
        $idUser = decrypt($id);
        $member = CompanyGroupUser::where('group_id', $idUser)->get();
        return view('admin.member', [
            'id' => $id,
            'member' => $member,
        ]);
    }

    public function memberRemove($id)
    {
        $idUser = decrypt($id);
        CompanyGroupUser::find($idUser)->delete();
        return redirect()->back()->with('success', "Group member successfully removed");
    }

    public function memberAdd($id)
    {
        $users = User::where('company_id', auth()->user()->CompanyUser->id)->where('role', 'user')->orderBy('name', 'ASC')->get();
        return view('admin.account', [
            'option' => "group",
            'id' => $id,
            'users' => $users
        ]);
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

            return redirect('/admin/group/member/' . $request->id)->with('success', "Group member added successfully");
        }

        return redirect()->back()->with('error', "Please ensure to select a user");
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

            return redirect()->back()->with('success', "User account deactivated successfully");
        }

        return redirect()->back()->with('error', "Please ensure to select a user");
    }

    public function file()
    {
        $file = Files::where('company_id', auth()->user()->CompanyUser->id)->where('user_type', 'admin')->orderBy('id', 'DESC')->get();
        return view('admin.file', [
            'file' => $file,
            'option' => "admin"
        ]);
    }

    public function fileUser()
    {
        $file = Files::where('company_id', auth()->user()->CompanyUser->id)->where('user_type', 'user')->orderBy('id', 'DESC')->get();
        return view('admin.file', [
            'file' => $file,
            'option' => "user"
        ]);
    }

    public function fileRequest()
    {
        $file = FilesShares::where('company_id', auth()->user()->CompanyUser->id)->where('status', 'block')->orderBy('id', 'DESC')->get();
        return view('admin.file', [
            'file' => $file,
            'option' => "request"
        ]);
    }

    public function fileView($id)
    {
        $file = Files::find(decrypt($id));
        return view('admin.fileView', [
            'file' => $file,
            'user' => User::find(auth()->user()->id),
        ]);
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

        return redirect()->back()->with('success', "File Securely uploaded");
    }

    public function fileShareGroup($id)
    {
        $groups = CompanyGroup::where('company_id', auth()->user()->CompanyUser->id)->get();
        return view('admin.group', [
            'option' => "fileShare",
            'id' => $id,
            'groups' => $groups
        ]);
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

            return redirect('/admin/file/access/group/' . $request->id)->with('success', "File successfully shared");
        }

        return redirect()->back()->with('error', "Please ensure to select a user");
    }

    public function fileShareUser($id)
    {
        $users = User::where('company_id', auth()->user()->CompanyUser->id)->where('role', 'user')->get();
        return view('admin.account', [
            'option' => "fileShare",
            'id' => $id,
            'users' => $users
        ]);
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

            return redirect('/admin/file/access/user/' . $request->id)->with('success', "File successfully shared");
        }

        return redirect()->back()->with('error', "Please ensure to select a user");
    }

    public function fileAccessGroup($id)
    {
        $users = FilesShares::where('file_id', decrypt($id))->where('is_who', 'group')->get();
        return view('admin.fileAccessGroup', [
            'option' => "fileAccess",
            'users' => $users
        ]);
    }

    public function fileAccessUser($id)
    {
        $users = FilesShares::where('file_id', decrypt($id))->where('is_who', 'user')->get();
        return view('admin.fileAccessUser', [
            'option' => "fileAccess",
            'users' => $users
        ]);
    }

    public function fileAccessLog($id)
    {
        $data = FileShareLog::where('file_id', decrypt($id))->get();
        return view('admin.fileAccessLog', [
            'option' => "fileAccessLog",
            'data' => $data
        ]);
    }

    public function fileAccessRevoke($id)
    {
        $idUser = decrypt($id);
        FilesShares::find($idUser)->delete();
        return redirect()->back()->with('success', "Access revoke successfully");
    }

    public function fileAccept($id)
    {
        $idUser = decrypt($id);
        FilesShares::find($idUser)->update(['status' => 'access']);
        return redirect()->back()->with('success', "File accepted successfully");
    }

    public function fileDecline($id)
    {
        $idUser = decrypt($id);
        FilesShares::find($idUser)->delete();
        return redirect()->back()->with('success', "File decline successfully");
    }

    public function profile()
    {
        $user = User::find(auth()->user()->id);
        $companyUser = CompanyUser::find(auth()->user()->CompanyUser->id);
        return view('admin.profile', [
            'user' => $user,
            'companyUser' => $companyUser,
        ]);
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

        return redirect()->back()->with('success', "Profile updated successfully");
    }

    // Certificate Management
    public function certificateList($id)
    {
        $formId = decrypt($id);
        $form = EventForm::findOrFail($formId);
        $data = Certificate::where('form_id', $formId)->get();
        return view('admin.certificate', [
            'option' => "Certificates",
            'form' => $form,
            'data' => $data,
            'id' => $id
        ]);
    }

    public function certificateCreate(Request $request)
    {
        $formId = decrypt($request->id);
        $file_name = null;
        if($request->hasFile('template')) {
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

        return redirect()->back()->with('success', "Certificate successfully created");
    }

    public function certificateUpdate(Request $request)
    {
        $id = decrypt($request->id);
        $cert = Certificate::findOrFail($id);
        
        if($request->hasFile('template')) {
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

        return redirect()->back()->with('success', "Certificate successfully updated");
    }

    public function certificateDelete($id)
    {
        $cert = Certificate::findOrFail(decrypt($id));
        if ($cert->template && file_exists(public_path('certificates/' . $cert->template))) {
            unlink(public_path('certificates/' . $cert->template));
        }
        $cert->delete();
        return redirect()->back()->with('success', "Certificate successfully deleted");
    }

    public function certificateApplicants($id)
    {
        $certId = decrypt($id);
        $cert = Certificate::findOrFail($certId);
        $data = EventRegistration::where('form_id', $cert->form_id)->get();
        
        return view('admin.certificateApplicants', [
            'option' => "Certificate Applicants",
            'cert' => $cert,
            'data' => $data,
            'id' => $id
        ]);
    }

    public function certificateCollect(Request $request)
    {
        $certId = decrypt($request->cert_id);
        $regId = decrypt($request->reg_id);
        $status = $request->status; // 1 for collected, 0 for not

        $cert = Certificate::findOrFail($certId);
        $cert->registrations()->updateExistingPivot($regId, ['is_collected' => $status]);

        if (!$cert->registrations()->where('event_registration_id', $regId)->exists()) {
             $cert->registrations()->attach($regId, ['is_collected' => $status]);
        }

        return response()->json(['success' => true]);
    }

    public function certificateMail(Request $request)
    {
        $certId = decrypt($request->cert_id);
        $registrations = json_decode($request->registrations, true);
        $cert = Certificate::findOrFail($certId);
        $subject = $request->subject;
        $messageBody = $request->message;

        if (empty($registrations)) {
            return redirect()->back()->with('error', "No applicants selected");
        }

        $manager = new ImageManager(new Driver());
        $tempDir = storage_path('app/temp_certs');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        foreach ($registrations as $regEncId) {
            $regId = decrypt($regEncId);
            $registration = EventRegistration::findOrFail($regId);
            $userName = $registration->user->name;

            // Generate customized certificate
            $img = $manager->read(public_path('certificates/' . $cert->template));
            
            // Draw name - adjusting position based on typical certificate design
            // You might need to allow admins to define X, Y in the future
            $img->text($userName, $img->width() / 2, $img->height() / 2, function($font) {
                $font->file('C:\Windows\Fonts\arial.ttf'); 
                $font->size(60);
                $font->color('#000');
                $font->align('center');
                $font->valign('middle');
            });

            $tempPath = $tempDir . '/' . uniqid() . '.png';
            $img->save($tempPath);

            // Send Mail
            Mail::to($registration->user->email)->send(new CertificateMail($userName, $messageBody, $tempPath));

            // Mark as sent in pivot
            $cert->registrations()->updateExistingPivot($regId, ['is_sent' => true]);
            if (!$cert->registrations()->where('event_registration_id', $regId)->exists()) {
                $cert->registrations()->attach($regId, ['is_sent' => true]);
            }

            // Cleanup temp file after sending (optionally deferred or done later)
            // unlink($tempPath); // Best to do this after mail is sent, maybe in mailable's destructor or just leave for periodic cleanup
        }

        return redirect()->back()->with('success', "Certificates have been sent to selected applicants");
    }

    // Souvenir Management
    public function souvenirList($id)
    {
        $formId = decrypt($id);
        $form = EventForm::findOrFail($formId);
        $data = Souvenir::where('form_id', $formId)->get();
        return view('admin.souvenir', [
            'option' => "Souvenirs",
            'form' => $form,
            'data' => $data,
            'id' => $id
        ]);
    }

    public function souvenirCreate(Request $request)
    {
        $formId = decrypt($request->id);
        $file_name = null;
        if($request->hasFile('image')) {
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

        return redirect()->back()->with('success', "Souvenir successfully created");
    }

    public function souvenirUpdate(Request $request)
    {
        $id = decrypt($request->id);
        $souvenir = Souvenir::findOrFail($id);
        
        if($request->hasFile('image')) {
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

        return redirect()->back()->with('success', "Souvenir successfully updated");
    }

    public function souvenirDelete($id)
    {
        $souvenir = Souvenir::findOrFail(decrypt($id));
        if ($souvenir->image && file_exists(public_path('souvenirs/' . $souvenir->image))) {
            unlink(public_path('souvenirs/' . $souvenir->image));
        }
        $souvenir->delete();
        return redirect()->back()->with('success', "Souvenir successfully deleted");
    }

    public function souvenirApplicants($id)
    {
        $souvenirId = decrypt($id);
        $souvenir = Souvenir::findOrFail($souvenirId);
        $data = EventRegistration::where('form_id', $souvenir->form_id)->get();
        
        return view('admin.souvenirApplicants', [
            'option' => "Souvenir Applicants",
            'souvenir' => $souvenir,
            'data' => $data,
            'id' => $id
        ]);
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

        return response()->json(['success' => true]);
    }
}
