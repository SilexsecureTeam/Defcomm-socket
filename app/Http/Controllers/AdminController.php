<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Files;
use App\Mail\FileShare;
use App\Models\Meeting;
use App\Mail\Invitation;
use App\Models\EventForm;
use App\Models\CompanyUser;
use App\Models\FilesShares;
use App\Models\CompanyGroup;
use App\Models\FileShareLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\CompanyGroupUser;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\FileEncryptorService;

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
        $notify = Notification::get();
        return view('admin.notification', [
            'notify' => $notify,
        ]);
    }

    public function notificationCreate(Request $request)
    {
        $file = $request->file('icon');
        $file_name = time() . "icon." . $file->getClientOriginalExtension();
        $file->move(public_path('icon'), $file_name);

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
            'data' => $data
        ]);
    }

    public function formCreate(Request $request)
    {
        EventForm::create([
            'name' => $request->name,
            'message' => $request->message,
            'group_id' => decrypt($request->group_id),
            'meeting_id' => $request->meeting_id ? decryptHelper($request->meeting_id) : null,
            'signup' => $request->signup,
            'status' => $request->status,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', "Event form successfully created");
    }

    public function formUpdate(Request $request)
    {
        EventForm::find(decrypt($request->id))->update([
            'name' => $request->name,
            'message' => $request->message,
            'group_id' => decrypt($request->group_id),
            'meeting_id' => $request->meeting_id ? decryptHelper($request->meeting_id) : null,
            'signup' => $request->signup,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', "Event form successfully created");
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
}
