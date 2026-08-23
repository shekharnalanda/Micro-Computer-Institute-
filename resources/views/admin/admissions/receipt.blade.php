<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Fee Receipt {{ $application['receipt_no'] ?? $application['application_no'] }} · MCI</title>
<style>
:root{--ink:#07172d;--blue:#1769ff;--line:#dce4ea;--muted:#62778a}*{box-sizing:border-box}body{margin:0;background:#eef3f7;color:var(--ink);font-family:Arial,sans-serif}.toolbar{max-width:850px;margin:24px auto 12px;display:flex;justify-content:space-between;gap:12px}.toolbar a,.toolbar button{border:0;border-radius:9px;padding:11px 16px;text-decoration:none;font-weight:800;cursor:pointer}.toolbar a{background:#fff;color:var(--ink)}.toolbar button{background:var(--blue);color:#fff}.receipt{width:min(850px,calc(100% - 24px));margin:0 auto 30px;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 20px 60px #07172d1f}.head{display:flex;justify-content:space-between;align-items:center;padding:30px 36px;background:linear-gradient(120deg,#06182e,#1769ff);color:#fff}.identity{display:flex;gap:16px;align-items:center}.identity img{width:78px;height:78px;border-radius:50%;background:#fff}.identity h1{margin:0;font-size:23px}.identity p{margin:6px 0 0;color:#d8e8f5;font-size:12px}.receipt-no{text-align:right}.receipt-no small,.receipt-no b{display:block}.receipt-no small{color:#b9d3e7;font-size:10px;letter-spacing:1px}.receipt-no b{margin-top:7px;font-size:16px}.body{padding:32px 36px}.status{display:flex;justify-content:space-between;gap:14px;align-items:center;padding-bottom:22px;border-bottom:1px solid var(--line)}.status span{padding:7px 10px;border-radius:99px;background:#e4f8ee;color:#087548;font-size:11px;font-weight:900}.status time{font-size:12px;color:var(--muted)}.student{display:grid;grid-template-columns:1fr 1fr;gap:24px;padding:26px 0}.block small{display:block;color:var(--muted);font-size:9px;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px}.block b{font-size:15px}.block p{margin:5px 0;color:#52697c;font-size:12px;line-height:1.5}.fees{border:1px solid var(--line);border-radius:13px;overflow:hidden}.fees div{display:flex;justify-content:space-between;padding:15px 18px;border-bottom:1px solid var(--line)}.fees div:last-child{border:0}.fees span{color:#52697c}.fees b{font-size:16px}.fees .paid{background:#edf9f3}.fees .balance{background:#fff7e8}.note{margin-top:20px;padding:14px;border-radius:10px;background:#f4f7fa;color:#52697c;font-size:12px}.foot{display:flex;justify-content:space-between;gap:20px;margin-top:38px;padding-top:20px;border-top:1px dashed #b9c6d1;font-size:11px;color:var(--muted)}.signature{text-align:center;min-width:180px}.signature:before{content:"";display:block;border-top:1px solid var(--ink);margin-bottom:7px}.disclaimer{text-align:center;padding:17px;background:#f4f7fa;color:#718599;font-size:10px}@media(max-width:600px){.head,.status,.foot{align-items:flex-start;flex-direction:column}.student{grid-template-columns:1fr}.body,.head{padding:24px}.receipt-no{text-align:left}.toolbar{padding:0 12px}}@media print{body{background:#fff}.toolbar{display:none}.receipt{width:100%;margin:0;border-radius:0;box-shadow:none}.head{-webkit-print-color-adjust:exact;print-color-adjust:exact}.fees .paid,.fees .balance,.disclaimer{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
</head>
<body>
<div class="toolbar"><a href="{{ route('admin.admissions.index') }}">← Back to Admissions</a><button onclick="window.print()">Print / Save PDF</button></div>
<article class="receipt">
<header class="head"><div class="identity"><img src="{{ asset('images/mci-logo.webp') }}" alt="MCI"><div><h1>Micro Computer Institute</h1><p>Education · Skill Development · Career</p></div></div><div class="receipt-no"><small>FEE RECEIPT</small><b>{{ $application['receipt_no'] ?? 'PAYMENT PENDING' }}</b></div></header>
<div class="body">
<div class="status"><span>{{ strtoupper($application['payment_status']) }}</span><time>{{ \Carbon\Carbon::parse($application['updated_at'] ?? $application['created_at'])->format('d M Y, h:i A') }}</time></div>
<section class="student">
<div class="block"><small>Student / विद्यार्थी</small><b>{{ $application['student_name'] }}</b><p>Guardian: {{ $application['guardian_name'] }}<br>Phone: {{ $application['phone'] }}<br>City: {{ $application['city'] }}</p></div>
<div class="block"><small>Admission Details</small><b>{{ $application['application_no'] }}</b><p>Course: {{ $application['course_code'] }} — {{ $course?->title ?? 'Course' }}<br>Duration: {{ $course?->duration ?? '—' }}<br>Preferred Batch: {{ $application['preferred_time'] ?: '—' }}</p></div>
</section>
<section class="fees">
<div><span>Total Course Fee</span><b>₹{{ number_format((float)$application['course_fee'],2) }}</b></div>
<div class="paid"><span>Amount Paid</span><b>₹{{ number_format((float)$application['paid_amount'],2) }}</b></div>
<div class="balance"><span>Balance Due</span><b>₹{{ number_format((float)$application['balance_amount'],2) }}</b></div>
</section>
@if($application['payment_note'] ?? null)<div class="note"><b>Payment Note:</b> {{ $application['payment_note'] }}</div>@endif
<footer class="foot"><div>{{ $settings['address_line'] }}<br>{{ $settings['city'] }}, {{ $settings['district'] }}, {{ $settings['state'] }} – {{ $settings['pin'] }}<br>{{ $settings['phone'] }} · {{ $settings['email'] }}</div><div class="signature">Authorised Signature</div></footer>
</div>
<div class="disclaimer">Computer-generated fee receipt. Please retain it for future reference.</div>
</article>
</body></html>
