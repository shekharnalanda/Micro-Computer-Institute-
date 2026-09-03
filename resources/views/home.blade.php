<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Micro Computer Institute — practical bilingual computer courses, skill development and career support in Bihar Sharif, Nalanda."><title>Micro Computer Institute</title>
<link rel="canonical" href="https://mciedu.com/">
<meta property="og:type" content="website">
<meta property="og:title" content="Micro Computer Institute">
<meta property="og:description" content="Micro Computer Institute — practical bilingual computer courses, skill development and career support in Bihar Sharif, Nalanda.">
<meta property="og:url" content="https://mciedu.com/">
<meta property="og:image" content="https://mciedu.com/images/mci-logo.webp">
<meta property="og:site_name" content="Micro Computer Institute">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="Micro Computer Institute">
<meta name="twitter:description" content="Micro Computer Institute — practical bilingual computer courses, skill development and career support in Bihar Sharif, Nalanda.">
<meta name="twitter:image" content="https://mciedu.com/images/mci-logo.webp"><link rel="stylesheet" href="{{ asset('css/site.css') }}?v=mci-hero-crop-v1">@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "Micro Computer Institute",
  "url": "https://mciedu.com/",
  "logo": "https://mciedu.com/images/mci-logo.webp",
  "description": "Micro Computer Institute offers practical bilingual computer courses, skill development and career support in Bihar Sharif, Nalanda.",
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "Bihar Sharif",
    "addressRegion": "Bihar",
    "postalCode": "803101",
    "addressCountry": "IN"
  }
}
</script>
@endverbatim

