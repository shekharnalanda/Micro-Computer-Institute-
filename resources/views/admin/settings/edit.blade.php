@extends('admin.layout')
@section('title','Website Settings')
@section('content')
<div class="settings-intro"><div><span>⚙</span><div><h2>Manage website information</h2><p>Saved changes automatically appear on the public homepage.</p></div></div><a href="{{ route('home') }}" target="_blank">Preview Website ↗</a></div>
<form method="post" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
<div class="settings-grid">
<section class="panel settings-panel full-panel"><div class="panel-title"><div><small>HOMEPAGE HERO</small><h2>Main banner content</h2></div></div><div class="settings-form">
<label class="full">Admission Notice<input name="admission_notice" value="{{ old('admission_notice',$settings['admission_notice']) }}" required></label>
<label>Main Heading<input name="hero_title" value="{{ old('hero_title',$settings['hero_title']) }}" required></label>
<label>Highlighted Heading<input name="hero_highlight" value="{{ old('hero_highlight',$settings['hero_highlight']) }}" required></label>
<label class="full">English Introduction<textarea name="hero_text_en" rows="3" required>{{ old('hero_text_en',$settings['hero_text_en']) }}</textarea></label>
<label class="full">Hindi Introduction<textarea name="hero_text_hi" rows="3" required>{{ old('hero_text_hi',$settings['hero_text_hi']) }}</textarea></label>
<label>Second Highlight Value<input name="highlight_two_value" value="{{ old('highlight_two_value',$settings['highlight_two_value']) }}" required></label>
<label>Second Highlight Label<input name="highlight_two_label" value="{{ old('highlight_two_label',$settings['highlight_two_label']) }}" required></label>
<label>Third Highlight Value<input name="highlight_three_value" value="{{ old('highlight_three_value',$settings['highlight_three_value']) }}" required></label>
<label>Third Highlight Label<input name="highlight_three_label" value="{{ old('highlight_three_label',$settings['highlight_three_label']) }}" required></label>
</div></section>
<section class="panel settings-panel full-panel"><div class="panel-title"><div><small>WHY MCI</small><h2>Institute message</h2></div></div><div class="settings-form">
<label>Main Text<input name="why_title" value="{{ old('why_title',$settings['why_title']) }}" required></label>
<label>Highlighted Text<input name="why_highlight" value="{{ old('why_highlight',$settings['why_highlight']) }}" required></label>
<label class="full">Short Description<textarea name="why_lead" rows="2" required>{{ old('why_lead',$settings['why_lead']) }}</textarea></label>
</div></section>
<section class="panel settings-panel"><div class="panel-title"><div><small>CONTACT DETAILS</small><h2>Phone & Email</h2></div></div><div class="settings-form">
<label>Display Phone<input name="phone" value="{{ old('phone',$settings['phone']) }}" required></label>
<label>WhatsApp Number<input name="whatsapp" value="{{ old('whatsapp',$settings['whatsapp']) }}" required><small>Digits with country code, e.g. 917004773247</small></label>
<label class="full">Enquiry Email<input type="email" name="email" value="{{ old('email',$settings['email']) }}" required></label>
</div></section>
<section class="panel settings-panel"><div class="panel-title"><div><small>INSTITUTE ADDRESS</small><h2>Location</h2></div></div><div class="settings-form">
<label class="full">Address<input name="address_line" value="{{ old('address_line',$settings['address_line']) }}" required></label>
<label>City<input name="city" value="{{ old('city',$settings['city']) }}" required></label><label>District<input name="district" value="{{ old('district',$settings['district']) }}" required></label>
<label>State<input name="state" value="{{ old('state',$settings['state']) }}" required></label><label>PIN<input name="pin" value="{{ old('pin',$settings['pin']) }}" maxlength="6" required></label>
</div></section>
<section class="panel settings-panel full-panel"><div class="panel-title"><div><small>JOB SEARCH DEFAULTS</small><h2>Student Job Search</h2></div></div><div class="settings-form">
<label>Default Job Role<input name="job_role" value="{{ old('job_role',$settings['job_role']) }}" required></label><label>Default Location<input name="job_location" value="{{ old('job_location',$settings['job_location']) }}" required></label>
</div></section></div>
<div class="settings-save"><div><b>Publish these details</b><p>Save and refresh the homepage to view changes.</p></div><button class="btn">Save Website Settings</button></div></form>
@endsection
