<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $result['result_no'] }} · Marksheet</title>
<style>
@page{size:A4 portrait;margin:6mm}
:root{--ink:#07172d;--blue:#1769ff;--line:#dce4ea;--muted:#62778a}*{box-sizing:border-box}body{margin:0;background:#edf2f6;color:var(--ink);font-family:Arial,sans-serif}.toolbar{max-width:850px;margin:22px auto 10px;display:flex;justify-content:space-between}.toolbar a,.toolbar button{border:0;border-radius:9px;padding:11px 16px;text-decoration:none;font-weight:bold;cursor:pointer}.toolbar a{background:#fff;color:var(--ink)}.toolbar button{background:var(--blue);color:#fff}.sheet{width:min(850px,calc(100% - 24px));margin:auto;background:#fff;border:10px solid #eaf1ff;box-shadow:0 20px 60px #07172d1f}.head{text-align:center;padding:28px 30px 20px;border-bottom:2px solid var(--blue)}.head img{width:82px;height:82px;border-radius:50%}.head h1{margin:8px 0 3px;font-size:25px}.head p{margin:0;color:var(--muted);font-size:12px}.head h2{margin:18px 0 0;color:var(--blue);letter-spacing:2px}.body{padding:26px 34px}.identity{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px;padding:12px;border:1px solid var(--line);background:#f9fbfd}.identity-photo{width:76px;height:92px;object-fit:cover;border:1px solid var(--line);border-radius:6px}.identity-fallback{display:grid;place-items:center;font-size:28px;font-weight:900;color:var(--blue)}.identity-qr{width:82px;height:82px}.identity-text{flex:1}.identity-text b{display:block;font-size:18px}.identity-text span{font-size:11px;color:var(--muted)}.meta{display:grid;grid-template-columns:1fr 1fr;gap:10px 35px;margin-bottom:22px}.meta div{display:flex;justify-content:space-between;border-bottom:1px dotted #aab8c5;padding:7px 0;font-size:12px}.meta span{color:var(--muted)}table{width:100%;border-collapse:collapse}th,td{padding:12px;border:1px solid var(--line);text-align:left;font-size:12px}th{background:#f4f8ff;color:#31516c}td:nth-child(n+2),th:nth-child(n+2){text-align:center}.summary{display:grid;grid-template-columns:repeat(4,1fr);margin-top:20px;border:1px solid var(--line)}.summary div{text-align:center;padding:14px;border-right:1px solid var(--line)}.summary div:last-child{border:0}.summary small,.summary b{display:block}.summary small{color:var(--muted);font-size:9px;margin-bottom:5px}.status{margin:20px 0;text-align:center;padding:12px;border-radius:9px;font-weight:bold}.status-pass{background:#e4f8ee;color:#087548}.status-fail{background:#fff0f0;color:#b42318}.remarks{font-size:12px;color:#52697c}.foot{display:flex;justify-content:space-between;gap:30px;margin-top:45px;font-size:11px}.sign{text-align:center;min-width:180px;border-top:1px solid var(--ink);padding-top:7px}.disclaimer{text-align:center;padding:15px;background:#f4f7fa;color:#718599;font-size:9px}@media(max-width:600px){.body{padding:20px}.meta,.summary{grid-template-columns:1fr}.summary div{border-right:0;border-bottom:1px solid var(--line)}.toolbar{padding:0 12px}}@media print{
html,body{width:100%;height:auto;background:#fff}
body{margin:0}
.toolbar{display:none}
.sheet{width:100%;margin:0;border-width:4px;box-shadow:none;page-break-inside:avoid;break-inside:avoid}
.head{padding:10px 18px 8px}
.head img{width:52px;height:52px}
.head h1{margin:3px 0 2px;font-size:20px}
.head p{font-size:9px}
.head h2{margin:7px 0 0;font-size:19px}
.body{padding:12px 20px 10px}
.identity{gap:12px;margin-bottom:8px;padding:7px}
.identity-photo{width:52px;height:62px}
.identity-fallback{font-size:21px}
.identity-qr{width:58px;height:58px}
.identity-text b{font-size:15px}
.meta{gap:4px 24px;margin-bottom:10px}
.meta div{padding:4px 0;font-size:10px}
th,td{padding:7px;font-size:10px}
.summary{margin-top:10px}
.summary div{padding:8px}
.summary small{margin-bottom:2px}
.status{margin:10px 0;padding:7px}
.remarks{margin:7px 0;font-size:10px}
.foot{margin-top:22px;font-size:10px}
.sign{min-width:150px;padding-top:5px}
.disclaimer{padding:7px;font-size:8px}
.head,.status,.disclaimer{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
</style></head><body>@if($result['is_demo']??false)<div style="position:fixed;inset:42% 0 auto;z-index:9;text-align:center;font:900 72px Arial;color:#b4231825;transform:rotate(-18deg);pointer-events:none">SAMPLE / DEMO</div>@endif
<div class="toolbar"><a href="{{ session('student_portal_id') ? route('student.dashboard').'#results' : route('admin.results.index') }}">← Back to Results</a><button onclick="window.print()">Print / Save PDF</button></div>
<article class="sheet"><header class="head"><img src="{{ asset('images/mci-logo.webp') }}" alt="MCI"><h1>Micro Computer Institute</h1><p>{{ $settings['address_line'] }}, {{ $settings['city'] }}, {{ $settings['district'] }} – {{ $settings['pin'] }}</p><h2>STATEMENT OF MARKS</h2></header>
<div class="body">@php($markQr=urlencode(route('student.results.marksheet',$result['id']).'?result='.$result['result_no']))<section class="identity"><div>@if(!empty($student['photo_path']))<img class="identity-photo" src="{{ asset($student['photo_path']) }}" alt="{{ $student['student_name'] }}">@else<div class="identity-photo identity-fallback">{{ strtoupper(substr($student['student_name'],0,1)) }}</div>@endif</div><div class="identity-text"><b>{{ $student['student_name'] }}</b><span>{{ $student['roll_no'] ?? $student['application_no'] }} · {{ $student['course_code'] }}</span></div><img class="identity-qr" src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&amp;data={{ $markQr }}" alt="Marksheet QR"></section><section class="meta"><div><span>Result No.</span><b>{{ $result['result_no'] }}</b></div><div><span>Exam</span><b>{{ $result['exam_name'] }}</b></div><div><span>Student</span><b>{{ $student['student_name'] }}</b></div><div><span>Roll No.</span><b>{{ $student['roll_no'] ?? $student['application_no'] }}</b></div><div><span>Course</span><b>{{ $student['course_code'] }} — {{ $course?->title ?? '' }}</b></div><div><span>Exam Date</span><b>{{ \Carbon\Carbon::parse($result['exam_date'])->format('d M Y') }}</b></div></section>
<table><thead><tr><th>#</th><th>Subject</th><th>Maximum</th><th>Obtained</th><th>Status</th></tr></thead><tbody>@foreach($result['subjects'] as $subject)<tr><td>{{ $loop->iteration }}</td><td>{{ $subject['name'] }}</td><td>{{ number_format($subject['max_marks'],2) }}</td><td>{{ number_format($subject['obtained_marks'],2) }}</td><td>{{ strtoupper($subject['status']) }}</td></tr>@endforeach</tbody></table>
<section class="summary"><div><small>MAXIMUM MARKS</small><b>{{ number_format($result['max_total'],2) }}</b></div><div><small>MARKS OBTAINED</small><b>{{ number_format($result['obtained_total'],2) }}</b></div><div><small>PERCENTAGE</small><b>{{ number_format($result['percentage'],2) }}%</b></div><div><small>GRADE</small><b>{{ $result['grade'] }}</b></div></section>
<div class="status status-{{ $result['result_status'] }}">RESULT: {{ $result['result_status']==='pass' ? 'PASS' : 'NEEDS IMPROVEMENT' }}</div>@if($result['remarks'])<p class="remarks"><b>Remarks:</b> {{ $result['remarks'] }}</p>@endif
<footer class="foot"><span>Date of issue: {{ now()->format('d M Y') }}</span><span class="sign">Authorised Signature</span></footer></div><div class="disclaimer">Computer-generated marksheet. Subject-wise minimum passing requirement is 33%.</div></article>
</body></html>
