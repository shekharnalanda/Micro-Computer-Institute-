<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $certificate['certificate_no'] }} · Certificate</title><style>
@page{size:A4 landscape;margin:8mm}*{box-sizing:border-box}body{margin:0;background:#eaf0f5;color:#07172d;font-family:Georgia,serif}.toolbar{max-width:1100px;margin:18px auto 10px;display:flex;justify-content:space-between;font-family:Arial}.toolbar a,.toolbar button{border:0;border-radius:8px;padding:10px 15px;text-decoration:none;font-weight:bold;cursor:pointer}.toolbar a{background:#fff;color:#07172d}.toolbar button{background:#1769ff;color:#fff}.certificate{position:relative;width:min(1100px,calc(100% - 20px));aspect-ratio:1.414;margin:auto;background:#fff;border:16px solid #082b59;padding:9px}.inner{height:100%;border:3px solid #c99b37;padding:34px 60px;text-align:center;background:radial-gradient(circle at center,#fff 0,#fff 55%,#f3f7fc 100%)}.logo{width:82px;height:82px;border-radius:50%}.eyebrow{margin:10px 0 3px;color:#53697c;font:700 11px Arial;letter-spacing:3px}.certificate h1{margin:4px 0;color:#082b59;font-size:42px}.subtitle{font-size:18px;color:#c18416;font-style:italic}.presented{margin:24px 0 8px;color:#53697c;font-size:15px}.student{display:inline-block;min-width:55%;margin:0;padding:5px 30px 9px;border-bottom:2px solid #c99b37;font-size:35px;color:#082b59}.copy{max-width:800px;margin:16px auto;font-size:17px;line-height:1.7}.copy strong{color:#1769ff}.description{max-width:740px;margin:10px auto;color:#52697c;font-size:13px}.meta{display:flex;justify-content:center;gap:35px;margin-top:20px;font:12px Arial;color:#52697c}.signatures{display:flex;justify-content:space-between;align-items:end;margin-top:45px;font:11px Arial}.signatures span{min-width:190px;padding-top:7px;border-top:1px solid #07172d}.seal{display:grid;place-items:center;width:90px;height:90px;border:3px double #c99b37;border-radius:50%;color:#9b6b0d;font-weight:bold}.credential-row{display:flex;align-items:center;justify-content:center;gap:34px;margin:14px auto 8px}.student-photo{width:72px;height:86px;object-fit:cover;border:2px solid #c99b37;border-radius:6px}.student-photo-fallback{display:grid;place-items:center;background:#eef4fa;font:900 28px Arial;color:#082b59}.verify-qr{display:block;width:68px;height:68px;margin:0 auto 4px}.verify{text-align:center;font:9px Arial;color:#718599}.verify b{display:block;color:#082b59;margin-top:3px}@media(max-width:750px){.certificate{aspect-ratio:auto}.inner{padding:25px}.certificate h1{font-size:30px}.student{font-size:25px}.signatures{gap:15px}.credential-row{gap:20px;flex-wrap:wrap}}
@media print{
html,body{width:100%;height:100%;background:#fff}
body{margin:0}
.toolbar{display:none}
.certificate{width:277mm;height:190mm;max-width:none;aspect-ratio:auto;margin:0;border-width:10px;padding:6px;overflow:hidden;page-break-inside:avoid;break-inside:avoid}
.inner{padding:13px 38px;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.logo{width:58px;height:58px}
.eyebrow{margin:4px 0 2px}
.certificate h1{margin:2px 0;font-size:31px}
.subtitle{font-size:15px}
.presented{margin:8px 0 4px}
.student{padding:3px 24px 5px;font-size:27px}
.copy{margin:7px auto;font-size:14px;line-height:1.4}
.description{margin:5px auto;font-size:11px}
.credential-row{gap:28px;margin:7px auto 5px}
.student-photo{width:54px;height:65px}
.verify-qr{width:54px;height:54px}
.seal{width:65px;height:65px;font-size:12px}
.meta{gap:22px;margin-top:6px;font-size:10px}
.signatures{margin-top:18px;font-size:10px}
.signatures span{min-width:165px}
}
</style></head><body>@if($certificate['is_demo']??false)<div style="position:fixed;inset:38% 0 auto;z-index:9;text-align:center;font:900 90px Arial;color:#b4231825;transform:rotate(-18deg);pointer-events:none">SAMPLE / DEMO</div>@endif<div class="toolbar"><a href="{{ session('student_portal_id') ? route('student.dashboard').'#certificates' : route('admin.certificates.index') }}">← Back to Certificates</a><button onclick="window.print()">Print / Save PDF</button></div>
<article class="certificate"><div class="inner">@php($certificateQr=urlencode(route('certificates.verify').'?code='.$certificate['verification_code']))<img class="logo" src="{{ asset('images/mci-logo.webp') }}" alt="MCI"><p class="eyebrow">MICRO COMPUTER INSTITUTE</p><h1>{{ $certificate['title'] }}</h1><div class="subtitle">{{ $certificate['type']==='completion' ? 'Certificate of Achievement' : ($certificate['type']==='merit' ? 'Recognition of Excellence' : 'Recognition of Participation') }}</div><p class="presented">This certificate is proudly presented to</p><h2 class="student">{{ $student['student_name'] }}</h2>
@if($certificate['type']==='completion')
<p class="copy">for successfully completing the <strong>{{ $course?->title ?? $student['course_code'] }}</strong> course @if($certificate['completion_date']) on {{ \Carbon\Carbon::parse($certificate['completion_date'])->format('d F Y') }} @endif.</p>
@elseif($certificate['type']==='merit')
<p class="copy">in recognition of outstanding merit and achievement in <strong>{{ $course?->title ?? $student['course_code'] }}</strong>.</p>
@else
<p class="copy">for active participation in <strong>{{ $certificate['description'] ?: ($course?->title ?? $student['course_code']) }}</strong>.</p>
@endif
@if($certificate['grade'])<p class="description"><b>Grade / Distinction:</b> {{ $certificate['grade'] }}</p>@endif
@if($certificate['description'] && $certificate['type']!=='participation')<p class="description">{{ $certificate['description'] }}</p>@endif
<div class="credential-row"><div>@if(!empty($student['photo_path']))<img class="student-photo" src="{{ asset($student['photo_path']) }}" alt="{{ $student['student_name'] }}">@else<div class="student-photo student-photo-fallback">{{ strtoupper(substr($student['student_name'],0,1)) }}</div>@endif</div><div class="seal">MCI<br>VERIFIED</div><div class="verify"><img class="verify-qr" src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&amp;data={{ $certificateQr }}" alt="Certificate verification QR">Verify Online<b>{{ $certificate['verification_code'] }}</b></div></div><div class="meta"><span>Certificate No: <b>{{ $certificate['certificate_no'] }}</b></span><span>Issue Date: <b>{{ \Carbon\Carbon::parse($certificate['issue_date'])->format('d F Y') }}</b></span><span>Roll No: <b>{{ $student['roll_no'] ?? $student['application_no'] }}</b></span></div><div class="signatures"><span>Course Coordinator</span><span>Authorised Signatory</span></div></div></article></body></html>
