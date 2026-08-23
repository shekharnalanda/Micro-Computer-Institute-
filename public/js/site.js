const defaultCourses = [
  {code:"DCA",title:"Diploma in Computer Applications",hi:"कंप्यूटर एप्लीकेशन डिप्लोमा",duration:"6 Months",level:"Foundation",summary:"MS Office · Internet · Typing · Digital Services",eligibility:"10th pass or equivalent",modules:["Computer fundamentals & Windows","MS Word document creation","MS Excel spreadsheets","PowerPoint presentations","Internet, email & cyber safety","Hindi/English typing & digital services"],careers:["Computer Operator","Office Assistant","Data Entry Operator"]},
  {code:"ADCA",title:"Advanced Diploma in Computer Applications",hi:"एडवांस कंप्यूटर एप्लीकेशन",duration:"12 Months",level:"Career",summary:"Advanced Office · Tally · DTP · Web Basics",eligibility:"10th/12th pass",modules:["Advanced MS Office","Advanced Excel and MIS","Tally Prime with GST","DTP and graphic design","Web design fundamentals","Projects, typing and viva"],careers:["Office Executive","Accounts Assistant","Computer Faculty"]},
  {code:"CCC",title:"Course on Computer Concepts",hi:"कंप्यूटर कॉन्सेप्ट कोर्स",duration:"3 Months",level:"Foundation",summary:"Digital Literacy · Office Tools · Email · Cyber Safety",eligibility:"Open to all learners",modules:["Computer and OS basics","Word processing","Spreadsheets and presentations","Internet and email","Digital payments and e-governance","Cyber security awareness"],careers:["Digital Service Assistant","Office Support","Entry-level Operator"]},
  {code:"TALLY",title:"Tally Prime with GST",hi:"टैली प्राइम एवं जीएसटी",duration:"3–6 Months",level:"Job-ready",summary:"Accounting · Inventory · GST · Payroll · Reports",eligibility:"10th/12th pass",modules:["Accounting fundamentals","Company and ledger creation","Vouchers and inventory","GST billing and returns basics","Banking, payroll and TDS","Reports and assignments"],careers:["Tally Operator","Accounts Assistant","Billing Executive"]},
  {code:"EXCEL",title:"Advanced Excel & MIS",hi:"एडवांस एक्सेल और MIS",duration:"2 Months",level:"Job-ready",summary:"Formulas · Dashboards · Pivot Tables · MIS Reports",eligibility:"Basic computer knowledge",modules:["Advanced formulas","Data cleaning and validation","Pivot Tables and Charts","Dashboards","Lookup and logical functions","MIS reports"],careers:["MIS Executive","Data Analyst Trainee","Back Office Executive"]},
  {code:"DTP",title:"DTP & Graphic Design",hi:"डीटीपी एवं ग्राफिक डिजाइन",duration:"6 Months",level:"Creative",summary:"Photoshop · CorelDRAW · Page Layout · Branding",eligibility:"10th pass",modules:["Design principles and colour","Adobe Photoshop","CorelDRAW","Page layout and typography","Social media creatives","Portfolio projects"],careers:["Graphic Designer","DTP Operator","Social Media Designer"]},
  {code:"WEB",title:"Web Design & Development",hi:"वेब डिजाइन एवं डेवलपमेंट",duration:"6 Months",level:"Technical",summary:"HTML · CSS · JavaScript · Responsive Projects",eligibility:"10th/12th pass",modules:["HTML5 structure","CSS3 responsive design","JavaScript fundamentals","Forms and UI components","Publishing basics","Portfolio projects"],careers:["Junior Web Designer","Front-end Trainee","Website Assistant"]},
  {code:"PYTHON",title:"Python Programming",hi:"पायथन प्रोग्रामिंग",duration:"4 Months",level:"Technical",summary:"Logic · Python · Automation · Data · Projects",eligibility:"12th pass recommended",modules:["Programming logic","Python syntax and data types","Conditions, loops and functions","Lists and files","Automation and data basics","Mini projects"],careers:["Python Trainee","Automation Assistant","Programming Intern"]},
  {code:"DIGITAL",title:"Digital Marketing",hi:"डिजिटल मार्केटिंग",duration:"3 Months",level:"Career",summary:"SEO · Social Media · Content · Ads · Analytics",eligibility:"10th/12th pass",modules:["Digital marketing fundamentals","SEO and keywords","Social media marketing","Content workflow","Online advertising","Analytics project"],careers:["Digital Marketing Executive","SEO Trainee","Social Media Assistant"]},
  {code:"HARDWARE",title:"Hardware & Networking",hi:"हार्डवेयर एवं नेटवर्किंग",duration:"6 Months",level:"Technical",summary:"PC Assembly · OS · LAN · Routers · Security",eligibility:"10th/12th pass",modules:["PC components and assembly","OS installation","Drivers and troubleshooting","LAN and IP basics","Router and Wi-Fi setup","Maintenance and security"],careers:["Hardware Technician","Desktop Support Trainee","Network Assistant"]},
  {code:"AI",title:"AI Tools for Study & Work",hi:"पढ़ाई और काम के लिए AI Tools",duration:"1 Month",level:"Future Skill",summary:"Prompting · Research · Productivity · Responsible AI",eligibility:"Basic computer knowledge",modules:["AI fundamentals","Effective prompting","Research and summarisation","Documents and presentations","Creative workflows","Responsible AI"],careers:["AI-enabled Office Assistant","Content Assistant","Productivity Specialist"]},
  {code:"DATA",title:"Data Entry & Office Assistant",hi:"डेटा एंट्री एवं ऑफिस असिस्टेंट",duration:"3 Months",level:"Job-ready",summary:"Typing · Documents · Spreadsheets · Communication",eligibility:"10th pass",modules:["English and Hindi typing","Document formatting","Spreadsheet data entry","Email and file management","Office communication","Accuracy tests"],careers:["Data Entry Operator","Office Assistant","Back Office Executive"]}
];
const courses = Array.isArray(window.MCI_COURSES) && window.MCI_COURSES.length ? window.MCI_COURSES : defaultCourses;

