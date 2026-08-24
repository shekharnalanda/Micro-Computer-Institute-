<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Support\AdmissionStore;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AdmissionController extends Controller
{
    public function create()
    {
        return view('admission.apply', [
            'courses' => Course::where('is_active', true)->orderBy('sort_order')->orderBy('title')->get(),
            'settings' => SiteSettings::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_name' => ['required','string','max:100'],
            'dob' => ['required','date','before:today'],
            'gender' => ['required', Rule::in(['Male','Female','Other'])],
            'guardian_name' => ['required','string','max:100'],
            'phone' => ['required','regex:/^[0-9+ -]{10,15}$/'],
            'email' => ['nullable','email','max:150'],
            'address' => ['required','string','max:300'],
            'city' => ['required','string','max:100'],
            'qualification' => ['required','string','max:150'],
            'course_code' => ['required', Rule::exists('courses','code')->where('is_active', true)],
            'preferred_time' => ['nullable','string','max:100'],
            'message' => ['nullable','string','max:800'],
            'photo' => ['required','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'website' => ['nullable','max:0'],
        ]);
        unset($data['website']);
        $photo = $request->file('photo');
        $photoName = 'mci-student-'.now()->format('YmdHis').'-'.strtolower(\Illuminate\Support\Str::random(8)).'.'.$photo->getClientOriginalExtension();
        if (! is_dir(public_path('uploads/student-photos'))) mkdir(public_path('uploads/student-photos'), 0755, true);
        $photo->move(public_path('uploads/student-photos'), $photoName);
        $data['photo_path'] = 'uploads/student-photos/'.$photoName;
        $course = Course::where('code', $data['course_code'])->firstOrFail();
        $data['course_fee'] = (float) ($course->fee_amount ?? 0);
        $data['course_fee_note'] = $course->fee_note;
        $data['ip_address'] = $request->ip();
        $application = AdmissionStore::add($data);

        try {
            $recipient = SiteSettings::get('email', config('mail.enquiry_to'));
            Mail::raw(
                "Application: {$application['application_no']}\nStudent: {$application['student_name']}\nGuardian: {$application['guardian_name']}\nPhone: {$application['phone']}\nCourse: {$application['course_code']}\nCourse Fee: ₹".number_format((float) $application['course_fee'], 2)."\nCity: {$application['city']}\nQualification: {$application['qualification']}",
                function ($mail) use ($application, $recipient) {
                    $mail->to($recipient)->subject("New Admission Application: {$application['application_no']}");
                    if (! empty($application['email'])) $mail->replyTo($application['email'], $application['student_name']);
                }
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Application submitted successfully.')->with('application_no', $application['application_no']);
    }
}
