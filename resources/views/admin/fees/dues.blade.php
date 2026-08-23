@extends('admin.layout')
@section('title','Fee Due Report')
@section('content')
<div class="cards due-summary">
    <div class="card blue"><small>Students with Balance</small><strong>{{ count($students) }}</strong></div>
    <div class="card dark"><small>Total Outstanding</small><strong>₹{{ number_format($totalDue,2) }}</strong></div>
    <div class="card orange"><small>Partially Paid</small><strong>{{ $partialCount }}</strong></div>
    <div class="card purple"><small>Not Paid Yet</small><strong>{{ $unpaidCount }}</strong></div>
</div>

<section class="panel">
<form class="due-filter" method="get">
    <input name="search" value="{{ request('search') }}" placeholder="Student, admission no, roll no or phone...">
    <select name="course"><option value="">All Courses</option>@foreach($courses as $course)<option value="{{ $course->code }}" @selected(request('course')===$course->code)>{{ $course->code }} — {{ $course->title }}</option>@endforeach</select>
    <select name="payment_status"><option value="">All Payment Status</option><option value="unpaid" @selected(request('payment_status')==='unpaid')>Unpaid</option><option value="partial" @selected(request('payment_status')==='partial')>Partially Paid</option></select>
    <select name="age"><option value="">Any Pending Age</option><option value="30" @selected(request('age')==='30')>30+ Days</option><option value="60" @selected(request('age')==='60')>60+ Days</option><option value="90" @selected(request('age')==='90')>90+ Days</option></select>
    <button class="btn">Apply Filters</button><a href="{{ route('admin.fees.dues') }}">Clear</a>
    <a class="export-link" href="{{ route('admin.fees.dues.export',request()->query()) }}">Export CSV ↓</a>
</form>
</section>

<section class="panel">
<div class="panel-title"><div><small>FEE COLLECTION WORKSPACE</small><h2>Outstanding fee follow-up</h2></div><a href="{{ route('admin.admissions.index') }}">Record Payment →</a></div>
@if(count($students))
<div class="due-table-wrap"><table class="due-table"><thead><tr><th>Student</th><th>Course / Batch</th><th>Fee Account</th><th>Pending Age</th><th>Last Payment</th><th>Reminder</th></tr></thead><tbody>
@foreach($students as $student)
@php
$message='Namaste '.$student['student_name'].', Micro Computer Institute में आपके '.$student['course_code'].' course की ₹'.number_format($student['balance_amount'],2).' fee बाकी है। कृपया सुविधानुसार भुगतान करें। धन्यवाद।';
$phone=preg_replace('/\D+/','',$student['phone'] ?? '');
@endphp
<tr>
<td><b>{{ $student['student_name'] }}</b><small>{{ $student['application_no'] }}@if($student['roll_no'] ?? null) · {{ $student['roll_no'] }}@endif</small><small>{{ $student['phone'] }}</small></td>
<td><b>{{ $student['course_code'] }}</b><small>{{ $student['batch_name'] ?? 'Batch not assigned' }}</small></td>
<td><span>Total ₹{{ number_format($student['course_fee'],2) }}</span><span class="due-paid">Paid ₹{{ number_format($student['paid_amount'],2) }}</span><strong>Due ₹{{ number_format($student['balance_amount'],2) }}</strong></td>
<td><em class="due-age {{ $student['due_age_days'] >= 60 ? 'old' : '' }}">{{ $student['due_age_days'] }} days</em><small>{{ ucfirst($student['payment_status']) }}</small></td>
<td>{{ $student['last_payment_date'] ? \Carbon\Carbon::parse($student['last_payment_date'])->format('d M Y') : 'No payment' }}</td>
<td><a class="due-whatsapp" href="https://wa.me/{{ $phone }}?text={{ urlencode($message) }}" target="_blank" rel="noopener">WhatsApp Reminder</a><a class="due-account" href="{{ route('admin.admissions.index',['search'=>$student['application_no']]) }}">Open Fee Account</a></td>
</tr>
@endforeach
</tbody></table></div>
@else
<div class="empty"><b>No outstanding fees found</b><p>Selected filters में कोई pending fee record नहीं है।</p></div>
@endif
</section>
@endsection