function feeLabel(course){return course.fee===null||course.fee===undefined||course.fee===""?"Fee on enquiry":"₹"+Number(course.fee).toLocaleString("en-IN",{minimumFractionDigits:2,maximumFractionDigits:2})}
const levels=["All","Foundation","Job-ready","Career","Technical","Creative","Future Skill"];
const grid=document.getElementById("courseGrid"),filters=document.getElementById("filters"),modal=document.getElementById("courseModal");

function renderFilters(active="All"){
  filters.innerHTML=levels.map(x=>`<button class="${x===active?'active':''}" data-filter="${x}">${x}</button>`).join("");
  filters.querySelectorAll("button").forEach(b=>b.addEventListener("click",()=>{renderFilters(b.dataset.filter);renderCourses(b.dataset.filter)}));
}
function renderCourses(level="All"){
  const list=level==="All"?courses:courses.filter(c=>c.level===level);
  grid.innerHTML=list.map((c,i)=>`<article class="course"><div class="course-top"><span>${String(i+1).padStart(2,"0")}</span><b>${c.level}</b></div><small>${c.code} · ${c.duration}</small><div class="course-fee">${feeLabel(c)}</div><h3>${c.title}</h3><h4>${c.hi}</h4><p>${c.summary}</p><button class="course-link" data-code="${c.code}">View course details <span>↗</span></button></article>`).join("");
  grid.querySelectorAll(".course-link").forEach(b=>b.addEventListener("click",()=>openCourse(b.dataset.code)));
}
function openCourse(code){
  const c=courses.find(x=>x.code===code); if(!c)return;
  document.getElementById("modalHead").innerHTML=`<span>${c.level} · ${c.code}</span><h2 id="courseTitle">${c.title}</h2><h3>${c.hi}</h3><p>Practical, career-focused training with guided lab work and useful assignments.</p><p class="modal-hi">व्यावहारिक लैब प्रशिक्षण, assignments और career guidance के साथ सीखें।</p>`;
  document.getElementById("courseFacts").innerHTML=`<div><small>Duration · अवधि</small><b>${c.duration}</b></div><div><small>Eligibility · योग्यता</small><b>${c.eligibility}</b></div><div><small>Course Fee · शुल्क</small><b>${feeLabel(c)}${c.fee_note?`<small class="fee-note">${c.fee_note}</small>`:""}</b></div>`;
  document.getElementById("detailColumns").innerHTML=`<div><h4>What you will learn · क्या सीखेंगे</h4><ul>${c.modules.map(x=>`<li>✓ <span>${x}</span></li>`).join("")}</ul></div><div><h4>Career opportunities · रोजगार</h4><ul>${c.careers.map(x=>`<li>→ <span>${x}</span></li>`).join("")}</ul><div class="detail-note"><b>Certificate & assessment</b><p>Course completion assessment and certificate support included.</p></div></div>`;
  document.getElementById("courseSelect").value=c.code; modal.hidden=false; document.body.style.overflow="hidden"; document.getElementById("modalClose").focus();
}
function closeModal(){modal.hidden=true;document.body.style.overflow=""}
document.getElementById("modalClose").addEventListener("click",closeModal);document.getElementById("modalBack").addEventListener("click",closeModal);document.getElementById("modalEnquire").addEventListener("click",closeModal);modal.addEventListener("click",e=>{if(e.target===modal)closeModal()});document.addEventListener("keydown",e=>{if(e.key==="Escape")closeModal()});

