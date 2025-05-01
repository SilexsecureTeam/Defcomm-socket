<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\Invitation;
use App\Models\CompanyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $usr = User::where('role', 'admin')->get();
        return view('super.dashboard', [
            'page' => "Dashboard",
            'opt' => 'admin',
            'usr' => $usr
        ]);
    }

    public function account()
    {
        $usr = User::where('role', 'admin')->get();
        return view('super.account', [
            'page' => "Account",
            'opt' => 'admin',
            'usr' => $usr
        ]);
    }

    public function accountView($id)
    {
        $comp = CompanyUser::find(decrypt($id));
        if($comp){
            $usr = User::where('role', 'user')->where('company_id', $comp->id)->get();
            return view('super.account', [
                'page' => $comp->name . " User's Account",
                'opt' => 'user',
                'usr' => $usr
            ]);
        }
        return redirect()->back()->with('error', 'No data found for this account');
    }

    public function accountDelete($id){
        User::find(decrypt($id))->delete();
        return redirect()->back()->with('success', 'User successfully deleted');
    }

    public function accountCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|max:255',
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

        $usr = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'admin',
            'password' => Hash::make($request->password),
        ]);

        $comp = CompanyUser::create([
            'name' => $request->nameorg,
            'user_id' => $usr->id
        ]);

        $otp = rand(1000, 9999);

        $usr->update(['company_id' => $comp->id, 'otp' => $otp]);

        $encrypt = encrypt($request->email);

        Mail::to($request->email)->send(new Invitation($request->name, $request->email, $encrypt, $otp));

        $bodysms = 'Welcome to Defcomm!, Your OTP is ' . $otp . ' or use https://cloud.defcomm.ng/onboarding to join. If you have any questions or concerns we  are here to help contact us via our Help Center';

        $this->TermiiSms($request->phone, $bodysms);

        return redirect()->back()->with('success', "User successfully added");
    }

    public function accountEdit(Request $request)
    {
        $id = decrypt($request->id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|unique:users,phone,' . $id,
        ]);

        $error = "";
        if ($validator->fails()) {
            foreach ($validator->messages()->all() as $mess) {
                $error .= "$mess <br>";
            }
            return redirect()->back()->with('error', $error);
        }

        $usr = User::find($id);
        $usr->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        if($request->password){
            $usr->update(['password' => Hash::make($request->password)]);
        }

        $comp = CompanyUser::find($usr->company_id)->update([
            'name' => $request->nameorg,
        ]);

        return redirect()->back()->with('success', "User successfully updated");
    }
}