<style id="MCI_FINAL_HERO_CROP_V2">
.hero{min-height:auto!important;padding-top:38px!important;padding-bottom:38px!important}
.hero-media{height:320px!important;overflow:hidden!important;border-radius:110px 20px 110px 20px!important}
.hero-media>img{display:block!important;width:100%!important;height:100%!important;object-fit:cover!important;object-position:center 48%!important}
@media(max-width:1100px){.hero-media{height:285px!important}}
@media(max-width:760px){.hero{padding-top:30px!important;padding-bottom:30px!important}.hero-media{height:220px!important;border-radius:65px 12px 65px 12px!important}}
@media(max-width:480px){.hero-media{height:190px!important}}
</style>
</head><body><main>
<header class="site-header"><a class="brand" href="#home"><img class="brand-logo" src="{{ asset('images/mci-logo.webp') }}" alt="MCI logo"><span><strong>Micro Computer</strong><small>Institute</small></span></a><button class="mobile-menu-toggle" id="mobileMenuToggle" type="button" aria-expanded="false" aria-controls="siteNavigation" aria-label="Open navigation"><span></span><span></span><span></span></button><nav id="siteNavigation"><a href="#home">Home</a><a href="#courses">Courses</a><a href="#notices">Notices</a><a href="#gallery">Gallery</a><a href="#jobs">Job Search</a><a href="{{ route('certificates.verify') }}">Verify Certificate</a><a href="{{ route('student.login') }}">Student Login</a><a href="#enquiry">Enquiry</a><a class="admin-nav-link" href="{{ route('admin.login') }}">Admin Login</a></nav><a class="pill" href="{{ route('admission.create') }}">Apply Online ↗</a></header>
<section class="hero" id="home"><div class="hero-copy"><div class="eyebrow">● {{ $settings['admission_notice'] }}</div><h1>{{ $settings['hero_title'] }}<br><em>{{ $settings['hero_highlight'] }}</em></h1><p>{{ $settings['hero_text_en'] }}</p><p class="hi">{{ $settings['hero_text_hi'] }}</p><div class="actions"><a class="primary" href="#courses">Explore Courses →</a><a class="secondary" href="#jobs">⌕ Find Jobs</a></div><div class="proof"><div><strong>{{ $courses->count() }}+</strong><span>Career Courses</span></div><div><strong>{{ $settings['highlight_two_value'] }}</strong><span>{{ $settings['highlight_two_label'] }}</span></div><div><strong>{{ $settings['highlight_three_value'] }}</strong><span>{{ $settings['highlight_three_label'] }}</span></div></div></div><div class="hero-media"><img src="{{ asset('images/hero-computer-lab.webp') }}?v=mci-full-banner-v4" alt="Students learning in a computer lab"><div class="float top">● <span><b>Practical First</b><small>Learn by doing</small></span></div><div class="float bottom">⌁ <span><b>Career Support</b><small>Skills to opportunities</small></span></div></div></section>
<div class="trust"><span>MS Office</span><i>✦</i><span>Tally Prime</span><i>✦</i><span>Graphic Design</span><i>✦</i><span>Web Development</span><i>✦</i><span>Python</span><i>✦</i><span>AI Tools</span></div>
@if(count($notices))
<section class="section public-notices" id="notices"><div class="heading split"><div><span class="kicker">Latest Notices · नवीन सूचना</span><h2>Stay informed.<br><em>आगे बढ़ते रहें।</em></h2></div><p>Admissions, course updates, events and important announcements from MCI.</p></div>
<div class="notice-public-grid">@foreach(array_slice($notices,0,6) as $notice)<article class="notice-public-card type-border-{{ $notice['type'] }}"><div class="notice-public-top"><span class="notice-public-type">{{ strtoupper($notice['type']) }}</span><time>{{ \Carbon\Carbon::parse($notice['notice_date'])->format('d M Y') }}</time></div><h3>{{ $notice['title'] }}</h3>@if($notice['title_hi'])<h4>{{ $notice['title_hi'] }}</h4>@endif @if($notice['description'])<p>{{ $notice['description'] }}</p>@endif @if($notice['link'])<a href="{{ $notice['link'] }}" target="_blank" rel="noopener">View details ↗</a>@endif @if($notice['is_pinned'])<b class="public-pin">★ Important</b>@endif</article>@endforeach</div>
</section>
@endif
@if(count($gallery))
<section class="section public-gallery" id="gallery"><div class="heading split"><div><span class="kicker">MCI Gallery · हमारी गतिविधियाँ</span><h2>Learning in action.<br><em>देखिए हमारा परिसर।</em></h2></div><p>Computer lab, classroom activities and student learning moments from MCI.</p></div>
<div class="gallery-public-grid">@foreach($gallery as $item)<button class="gallery-public-item" type="button" data-gallery-src="{{ asset($item['path']) }}" data-gallery-title="{{ $item['title'] }}" data-gallery-caption="{{ $item['caption'] }}"><img src="{{ asset($item['path']) }}" alt="{{ $item['title'] }}" loading="lazy"><span><b>{{ $item['title'] }}</b>@if($item['caption'])<small>{{ $item['caption'] }}</small>@endif</span></button>@endforeach</div>
</section>
@endif
<section class="section" id="courses"><div class="heading split"><div><span class="kicker">Programs · पाठ्यक्रम</span><h2>Courses built for<br><em>real-world success.</em></h2></div><p>Courses are managed from the secure Admin panel and displayed here automatically.</p></div><div class="filters" id="filters"></div><div class="course-grid" id="courseGrid"></div></section>
<section class="section lab" id="why"><div class="lab-photo"><img src="{{ asset('images/courses-computer-lab.webp') }}" alt="Hands-on computer training"><div><b>Learn.</b><b>Practice.</b><b>Grow.</b></div></div><div><span class="kicker">Why MCI · हमारी विशेषता</span><h2>{{ $settings['why_title'] }}<em>{{ $settings['why_highlight'] }}</em></h2><p class="lead">{{ $settings['why_lead'] }}</p><ul class="features"><li><span>01</span><div><b>Modern computer lab</b><p>Individual practice with guided support.</p></div></li><li><span>02</span><div><b>Bilingual teaching</b><p>Clear instruction in English and Hindi.</p></div></li><li><span>03</span><div><b>Projects & assessments</b><p>Practical assignments that show progress.</p></div></li><li><span>04</span><div><b>Career preparation</b><p>Resume, interview and job-search guidance.</p></div></li></ul></div></section>
<section class="section jobs" id="jobs"><div class="job-intro"><span class="kicker light">Career Desk · रोजगार सहायता</span><h2>Turn your skills<br>into your <em>next job.</em></h2><p>Search trusted job portals by role and location.</p><img src="{{ asset('images/job-career.webp') }}" alt="Graduate preparing for a job"><div class="safety"><b>✓</b><p><strong>Job safety first</strong><br>Never pay for an interview or job offer.</p></div></div><div class="job-panel"><div class="panel-head"><b>Job Search / नौकरी खोजें</b><span>✓ Verified portals</span></div><div class="job-fields"><label>Job role / पद<input id="jobRole" value="{{ $settings['job_role'] }}"></label><label>Location / स्थान<input id="jobLocation" value="{{ $settings['job_location'] }}"></label></div><div class="smart"><a id="linkedinSearch" target="_blank" rel="noopener">Search LinkedIn ↗</a><a id="indeedSearch" target="_blank" rel="noopener">Search Indeed ↗</a></div><div class="portals"><a href="https://www.ncs.gov.in/" target="_blank" rel="noopener"><span class="portal-logo navy">NCS</span><span><b>National Career Service</b><small>Government career portal</small></span><strong>↗</strong></a><a href="https://www.naukri.com/" target="_blank" rel="noopener"><span class="portal-logo orange">N</span><span><b>Naukri.com</b><small>Private-sector openings</small></span><strong>↗</strong></a><a href="https://apna.co/" target="_blank" rel="noopener"><span class="portal-logo teal">A</span><span><b>Apna</b><small>Local and entry-level roles</small></span><strong>↗</strong></a></div></div></section>
@if(count($jobs))
<section class="section opportunity-board" id="opportunities"><div class="heading split"><div><span class="kicker">Verified Openings · रोजगार अवसर</span><h2>Current opportunities.<br><em>Find your next step.</em></h2></div><p>Admin-verified openings with direct official application links. Never pay for an interview or job offer.</p></div>
<div class="opportunity-filters"><label>Search role or company<input id="jobBoardSearch" placeholder="Computer Operator, Tally, company..."></label><label>Filter location<input id="jobBoardLocation" placeholder="Bihar Sharif, Patna, Remote..."></label><span id="jobBoardCount">{{ count($jobs) }} jobs found</span></div>
<div class="opportunity-grid" id="opportunityGrid">@foreach($jobs as $job)<article class="opportunity-card" data-job-search="{{ strtolower($job['title'].' '.$job['company'].' '.$job['qualification']) }}" data-job-location="{{ strtolower($job['location']) }}"><div class="opportunity-top"><span>{{ $job['job_type'] }}</span>@if($job['is_verified'])<b>✓ VERIFIED</b>@endif</div><h3>{{ $job['title'] }}</h3><h4>{{ $job['company'] }}</h4><div class="opportunity-meta"><span>⌖ {{ $job['location'] }}</span>@if($job['salary'])<span>₹ {{ $job['salary'] }}</span>@endif</div>@if($job['description'])<p>{{ $job['description'] }}</p>@endif<div class="opportunity-foot"><small>{{ $job['qualification'] ?: 'Open qualification' }}@if($job['deadline'])<br>Apply by {{ \Carbon\Carbon::parse($job['deadline'])->format('d M Y') }}@endif</small><a href="{{ $job['apply_url'] }}" target="_blank" rel="noopener">Apply safely ↗</a></div></article>@endforeach</div>
<div class="job-board-empty" id="jobBoardEmpty" hidden>No matching jobs found. Try another role or location.</div>
</section>
@endif
<section class="admission-promo"><div><span>ONLINE ADMISSION · ऑनलाइन प्रवेश</span><h2>Ready to learn a new skill?</h2><p>Choose your course and submit your admission application online. You will receive a unique application number immediately.</p></div><div class="admission-promo-steps"><span><b>1</b>Fill details</span><span><b>2</b>Team verification</span><span><b>3</b>Admission confirmation</span></div><a href="{{ route('admission.create') }}">Apply for Admission ↗</a></section>
<section class="section enquiry" id="enquiry"><div class="enquiry-wrap"><div class="enquiry-copy"><span class="kicker">Admission enquiry · प्रवेश पूछताछ</span><h2>Start your learning journey.</h2><p>अपना विवरण भेजें। हमारी टीम course, fees और admission के संबंध में आपसे संपर्क करेगी।</p></div><form class="enquiry-form" id="enquiryForm" action="{{ route('enquiry.store') }}" method="post">@csrf<label>Student name / नाम<input name="name" required maxlength="80"></label><label>Mobile / मोबाइल<input name="phone" required maxlength="15" pattern="[0-9+ -]{10,15}"></label><label>Email<input type="email" name="email" maxlength="120"></label><label>City / शहर<input name="city" value="{{ $settings['city'] }}"></label><label class="full">Course<select name="course" id="courseSelect" required><option value="">Select a course</option></select></label><label class="full">Message<textarea name="message" maxlength="1000"></textarea></label><label class="hp">Leave empty<input name="website" tabindex="-1"></label><input type="hidden" name="form_token" id="formToken"><div class="form-message" id="formMessage" role="status"></div><button type="submit">Send Enquiry / पूछताछ भेजें ↗</button><small>Your enquiry is saved securely and emailed to our team.</small></form></div></section>
<section class="section contact" id="contact"><div><span class="kicker light">Visit MCI</span><h2>Ready to upgrade<br>your digital future?</h2></div><div class="contact-card"><a href="tel:{{ preg_replace('/\\s+/', '', $settings['phone']) }}"><span>Call</span><b>{{ $settings['phone'] }}</b></a><a href="mailto:{{ $settings['email'] }}"><span>Email</span><b>{{ $settings['email'] }}</b></a><a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($settings['address_line'].', '.$settings['city'].', '.$settings['district'].', '.$settings['state'].' '.$settings['pin']) }}" target="_blank" rel="noopener"><span>Visit</span><b>{{ $settings['address_line'] }}<br>{{ $settings['city'] }}, {{ $settings['district'] }}<br>{{ $settings['state'] }} – {{ $settings['pin'] }}</b></a><a class="contact-btn" href="https://wa.me/{{ $settings['whatsapp'] }}" target="_blank" rel="noopener">Chat on WhatsApp ↗</a></div></section>
<footer><div class="footer-brand"><img class="footer-logo" src="{{ asset('images/mci-logo.webp') }}" alt="MCI logo"><div><b>Micro Computer Institute</b><p>Skills for today. Confidence for tomorrow.</p></div></div><div class="footer-links"><a href="#courses">Courses</a><a href="{{ route('admission.create') }}">Online Admission</a><a href="#jobs">Job Search</a><a href="{{ route('certificates.verify') }}">Verify Certificate</a><a href="{{ route('student.login') }}">Student Login</a><a href="#enquiry">Enquiry</a><a href="{{ route('admin.login') }}">Admin</a></div><small>© {{ date('Y') }} Micro Computer Institute.</small></footer>
</main><div class="gallery-lightbox" id="galleryLightbox" hidden><button type="button" class="gallery-lightbox-close" aria-label="Close image">×</button><figure><img id="galleryLightboxImage" alt=""><figcaption><b id="galleryLightboxTitle"></b><span id="galleryLightboxCaption"></span></figcaption></figure></div><div class="course-modal" id="courseModal" role="dialog" aria-modal="true" aria-labelledby="courseTitle" hidden><div class="course-dialog"><button class="modal-close" id="modalClose">×</button><div class="modal-head" id="modalHead"></div><div class="course-facts" id="courseFacts"></div><div class="detail-columns" id="detailColumns"></div><div class="modal-actions"><a href="#enquiry" id="modalEnquire">Enquire for admission ↗</a><button id="modalBack">Back to courses</button></div></div></div>
@php
$mciCourses = $courses->map(function ($course) {
    return [
        'code' => $course->code,
        'title' => $course->title,
        'hi' => $course->title_hi,
        'duration' => $course->duration,
        'fee' => $course->fee_amount !== null ? (float) $course->fee_amount : null,
        'fee_note' => $course->fee_note,
        'level' => $course->level,
        'summary' => $course->summary,
        'eligibility' => $course->eligibility,
        'modules' => $course->modules ?: [],
        'careers' => $course->careers ?: [],
    ];
})->values();
@endphp
<script>window.MCI_COURSES = @json($mciCourses);</script><script src="{{ asset('js/navigation.js') }}"></script><script src="{{ asset('js/site.js') }}"></script></body></html>
