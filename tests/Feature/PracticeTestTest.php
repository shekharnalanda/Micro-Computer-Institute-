<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\CertificateStore;
use App\Support\CourseAssessment;
use App\Support\ExamResultStore;
use App\Support\PracticeTestStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PracticeTestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach(['mci-admissions.json','mci-practice-tests.json','mci-practice-attempts.json','mci-exam-results.json','mci-certificates.json'] as $file) @unlink(storage_path('app/'.$file));
        foreach([['DCA','Diploma in Computer Applications'],['TALLY','Tally Prime']] as [$code,$title]){
            Course::create(['code'=>$code,'title'=>$title,'duration'=>'6 Months','fee_amount'=>6000,'level'=>'Foundation','summary'=>'Course','is_active'=>true]);
        }
    }

    protected function tearDown(): void
    {
        foreach(['mci-admissions.json','mci-practice-tests.json','mci-practice-attempts.json','mci-exam-results.json','mci-certificates.json'] as $file) @unlink(storage_path('app/'.$file));
        parent::tearDown();
    }

    public function test_admin_can_create_toggle_filter_and_delete_practice_test(): void
    {
        $admin=User::factory()->create(['is_admin'=>true]);
        $this->actingAs($admin)->post(route('admin.practice.store'),[
            'course_code'=>'DCA','title'=>'Computer Fundamentals Test','duration_minutes'=>15,'pass_percentage'=>40,
            'questions'=>[[
                'prompt'=>'CPU stands for?','option_a'=>'Central Processing Unit','option_b'=>'Computer Personal Unit',
                'option_c'=>'Central Print Unit','option_d'=>'Control Program Unit','correct'=>'A',
            ]],
        ])->assertRedirect()->assertSessionHas('success');
        $test=PracticeTestStore::all()[0];
        $this->actingAs($admin)->get(route('admin.practice.index',['search'=>'fundamentals','course'=>'DCA']))
            ->assertOk()->assertSee('Computer Fundamentals Test')->assertSee('1');
        $this->actingAs($admin)->patch(route('admin.practice.toggle',$test['id']))->assertRedirect();
        $this->assertFalse(PracticeTestStore::find($test['id'])['is_active']);
        $this->actingAs($admin)->delete(route('admin.practice.destroy',$test['id']))->assertRedirect();
        $this->assertCount(10,PracticeTestStore::all());
        $this->assertNull(PracticeTestStore::find($test['id']));
    }

    public function test_student_takes_own_course_test_and_gets_instant_score(): void
    {
        $student=$this->admittedStudent();
        $test=$this->testRecord('DCA');
        $question=$test['questions'][0];
        $this->withSession(['student_portal_id'=>$student['id']])->get(route('student.practice.take',$test['id']))
            ->assertOk()->assertSee('CPU stands for?')->assertDontSee('name="correct"',false);
        $response=$this->withSession(['student_portal_id'=>$student['id']])->post(route('student.practice.submit',$test['id']),[
            'answers'=>[$question['id']=>'A'],
        ]);
        $attempt=PracticeTestStore::attempts()[0];
        $response->assertRedirect(route('student.practice.result',$attempt['id']));
        $this->assertSame('pass',$attempt['status']);
        $this->assertEquals(100,$attempt['percentage']);
        $this->withSession(['student_portal_id'=>$student['id']])->get(route('student.practice.result',$attempt['id']))
            ->assertOk()->assertSee('100.0%')->assertSee('PASS')->assertSee('Your answer');
    }

    public function test_course_privacy_and_attempt_ownership_are_enforced(): void
    {
        $first=$this->admittedStudent();
        $second=$this->admittedStudent('Second Student','9999999999');
        $otherTest=$this->testRecord('TALLY');
        $dcaTest=$this->testRecord('DCA');
        $attempt=PracticeTestStore::recordAttempt([
            'test_id'=>$dcaTest['id'],'student_id'=>$second['id'],'course_code'=>'DCA',
            'correct_answers'=>0,'total_questions'=>1,'percentage'=>0,'status'=>'fail','review'=>[],
        ]);
        $this->withSession(['student_portal_id'=>$first['id']])->get(route('student.practice.take',$otherTest['id']))->assertNotFound();
        $this->withSession(['student_portal_id'=>$first['id']])->get(route('student.practice.result',$attempt['id']))->assertNotFound();
    }

    public function test_five_course_assessments_publish_final_marksheet_and_certificate(): void
    {
        $student=$this->admittedStudent();
        $this->assertSame(5,PracticeTestStore::installStarterSets(['DCA']));
        foreach(PracticeTestStore::all() as $test){
            if(($test['course_code']??'')!=='DCA') continue;
            PracticeTestStore::recordAttempt([
                'test_id'=>$test['id'],'student_id'=>$student['id'],'course_code'=>'DCA',
                'correct_answers'=>5,'total_questions'=>5,'percentage'=>100,'status'=>'pass','review'=>[],
            ]);
        }
        $summary=CourseAssessment::publishIfEligible($student);
        $this->assertTrue($summary['passed']);
        $this->assertSame(100.0,$summary['percentage']);
        $this->assertCount(1,ExamResultStore::all());
        $this->assertCount(1,CertificateStore::all());
        CourseAssessment::publishIfEligible($student);
        $this->assertCount(1,ExamResultStore::all());
        $this->assertCount(1,CertificateStore::all());
    }

    private function admittedStudent(string $name='Practice Student',string $phone='9876543210'): array
    {
        $student=AdmissionStore::add([
            'student_name'=>$name,'guardian_name'=>'Guardian','phone'=>$phone,'city'=>'Bihar Sharif',
            'course_code'=>'DCA','course_fee'=>6000,'dob'=>'2005-01-01','gender'=>'Male',
            'qualification'=>'12th','address'=>'Bihar Sharif','email'=>'','preferred_time'=>'Morning','message'=>'',
        ]);
        AdmissionStore::updateStatus($student['id'],'admitted');
        return AdmissionStore::find($student['id']);
    }

    private function testRecord(string $course): array
    {
        return PracticeTestStore::add([
            'course_code'=>$course,'title'=>$course.' Basic Test','duration_minutes'=>10,'pass_percentage'=>40,
            'questions'=>[[
                'id'=>'q-'.strtolower($course),'prompt'=>'CPU stands for?',
                'options'=>['A'=>'Central Processing Unit','B'=>'Computer Personal Unit','C'=>'Central Print Unit','D'=>'Control Program Unit'],
                'correct'=>'A',
            ]],
        ]);
    }
}
