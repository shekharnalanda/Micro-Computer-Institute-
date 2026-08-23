<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Support\AdmissionStore;
use App\Support\AssignmentSubmissionStore;
use App\Support\CertificateStore;
use App\Support\ExamResultStore;
use App\Support\LearningResourceStore;
use App\Support\PracticeTestStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class LearningSystemAudit extends Command
{
    protected $signature='mci:learning-audit {--install-demo : Create one admitted DCA audit student} {--remove-demo : Remove only the audit student and dependent records}';
    protected $description='Audit MCI course materials, assignments, tests, private files, routes and demo student access';

    public function handle(): int
    {
        if($this->option('remove-demo')) return $this->removeDemo();
        if($this->option('install-demo')) $this->installDemo();

        $courses=Course::where('is_active',true)->orderBy('code')->pluck('code');
        PracticeTestStore::installStarterSets($courses->all());
        $resources=collect(LearningResourceStore::all())->where('is_active',true);
        $materials=$resources->where('type','notes');
        $assignments=$resources->where('type','assignment');
        $tests=collect(PracticeTestStore::all())->where('is_active',true)->whereNotNull('starter_key');
        $failures=0;

        $this->newLine(); $this->info('MCI ONLINE LEARNING SYSTEM AUDIT');
        $this->line(str_repeat('-',68));
        $this->check('Active courses', $courses->count()===12, $courses->count().' / 12', $failures);
        $this->check('Published study PDFs', $materials->count()===24, $materials->count().' / 24', $failures);
        $this->check('Published assignments', $assignments->count()===36, $assignments->count().' / 36', $failures);
        $this->check('Ready-made online tests', $tests->count()===60, $tests->count().' / 60', $failures);

        $missingFiles=$materials->filter(fn(array $item):bool=>empty($item['file_path'])||!Storage::disk('local')->exists($item['file_path']));
        $this->check('Private PDF files', $missingFiles->isEmpty(), $missingFiles->isEmpty()?'All 24 available':$missingFiles->count().' missing', $failures);

        foreach(['student.login','student.dashboard','student.resources.download','student.assignments.submit','student.practice.take','admin.learning.index','admin.assignments.index','admin.practice.index'] as $route){
            $this->check('Route '.$route,Route::has($route),'Registered',$failures);
        }

        foreach($courses as $code){
            $courseMaterials=$materials->where('course_code',$code)->count();
            $courseAssignments=$assignments->where('course_code',$code)->count();
            $courseTests=$tests->where('course_code',$code);
            $unique=$courseTests->flatMap(fn(array $test):array=>$test['questions'])->pluck('prompt')->unique()->count();
            $final=$courseTests->firstWhere('assessment_type','final');
            $ok=$courseMaterials===2&&$courseAssignments===3&&$courseTests->count()===5&&$unique===10&&count($final['questions']??[])===10;
            $this->check($code.' learning package',$ok,"PDF={$courseMaterials}, Assignment={$courseAssignments}, Test={$courseTests->count()}, Unique MCQ={$unique}",$failures);
        }

        $demo=collect(AdmissionStore::all())->firstWhere('is_audit_demo',true);
        if($demo){
            $valid=($demo['status']??'')==='admitted'&&($demo['course_code']??'')==='DCA';
            $this->check('Audit student login record',$valid,($demo['application_no']??'Missing').' / '.($demo['phone']??'Missing'),$failures);
            $this->newLine(); $this->info('TEST STUDENT LOGIN');
            $this->line('URL: https://mciedu.com/student/login');
            $this->line('Application Number: '.$demo['application_no']);
            $this->line('Registered Mobile: '.$demo['phone']);
        }

        $this->newLine();
        if($failures===0){$this->info('LEARNING_AUDIT=PASS');return self::SUCCESS;}
        $this->error('LEARNING_AUDIT=FAIL | FAILURES='.$failures);return self::FAILURE;
    }

    private function installDemo(): void
    {
        $existing=collect(AdmissionStore::all())->firstWhere('is_audit_demo',true);
        if($existing){$this->line('Audit student already available.');return;}
        $course=Course::where('code','DCA')->firstOrFail();
        $student=AdmissionStore::add([
            'is_demo'=>true,'is_audit_demo'=>true,'student_name'=>'MCI Learning Audit Student',
            'guardian_name'=>'MCI System Audit','phone'=>'9999912345','email'=>'learning.audit@example.invalid',
            'city'=>'Bihar Sharif','course_code'=>'DCA','course_fee'=>(float)($course->fee_amount??0),
            'dob'=>'2004-01-15','gender'=>'Other','qualification'=>'12th','address'=>'MCI Campus - Temporary Audit Record',
            'preferred_time'=>'Morning','message'=>'Temporary student for online learning workflow verification.',
        ]);
        AdmissionStore::updateStatus($student['id'],'admitted');
        AdmissionStore::updateStudentRecord($student['id'],[
            'roll_no'=>'AUDIT-DCA-01','batch_name'=>'Audit Batch','batch_time'=>'10:00 AM',
            'joining_date'=>now()->toDateString(),'student_status'=>'active',
        ]);
        $this->info('One temporary DCA audit student created.');
    }

    private function removeDemo(): int
    {
        $students=collect(AdmissionStore::all())->where('is_audit_demo',true);$ids=$students->pluck('id')->all();
        if(!$ids){$this->line('No audit student found.');return self::SUCCESS;}
        AssignmentSubmissionStore::removeForStudents($ids);
        PracticeTestStore::removeAttemptsForStudents($ids);
        ExamResultStore::removeForStudents($ids);
        CertificateStore::removeForStudents($ids);
        foreach($ids as $id) AdmissionStore::remove($id);
        $this->info(count($ids).' audit student removed with dependent learning records.');
        return self::SUCCESS;
    }

    private function check(string $name,bool $passed,string $detail,int &$failures): void
    {
        if(!$passed)$failures++;
        $this->line(($passed?'<fg=green>PASS</>':'<fg=red>FAIL</>').' | '.str_pad($name,34).' | '.$detail);
    }
}
