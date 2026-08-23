<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Support\LearningResourceStore;
use Illuminate\Console\Command;

class InstallCourseAssignments extends Command
{
    protected $signature='mci:install-course-assignments';
    protected $description='Publish three ready-made practical assignments for every MCI course';

    public function handle(): int
    {
        $assignments=[
            'ADCA'=>[['Integrated Office Portfolio','Create an admission register, mail-merged confirmation letters and a five-slide MIS summary using one common Student ID.'],['Database and Accounts Project','Design related Students/Courses tables and enter ten accounting transactions including one local GST sale. Submit schema, reports and screenshots.'],['ADCA Capstone','Build a small institute fee-management solution with master data, receipts, balance report, dashboard, testing record and backup instructions.']],
            'EXCEL'=>[['Formula and Lookup Workbook','Create a 50-row sales table using validation, SUMIFS, COUNTIFS and XLOOKUP. Include error checks and meaningful named sections.'],['MIS Dashboard','Build a branch-month dashboard using PivotTables, charts, slicers and four KPI cards. Submit source file and PDF screenshot report.'],['Workbook Audit','Add reconciliation totals, formula protection, validation and an audit sheet to an existing workbook; describe five errors prevented.']],
            'AI'=>[['Prompt Improvement Log','Create three prompts for study, office drafting and analysis. Submit first output, revised prompt, improved output and verification notes.'],['Fact-checking Assignment','Choose an AI answer with five factual claims and prepare a claim-source-verdict table using authoritative sources.'],['Responsible AI Workflow','Build a privacy-safe AI-assisted office or study workflow. Document inputs, human corrections, risks, disclosure and final quality checklist.']],
            'CCC'=>[['Digital Office Pack','Prepare one formatted notice, one expense spreadsheet and a three-slide presentation; organize source and PDF files in named folders.'],['Internet and Email Task','Find one current official public-service notice, record source/date and draft a professional email with the correctly named attachment.'],['Cyber Safety Guide','Create a bilingual ten-point family guide covering passwords, MFA, OTP, phishing, updates, payments and backups.']],
            'DATA'=>[['Accurate Data Register','Enter and validate 40 admission records using unique IDs, date format, phone rules and course dropdown; calculate and report accuracy.'],['Mail Merge Office Letters','Create a joining-letter template and merge five fictional student records. Submit source, data sheet and final PDFs.'],['Enquiry MIS','Build a monthly enquiry register and report total, converted, pending, course-wise conversion rate and one chart.']],
            'DIGITAL'=>[['Marketing Funnel Plan','Create awareness, consideration, admission and retention content for a local computer course with audience and CTA.'],['SEO Content Brief','Prepare keyword intent, page outline, title, description, headings, FAQs and internal links for a local course landing page.'],['Campaign Analytics','Use fictional campaign data to calculate CTR, CPC, lead conversion and CPL; provide a one-page recommendation and next A/B test.']],
            'DCA'=>[['Professional Document Portfolio','Create a two-page brochure, official notice and mail-merged joining letter using styles, tables, footer and print-ready PDF.'],['Fees Spreadsheet','Build a 25-student fee register with validation, balance formula, due highlighting, SUMIFS summary and chart.'],['Digital Office Final Project','Combine an admission form, student register, fee report and orientation presentation with testing and handover notes.']],
            'DTP'=>[['Course Promotion Design','Design an A4 flyer using grid, hierarchy, typography, three benefits and one admission CTA; submit sketch, source and print PDF.'],['Brand Mini Kit','Create a vector logo treatment, three-colour palette, typography specimen, business card and social-media post.'],['Print Production Project','Prepare a brochure with correct size, bleed, safe margin, image resolution and colour settings; attach completed preflight checklist.']],
            'HARDWARE'=>[['PC Diagnostic Report','Diagnose five fictional PC symptoms using symptom, likely cause, safe test, result and recommended action; include ESD precautions.'],['Assembly and OS Setup','Prepare a compatible parts list, assembly checklist, firmware checks, OS/driver installation record and final test report.'],['Small Office Network','Design and document a router, switch, four PCs, printer and guest Wi-Fi network with IP plan, topology, tests and security controls.']],
            'PYTHON'=>[['Grade Calculator','Write a validated grade calculator and test boundary values -1, 0, 39, 40, 100 and 101; submit code and result table.'],['CSV Attendance Report','Create functions to read validated CSV data, calculate totals and save a report with clear exception handling.'],['Python Final Project','Build a persistent student or inventory manager with add, search, list and save features; submit source, ten test cases and README.']],
            'TALLY'=>[['Ledger and Voucher Practice','Create a company, classify 25 ledgers and enter one week of contra, payment, receipt, purchase and sales transactions.'],['GST Invoice Project','Create local and interstate sales invoices, verify CGST/SGST/IGST calculations and submit GST ledger reconciliation.'],['Accounts Audit','Review day book, ledgers, trial balance, receivables, P&L and balance sheet; identify and document five corrections plus backup.']],
            'WEB'=>[['Responsive Course Page','Build an accessible HTML/CSS course page with semantic structure, responsive cards, labelled enquiry form and 360/768/1366 tests.'],['JavaScript Form Project','Add accessible mobile navigation and client-side enquiry validation with trimmed values, phone checks and inline errors.'],['Website Deployment Project','Build and deploy a multi-page institute website using Git, HTTPS and private environment settings; submit smoke tests, performance notes and rollback plan.']],
        ];
        $existing=collect(LearningResourceStore::all())->pluck('seed_key')->filter()->all();
        $added=0;$skipped=0;
        foreach($assignments as $code=>$items){
            if(!Course::where('code',$code)->exists()){ $this->warn("Skipped {$code}: course not found."); continue; }
            foreach($items as $index=>[$title,$description]){
                $number=$index+1;$key='mci-assignment-v1-'.strtolower($code).'-'.$number;
                if(in_array($key,$existing,true)){ $skipped++; continue; }
                LearningResourceStore::add([
                    'seed_key'=>$key,'course_code'=>$code,'type'=>'assignment',
                    'title'=>$code.' Assignment '.$number.' - '.$title,
                    'description'=>$description.' Submit a written explanation or a cloud-drive link to your working files.',
                    'link_url'=>'','due_date'=>null,'is_pinned'=>false,
                ]);
                $added++;$this->info("Published: {$code} Assignment {$number}");
            }
        }
        $this->newLine();$this->info("Assignments installed: {$added}; already available: {$skipped}.");
        return self::SUCCESS;
    }
}
