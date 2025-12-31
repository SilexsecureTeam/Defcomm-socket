<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Files;
use App\Mail\FileShare;
use App\Models\ContactList;
use App\Models\FilesShares;
use App\Models\CompanyGroup;
use App\Models\FileShareLog;
use Illuminate\Http\Request;
use App\Models\CompanyGroupUser;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;

class UserController extends Controller
{
    public function dashboard()
    {
        $groups = CompanyGroupUser::where('user_id', auth()->user()->id)->where('status', 'joined')->orderBy('id', 'DESC')->get();
        $groupsPending = CompanyGroupUser::where('user_id', auth()->user()->id)->where('status', 'pending')->orderBy('id', 'DESC')->get();
        $file = FilesShares::where('user_id', auth()->user()->id)->where('status', 'access')->orderBy('id', 'DESC')->get();
        $filePending = FilesShares::where('user_id', auth()->user()->id)->where('status', 'pending')->orderBy('id', 'DESC')->get();

        return view('user.dashboard', [
            'file' => $filePending,
            'group' => $groupsPending,
            'groupCount' => $groups->count(),
            'fileCount' => $file->count(),
            'groupsPendingCount' => $groupsPending->count(),
            'filePendingCount' => $filePending->count(),
        ]);
    }
    
    public function file()
    {
        $file = Files::where('uploaded_by', auth()->user()->id)->orderBy('id', 'DESC')->get();
        return view('user.file', [
            'file' => $file,
            'option' => "mine"
        ]);
    }
    
    public function fileOther()
    {
        $file = FilesShares::where('user_id', auth()->user()->id)->where('status', 'access')->orderBy('id', 'DESC')->get();

        $data = [];
        foreach($file as $dt){
            if($dt->expire_date >= now() || $dt->expire_date == null){
                $data[] = $dt;
            }
        }

        return view('user.file', [
            'file' => collect($data),
            'option' => "other"
        ]);
    }
    
    public function fileRequest()
    {
        $file = FilesShares::where('user_from', auth()->user()->id)->where('status', 'block')->orderBy('id', 'DESC')->get();
        return view('user.file', [
            'file' => $file,
            'option' => "request"
        ]);
    }

    public function fileUpload(Request $request)
    {
        $file = $request->file('file');
        $file_ext = $file->getClientOriginalExtension();

        // return dd($file_ext != "pdf" || $file_ext != "PDF");

        if ($file_ext != "pdf") {
            return redirect()->back()->with('error', "Ensure the file is PDF");
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

        return redirect()->back()->with('success', "File Securely uploaded");
    }

    public function fileView($id)
    {
        $file = Files::find(decrypt($id));
        FileShareLog::create(['user_id' => auth()->user()->id, 'file_id' => $file->id, 'company_id' => auth()->user()->company_id ]);
        return view('admin.fileView', [
            'file' => $file
        ]);
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

    public function fileShareUser($id)
    {
        $contact = ContactList::where('user_id', auth()->user()->id)->get();
        return view('user.contact', [
            'option' => "fileShare",
            'id' => $id,
            'contact' => $contact
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
                    'file_id' => $id,
                ], [
                    'company_id' => auth()->user()->company_id,
                    'user_from' => auth()->user()->id,
                    'is_who' => 'user',
                    'expire_date' => $request->expire_date
                ]);
                $usr = User::find($dt);
                Mail::to($usr->email)->send(new FileShare($usr->name, $usr->email, auth()->user()->name));
            }

            return redirect('/user/file/access/user/' . $request->id)->with('success', "File successfully shared");
        }

        return redirect()->back()->with('error', "Please ensure to select a user");
    }

    public function fileShareUserRequest($id)
    {
        $contact = ContactList::where('user_id', auth()->user()->id)->get();
        return view('user.contact', [
            'option' => "fileShareRequest",
            'id' => $id,
            'contact' => $contact
        ]);
    }

    public function fileShareUserAddRequest(Request $request)
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

            return redirect('/user/file/request')->with('success', "File successfully shared");
        }

        return redirect()->back()->with('error', "Please ensure to select a user");
    }

    public function fileAccessUser($id)
    {
        $users = FilesShares::where('file_id', decrypt($id))->where('is_who', 'user')->get();
        return view('user.fileAccessUser', [
            'option' => "fileAccess",
            'users' => $users
        ]);
    }

    public function fileAccessRevoke($id)
    {
        $idUser = decrypt($id);
        FilesShares::find($idUser)->delete();
        return redirect()->back()->with('success', "Access revoke successfully");
    }

    public function fileAccessLog($id)
    {
        $data = FileShareLog::where('file_id', decrypt($id))->get();
        return view('user.fileAccessLog', [
            'option' => "fileAccessLog",
            'data' => $data
        ]);
    }

    public function group()
    {
        $group = CompanyGroupUser::where('user_id', auth()->user()->id)->where('status', 'joined')->orderBy('id', 'DESC')->get();
        return view('user.group', [
            'group' => $group
        ]);
    }

    public function member($id)
    {
        $idUser = decrypt($id);
        $member = CompanyGroupUser::where('group_id', $idUser)->where('user_id', '!=', auth()->user()->id)->get();
        $user = CompanyGroupUser::where('group_id', $idUser)->where('user_id', auth()->user()->id)->first();
        return view('user.member', [
            'id' => $id,
            'member' => $member,
            'user' => $user,
        ]);
    }

    public function memberChangeState($id, $status)
    {
        $idUser = decrypt($id);
        $state = $status == "yes" ? "no" : "yes";
        CompanyGroupUser::find($idUser)->update(['hide' => $state]);
        return redirect()->back()->with('success', "Group Setting Updated successfully");
    }
    
    public function groupAccept($id)
    {
        $idUser = decrypt($id);
        CompanyGroupUser::find($idUser)->update(['status' => 'joined']);
        return redirect()->back()->with('success', "Group accepted successfully");
    }
    
    public function groupDecline($id)
    {
        $idUser = decrypt($id);
        CompanyGroupUser::find($idUser)->delete();
        return redirect()->back()->with('success', "Group decline successfully");
    }

    public function profile()
    {
        $user = User::find(auth()->user()->id);
        return view('user.profile', [
            'user' => $user,
        ]);
    }

    public function profileUpload(Request $request)
    {
        $user = User::find(auth()->user()->id);

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

        return redirect()->back()->with('success', "Profile updated successfully");
    }

    public function contact()
    {
        $contact = ContactList::where('user_id', auth()->user()->id)->get();
        return view('user.contact', [
            'option' => "contact",
            'contact' => $contact
        ]);
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

        return redirect()->back()->with('success', "Contact successfully saved");
    }

    public function contactRemove($id)
    {
        $idUser = decrypt($id);
        ContactList::find($idUser)->delete();
        return redirect()->back()->with('success', "Contact successfully removed");
    }
    
}
