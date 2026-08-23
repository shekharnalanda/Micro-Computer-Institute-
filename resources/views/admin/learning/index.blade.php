@extends('admin.layout')
@section('title','Study Materials & Assignments')
@section('content')
@php
$typeLabels=['notes'=>'Notes / PDF','video'=>'Video Lesson','assignment'=>'Assignment','practice'=>'Practice Test','link'=>'Useful Link'];
@endphp
<div class="cards learning-summary">
    <div class="card blue"><small>Published Resources</small><strong>{{ count($resources) }}</strong></div>
    <div class="card green"><small>Visible to Students</small><strong>{{ $activeCount }}</strong></div>
    <div class="card orange"><small>Assignments</small><strong>{{ $assignmentCount }}</strong></div>
    <div class="card purple"><small>Pinned Items</small><strong>{{ collect($resources)->where('is_pinned',true)->count() }}</strong></div>
</div>

<div class="learning-admin-grid">
<section class="panel learning-entry"><div class="panel-title"><div><small>COURSE-WISE PUBLISHING</small><h2>Add a learning resource</h2></div></div>
<form method="post" action="{{ route('admin.learning.store') }}" enctype="multipart/form-data">@csrf
<div class="form-grid">
<label>Course<select name="course_code" required><option value="">Select course</option>@foreach($courses as $course)<option value="{{ $course->code }}" @selected(old('course_code')===$course->code)>{{ $course->code }} — {{ $course->title }}</option>@endforeach</select></label>
<label>Resource Type<select name="type" required>@foreach($typeLabels as $value=>$label)<option value="{{ $value }}" @selected(old('type','notes')===$value)>{{ $label }}</option>@endforeach</select></label>
<label class="learning-wide">Title<input name="title" value="{{ old('title') }}" maxlength="180" placeholder="MS Word Chapter 1 Notes" required></label>
<label class="learning-wide">Resource URL (PDF upload करने पर खाली छोड़ें)<input type="url" name="link_url" value="{{ old('link_url') }}" maxlength="1000" placeholder="https://drive.google.com/..., YouTube or website link"></label>
<label class="learning-wide">या PDF Upload (अधिकतम 25 MB)<input type="file" name="material_file" accept="application/pdf,.pdf"><small>PDF या Resource URL में से कोई एक देना जरूरी है।</small></label>
<label>Due Date (optional)<input type="date" name="due_date" value="{{ old('due_date') }}"></label>
<label class="learning-pin"><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned'))><span>Pin this resource at the top</span></label>
<label class="learning-wide">Instructions / Description<textarea name="description" rows="4" maxlength="1000" placeholder="What students should read, watch or submit...">{{ old('description') }}</textarea></label>
</div><button class="btn">Publish to Student Portal</button>
</form></section>

<section class="panel learning-list-panel">
<form class="learning-filter" method="get"><input name="search" value="{{ request('search') }}" placeholder="Search title, course or description..."><select name="course"><option value="">All Courses</option>@foreach($courses as $course)<option value="{{ $course->code }}" @selected(request('course')===$course->code)>{{ $course->code }}</option>@endforeach</select><select name="type"><option value="">All Types</option>@foreach($typeLabels as $value=>$label)<option value="{{ $value }}" @selected(request('type')===$value)>{{ $label }}</option>@endforeach</select><button class="soft-btn">Search</button><a href="{{ route('admin.learning.index') }}">Clear</a></form>
@if(count($resources))<div class="learning-records">@foreach($resources as $resource)<article class="{{ ($resource['is_active'] ?? true) ? '' : 'resource-hidden' }}">
<div class="resource-type type-{{ $resource['type'] }}"><span>{{ $resource['is_pinned'] ? '★' : '▰' }}</span><small>{{ $typeLabels[$resource['type']] ?? ucfirst($resource['type']) }}</small></div>
<div><div class="resource-meta"><b>{{ $resource['course_code'] }}</b><span class="visibility-{{ ($resource['is_active'] ?? true) ? 'on' : 'off' }}">{{ ($resource['is_active'] ?? true) ? 'Visible' : 'Hidden' }}</span>@if($resource['due_date'])<span>Due {{ \Carbon\Carbon::parse($resource['due_date'])->format('d M Y') }}</span>@endif</div><h3>{{ $resource['title'] }}</h3><p>{{ $resource['description'] ?: 'No additional instructions.' }}</p>@if(!empty($resource['file_path']) || !empty($resource['link_url']))<a class="resource-open" href="{{ !empty($resource['file_path']) ? route('admin.learning.download',$resource['id']) : $resource['link_url'] }}" target="_blank" rel="noopener noreferrer">{{ !empty($resource['file_path']) ? 'Download PDF ↓' : 'Open Resource ↗' }}</a>@endif</div>
<div class="learning-actions"><form method="post" action="{{ route('admin.learning.toggle',$resource['id']) }}">@csrf @method('PATCH')<button>{{ ($resource['is_active'] ?? true) ? 'Hide' : 'Show' }}</button></form><form method="post" action="{{ route('admin.learning.destroy',$resource['id']) }}" onsubmit="return confirm('Delete this learning resource permanently?')">@csrf @method('DELETE')<button class="danger">Delete</button></form></div>
</article>@endforeach</div>
@else<div class="empty"><b>No learning resources found</b><p>Publish course-wise notes, videos and assignments using the form.</p></div>@endif
</section></div>
@endsection
