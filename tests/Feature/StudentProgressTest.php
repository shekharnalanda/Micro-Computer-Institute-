<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\AssignmentSubmissionStore;
use App\Support\AttendanceStore;
use App\Support\ExamResultStore;
use App\Support\PracticeTestStore;
use App\Support\StudentProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProgressTest extends TestCase
{
    use RefreshDatabase;

    private array $files=['mci-admissions.json','mci-attendance.json','mci-assignment-submissions.json','mci-exam-results.json','mci-practice-tests.json','mci-practice-attempts.json'];

    protected function setUp(): void
    {
        parent::setUp();
        foreach($this->files as $file)@unlink(storage_path('app/'.$file));
        Course::create(['code'=>'DCA','title'=>'Diploma in Computer Applications','duration'=>'6 Months','fee_amount'=>6000,'level'=>'Foundation','summary'=>'Course','is_active'=>true]);
    }

    protected function tearDown(): void
    {
        foreach($this->files as $file)@unlink(storage_path('app/'.$file));
        parent::tearDown();
    }

    public function test_progress_combines_attendance_assignments_practice_exams_and_fees(): void
    {
        $student=$this->studentWithProgress();
        $progress=StudentProgress::calculate($student);
        $this->assertEquals(50,$progress['attendance_rate']);
        $this->assertEquals(80,$progress['assignment_average']);
        $this->assertEquals(70,$progress['practice_average']);
        $this->assertEquals(90,$progress['exam_average']);
        $this->assertEquals(72.5,$progress['overall']);
        $this->assertSame('Very Good',$progress['grade']);
        $this->assertEquals(50,$progress['fee_percentage']);
        $this->assertEquals(3000,$progress['balance_amount']);
    }

    public function test_admin_can_filter_and_print_progress_report(): void
    {
        $student=$this->studentWithProgress();
        $admin=User::factory()->create(['is_admin'=>true]);
        $this->actingAs($admin)->get(route('admin.progress.index',['search'=>'Progress Student','course'=>'DCA','level'=>'high']))
            ->assertOk()->assertSee('Progress Student')->assertSee('72.5%')->assertSee('Very Good');
        $this->actingAs($admin)->get(route('admin.progress.print',$student['id']))
            ->assertOk()->assertSee('PROGRESS REPORT')->assertSee('Progress Student')->assertSee('72.5%')->assertSee('₹3,000.00');
    }

    public function test_student_sees_only_their_own_progress_summary(): void
    {
        $student=$this->studentWithProgress();
        $this->withSession(['student_portal_id'=>$student['id']])->get(route('student.dashboard'))
            ->assertOk()->assertSee('Overall Learning Progress')->assertSee('72.5%')->assertSee('Very Good');
    }

    private function studentWithProgress(): array
    {
        $student=AdmissionStore::add([
            'student_name'=>'Progress Student','guardian_name'=>'Guardian','phone'=>'9876543210','city'=>'Bihar Sharif',
            'course_code'=>'DCA','course_fee'=>6000,'dob'=>'2005-01-01','gender'=>'Male','qualification'=>'12th',
            'address'=>'Bihar Sharif','email'=>'','preferred_time'=>'Morning','message'=>'',
        ]);
        AdmissionStore::updateStatus($student['id'],'admitted');
        AdmissionStore::addPaymentTransaction($student['id'],3000,now()->toDateString(),'cash','','Half fee');
        $student=AdmissionStore::find($student['id']);

        AttendanceStore::saveBulk(now()->subDay()->toDateString(),[$student['id']=>'present']);
        AttendanceStore::saveBulk(now()->toDateString(),[$student['id']=>'absent']);

        $submission=AssignmentSubmissionStore::submit([
            'student_id'=>$student['id'],'resource_id'=>'assignment-1','course_code'=>'DCA',
            'answer_text'=>'Done','submission_url'=>'',
        ]);
        AssignmentSubmissionStore::review($submission['id'],['marks'=>80,'feedback'=>'Good']);

        PracticeTestStore::recordAttempt([
            'test_id'=>'test-1','student_id'=>$student['id'],'course_code'=>'DCA',
            'correct_answers'=>7,'total_questions'=>10,'percentage'=>70,'status'=>'pass','review'=>[],
        ]);

        ExamResultStore::add([
            'student_id'=>$student['id'],'exam_name'=>'Final Test','exam_date'=>now()->toDateString(),
            'subjects'=>[['name'=>'Computer','max_marks'=>100,'obtained_marks'=>90,'status'=>'pass']],
            'max_total'=>100,'obtained_total'=>90,'percentage'=>90,'grade'=>'A+','result_status'=>'pass','remarks'=>'Excellent',
        ]);
        return $student;
    }
}
