@extends('admin.layout')
@section('title','Students Register')
@section('content')
<div class="cards student-summary">
    <div class="card blue"><small>Admitted Students</small><strong>{{ $studentCount }}</strong></div>
    <div class="card green"><small>Fees Collected</small><strong>₹{{ number_format($totalPaid,2) }}</strong></div>
    <div class="card orange"><small>Outstanding Balance</small><strong>₹{{ number_format($totalBalance,2) }}</strong></div>
</div>
<section class="panel"><form class="student-filter student-filter-expanded" method="get">
    <input name="search" value="{{ request('search') }}" placeholder="Search application no, student, guardian or phone">
    <select name="course"><option value="">All courses</option>@foreach($courses as $course)<option value="{{ $course->code }}" @selected(request('course')===$course->code)>{{ $course->code }} — {{ $course->title }}</option>@endforeach</select>
    <select name="payment_status"><option value="">All fee statuses</option>@foreach(['unpaid','partial','paid'] as $status)<option value="{{ $status }}" @selected(request('payment_status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select>
    <input name="batch" value="{{ request('batch') }}" placeholder="Batch name or timing">
    <select name="student_status"><option value="">All student statuses</option>@foreach(['active','completed','discontinued'] as $status)<option value="{{ $status }}" @selected(request('student_status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select>
    <button class="btn">Search</button><a href="{{ route('admin.students.index') }}">Clear</a>
</form></section>
<section class="panel"><div class="panel-title"><div><small>ADMITTED STUDENT RECORDS</small><h2>{{ $studentCount }} students</h2></div><div><form id="card-batch-form" method="get" action="{{ route('admin.students.cards') }}" target="_blank" style="display:inline"><button class="btn" type="submit">Print Selected ID Cards (Max 2)</button></form> <a href="{{ route('admin.admissions.index') }}">Admission workspace →</a></div></div>
@if(count($students))
<div class="student-table-wrap"><table class="student-table"><thead><tr><th>Select</th><th>Student</th><th>Application / Roll</th><th>Course & Batch</th><th>Contact</th><th>Fee Status</th><th>Balance</th><th>Actions</th></tr></thead><tbody>
@foreach($students as $student)<tr>
<td><input form="card-batch-form" type="checkbox" name="ids[]" value="{{ $student['id'] }}" aria-label="Select {{ $student['student_name'] }} for ID card"></td>
<td><b>{{ $student['student_name'] }}</b>@if($student['is_demo']??false)<span class="badge">DEMO</span>@endif<small>{{ $student['guardian_name'] }} · {{ $student['city'] }}</small><span class="academic-status status-{{ $student['student_status'] ?? 'active' }}">{{ ucfirst($student['student_status'] ?? 'active') }}</span></td>
<td><b>{{ $student['application_no'] }}</b><small>Roll: {{ $student['roll_no'] ?? 'Not assigned' }}</small><small>{{ \Carbon\Carbon::parse($student['joining_date'] ?? $student['created_at'])->format('d M Y') }}</small></td>
<td><span class="course-tag">{{ $student['course_code'] }}</span><small>{{ $student['batch_name'] ?? 'Batch not assigned' }}</small><small>{{ $student['batch_time'] ?? $student['preferred_time'] ?: 'Timing not set' }}</small></td>
<td><a href="tel:{{ preg_replace('/\s+/', '', $student['phone']) }}">{{ $student['phone'] }}</a></td>
<td><span class="student-payment payment-{{ $student['payment_status'] }}">{{ ucfirst($student['payment_status']) }}</span><small>Paid ₹{{ number_format((float)$student['paid_amount'],2) }}</small></td>
<td><b>₹{{ number_format((float)$student['balance_amount'],2) }}</b></td>
<td>
@php($studentResult = $latestResults->get($student['id']))
@php($studentCertificate = $latestCertificates->get($student['id']))
<div class="student-actions">
<button type="button" onclick="document.getElementById('documents-{{ $student['id'] }}').showModal()">Documents</button>
<button type="button" onclick="document.getElementById('student-{{ $student['id'] }}').showModal()">Edit Record</button>
</div>
<dialog class="student-record-dialog" id="documents-{{ $student['id'] }}">
<div class="dialog-head"><div><small>ONE-CLICK DOCUMENTS</small><h2>{{ $student['student_name'] }}</h2></div><button type="button" onclick="this.closest('dialog').close()">×</button></div>
<div class="student-actions document-actions">
<a href="{{ route('admin.students.card',$student['id']) }}" target="_blank">Print ID Card</a>
@if($studentResult)
<a href="{{ route('admin.results.marksheet',$studentResult['id']) }}" target="_blank">Print Marksheet</a>
@else
<a href="{{ route('admin.results.index',['search'=>$student['application_no']]) }}">Create Result First</a>
@endif
@if($studentCertificate)
<a href="{{ route('admin.certificates.print',$studentCertificate['id']) }}" target="_blank">Print Certificate</a>
@elseif($studentResult && ($studentResult['result_status'] ?? '') === 'pass')
<a href="{{ route('admin.certificates.index',['student_id'=>$student['id']]) }}">Issue Certificate</a>
@else
<span>Certificate will be available after a passing result.</span>
@endif
<a href="{{ route('admin.admissions.receipt',$student['id']) }}" target="_blank">Print Fee Receipt</a>
</div>
</dialog>
<dialog class="student-record-dialog" id="student-{{ $student['id'] }}"><div class="dialog-head"><div><small>ACADEMIC RECORD</small><h2>{{ $student['student_name'] }}</h2></div><button type="button" onclick="this.closest('dialog').close()">×</button></div>
<form class="form-grid" method="post" enctype="multipart/form-data" action="{{ route('admin.students.update',$student['id']) }}">@csrf @method('PATCH')
<label>Student Photo<input type="file" name="photo" accept=".jpg,.jpeg,.png,.webp"><small>JPG/PNG/WebP, max 2 MB</small></label>
<label>Roll Number<input name="roll_no" value="{{ $student['roll_no'] ?? '' }}" maxlength="40" placeholder="e.g. MCI-DCA-001"></label>
<label>Joining Date<input type="date" name="joining_date" value="{{ $student['joining_date'] ?? '' }}"></label>
<label>Batch Name<input name="batch_name" value="{{ $student['batch_name'] ?? '' }}" maxlength="100" placeholder="e.g. DCA Morning Batch"></label>
<label>Batch Timing<input name="batch_time" value="{{ $student['batch_time'] ?? $student['preferred_time'] ?? '' }}" maxlength="100" placeholder="e.g. 08:00 AM – 10:00 AM"></label>
<label class="full">Student Status<select name="student_status" required>@foreach(['active','completed','discontinued'] as $status)<option value="{{ $status }}" @selected(($student['student_status'] ?? 'active')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
<button class="btn full">Save Academic Record</button></form></dialog>
</td></tr>@endforeach
</tbody></table></div>
@else<div class="empty"><b>No admitted students found</b><p>Admission status को “Admitted” करने के बाद student यहाँ दिखाई देगा।</p><a href="{{ route('admin.admissions.index') }}">Open Admissions →</a></div>@endif
</section>
@endsection
