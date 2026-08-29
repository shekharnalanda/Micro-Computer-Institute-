<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:80'],
            'phone' => ['required','string','max:20','regex:/^[0-9+ -]{10,15}$/'],
            'email' => ['nullable','email','max:120'],
            'city' => ['nullable','string','max:80'],
            'course' => ['required','string','max:40'],
            'message' => ['nullable','string','max:1000'],
            'website' => ['nullable','max:0'],
        ]);

        $enquiry = Enquiry::create([
            'name'=>$data['name'],'phone'=>$data['phone'],'email'=>$data['email'] ?? null,
            'city'=>$data['city'] ?? null,'course_code'=>$data['course'],
            'message'=>$data['message'] ?? null,'ip_address'=>$request->ip(),
        ]);

        try {
            $recipient = SiteSettings::get('email', config('mail.enquiry_to'));
            Mail::raw("Name: {$enquiry->name}\nPhone: {$enquiry->phone}\nEmail: {$enquiry->email}\nCity: {$enquiry->city}\nCourse: {$enquiry->course_code}\nMessage: {$enquiry->message}", function ($mail) use ($enquiry, $recipient) {
                $mail->to($recipient)->subject("New MCI Enquiry: {$enquiry->course_code}");
                if ($enquiry->email) $mail->replyTo($enquiry->email, $enquiry->name);
            });
            if ($enquiry->email) {
                Mail::raw("Dear {$enquiry->name},\n\nThank you for contacting Micro Computer Institute. We have received your enquiry regarding {$enquiry->course_code}. Our team will contact you shortly.\n\nRegards,\nMicro Computer Institute\nMCI Campus, Quamruddin Ganj, Bihar Sharif, Nalanda - 803101\nPhone: 7004773247, 9334779133\nWebsite: https://mciedu.com", function ($mail) use ($enquiry) {
                    $mail->to($enquiry->email, $enquiry->name)->subject("We received your enquiry - Micro Computer Institute");
                });
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['success'=>true,'message'=>'धन्यवाद! आपकी enquiry सुरक्षित हो गई है। हमारी टीम जल्द संपर्क करेगी।']);
    }
}