const courseSelect=document.getElementById("courseSelect");courses.forEach(c=>courseSelect.add(new Option(`${c.code} — ${c.title} — ${feeLabel(c)}`,c.code)));
function updateJobs(){const role=encodeURIComponent(document.getElementById("jobRole").value||"Computer Operator"),loc=encodeURIComponent(document.getElementById("jobLocation").value||"Bihar");document.getElementById("linkedinSearch").href=`https://www.linkedin.com/jobs/search/?keywords=${role}&location=${loc}`;document.getElementById("indeedSearch").href=`https://in.indeed.com/jobs?q=${role}&l=${loc}`}
document.getElementById("jobRole").addEventListener("input",updateJobs);document.getElementById("jobLocation").addEventListener("input",updateJobs);

const form=document.getElementById("enquiryForm"),message=document.getElementById("formMessage");document.getElementById("formToken").value=Date.now().toString();
form.addEventListener("submit",async e=>{e.preventDefault();const button=form.querySelector("button[type=submit]");button.disabled=true;button.textContent="Sending…";message.className="form-message";try{const response=await fetch(form.action,{method:"POST",body:new FormData(form),headers:{"X-Requested-With":"XMLHttpRequest"}});const data=await response.json();message.textContent=data.message||"Thank you. Your enquiry has been sent.";message.className=`form-message show ${data.success?'success':'error'}`;if(data.success){form.reset();document.getElementById("formToken").value=Date.now().toString();}}catch(err){message.textContent="Enquiry could not be sent. Please call or WhatsApp us.";message.className="form-message show error"}finally{button.disabled=false;button.textContent="Send Enquiry / पूछताछ भेजें ↗"}});

renderFilters();renderCourses();updateJobs();

const galleryBox=document.getElementById("galleryLightbox");
if(galleryBox){
  const galleryImage=document.getElementById("galleryLightboxImage"),galleryTitle=document.getElementById("galleryLightboxTitle"),galleryCaption=document.getElementById("galleryLightboxCaption");
  const closeGallery=()=>{galleryBox.hidden=true;document.body.style.overflow=""};
  document.querySelectorAll(".gallery-public-item").forEach(item=>item.addEventListener("click",()=>{galleryImage.src=item.dataset.gallerySrc;galleryImage.alt=item.dataset.galleryTitle||"";galleryTitle.textContent=item.dataset.galleryTitle||"";galleryCaption.textContent=item.dataset.galleryCaption||"";galleryBox.hidden=false;document.body.style.overflow="hidden"}));
  galleryBox.querySelector(".gallery-lightbox-close").addEventListener("click",closeGallery);
  galleryBox.addEventListener("click",event=>{if(event.target===galleryBox)closeGallery()});
  document.addEventListener("keydown",event=>{if(event.key==="Escape"&&!galleryBox.hidden)closeGallery()});
}

const jobBoardSearch=document.getElementById("jobBoardSearch"),jobBoardLocation=document.getElementById("jobBoardLocation");
if(jobBoardSearch&&jobBoardLocation){
  const jobCards=[...document.querySelectorAll(".opportunity-card")],jobCount=document.getElementById("jobBoardCount"),jobEmpty=document.getElementById("jobBoardEmpty");
  const filterJobBoard=()=>{const query=jobBoardSearch.value.trim().toLowerCase(),location=jobBoardLocation.value.trim().toLowerCase();let visible=0;jobCards.forEach(card=>{const show=(!query||card.dataset.jobSearch.includes(query))&&(!location||card.dataset.jobLocation.includes(location));card.hidden=!show;if(show)visible++});jobCount.textContent=visible+" jobs found";jobEmpty.hidden=visible!==0};
  jobBoardSearch.addEventListener("input",filterJobBoard);jobBoardLocation.addEventListener("input",filterJobBoard);
}
