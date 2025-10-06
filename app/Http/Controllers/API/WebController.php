<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ContactBooking;
use App\Mail\ContactBookingMail;
use App\Models\ContactSubmission;
use App\Mail\ContactBookingAdmMail;
use App\Mail\ContactSubmissionMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactSubmissionAdmMail;

class WebController extends Controller
{
    // Store submission
    public function contact(Request $request)
    {
        $submission = ContactSubmission::create([
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "email" => $request->email,
            "phone" => $request->phone,
            "company" => $request->company,
            "detail" => $request->detail,
            "req" => uniqid()
        ]);

        Mail::to("defcommng@gmail.com")->send(new ContactSubmissionAdmMail($submission));
        Mail::to($request->email)->send(new ContactSubmissionMail($submission));

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your form has been submitted.',
            'data' => $submission
        ], 201);
    }

    // Store submission
    public function booking(Request $request)
    {
        $submission = ContactBooking::create([
            "dateTime" => $request->dateTime,
            "name" => $request->name,
            "role" => $request->role,
            "email" => $request->email,
            "phone" => $request->phone,
            "location" => $request->location,
            "meeting_type" => $request->meeting_type,
            "reason" => $request->reason,
            "req" => uniqid()
        ]);

        Mail::to("defcommng@gmail.com")->send(new ContactBookingAdmMail($submission));
        Mail::to($request->email)->send(new ContactBookingMail($submission));

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your form has been submitted.',
            'data' => $submission
        ], 201);
    }

    // Get all submissions (for admin)
    public function index()
    {
        return response()->json(ContactSubmission::latest()->get());
    }

    // Get a single submission
    public function show($id)
    {
        $submission = ContactSubmission::findOrFail($id);
        return response()->json($submission);
    }

    // Delete submission
    public function destroy($id)
    {
        $submission = ContactSubmission::findOrFail($id);
        $submission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Submission deleted successfully.'
        ]);
    }
}
