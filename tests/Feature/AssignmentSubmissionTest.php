<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\AssignmentSubmissionStore;
use App\Support\LearningResourceStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['mci-admissions.json','mci-learning-resources.json','mci-assignment-submissions.json'] as $file) @unlink(storage_path('app/'.$file));
        foreach ([['DCA','Diploma in Computer Applications'],['TALLY','Tally Prime']] as [$code,$title]) {
            Course::create(['code'=>$code,'title'=>$title,'duration'=>'6 Months','fee_amount'=>6000,'level'=>'Foundation','summary'=>'Course','is_active'=>true]);
        }
    }

    protected function tearDown(): void
    {
        foreach (['mci-admissions.json','mci-learning-resources.json','mci-assignment-submissions.json'] as $file) @unlink(storage_path('app/'.$file));
        parent::tearDown();
    }

    public function test_student_can_submit_and_resubmit_own_course_assignment(): void
    {
        [$student,$assignment] = $this->studentAndAssignment();
        $this->post(route('student.login.submit'), ['application_no'=>$student['application_no'],'phone'=>'9876543210'])
            ->assertRedirect(route('student.dashboard'));

        $this->post(route('student.assignments.submit'), [
            'resource_id'=>$assignment['id'], 'answer_text'=>'My practical answer',
            'submission_url'=>'https://drive.google.com/example',
        ])->assertRedirect()->assertSessionHas('success');

        $submission = AssignmentSubmissionStore::all()[0];
        $this->assertSame($student['id'], $submission['student_id']);
        $this->assertSame('submitted', $submission['status']);

        $this->post(route('student.assignments.submit'), [
            'resource_id'=>$assignment['id'], 'answer_text'=>'My corrected answer',
        ])->assertRedirect();
        $this->assertCount(1, AssignmentSubmissionStore::all());
        $this->assertSame('My corrected answer', AssignmentSubmissionStore::all()[0]['answer_text']);
    }

    public function test_admin_can_review_and_student_sees_marks_and_feedback(): void
    {
        [$student,$assignment] = $this->studentAndAssignment();
        $submission = AssignmentSubmissionStore::submit([
            'student_id'=>$student['id'],'resource_id'=>$assignment['id'],'course_code'=>'DCA',
            'answer_text'=>'Completed work','submission_url'=>'https://example.com/work',
        ]);
        $admin = User::factory()->create(['is_admin'=>true]);

        $this->actingAs($admin)->get(route('admin.assignments.index'))
            ->assertOk()->assertSee('Completed work')->assertSee('Learning Student');
        $this->actingAs($admin)->patch(route('admin.assignments.review',$submission['id']), [
            'marks'=>86, 'feedback'=>'Very good formatting.',
        ])->assertRedirect()->assertSessionHas('success');

        $reviewed = AssignmentSubmissionStore::find($submission['id']);
        $this->assertSame('reviewed', $reviewed['status']);
        $this->assertEquals(86, $reviewed['marks']);

        $this->withSession(['student_portal_id'=>$student['id']])->get(route('student.dashboard'))
            ->assertOk()->assertSee('Reviewed · 86/100')->assertSee('Very good formatting.');
    }

    public function test_student_cannot_submit_other_course_or_non_assignment_resource(): void
    {
        [$student] = $this->studentAndAssignment();
        $other = LearningResourceStore::add([
            'course_code'=>'TALLY','type'=>'assignment','title'=>'Tally Assignment',
            'description'=>'Private','link_url'=>'https://example.com/tally','due_date'=>null,'is_pinned'=>false,
        ]);
        $notes = LearningResourceStore::add([
            'course_code'=>'DCA','type'=>'notes','title'=>'DCA Notes',
            'description'=>'Notes','link_url'=>'https://example.com/notes','due_date'=>null,'is_pinned'=>false,
        ]);
        $this->withSession(['student_portal_id'=>$student['id']])
            ->post(route('student.assignments.submit'), ['resource_id'=>$other['id'],'answer_text'=>'Unauthorized'])
            ->assertNotFound();
        $this->withSession(['student_portal_id'=>$student['id']])
            ->post(route('student.assignments.submit'), ['resource_id'=>$notes['id'],'answer_text'=>'Not an assignment'])
            ->assertNotFound();
        $this->assertCount(0, AssignmentSubmissionStore::all());
    }

    private function studentAndAssignment(): array
    {
        $student = AdmissionStore::add([
            'student_name'=>'Learning Student','guardian_name'=>'Guardian','phone'=>'9876543210',
            'city'=>'Bihar Sharif','course_code'=>'DCA','course_fee'=>6000,'dob'=>'2005-01-01',
            'gender'=>'Male','qualification'=>'12th','address'=>'Bihar Sharif','email'=>'',
            'preferred_time'=>'Morning','message'=>'',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        $student = AdmissionStore::find($student['id']);
        $assignment = LearningResourceStore::add([
            'course_code'=>'DCA','type'=>'assignment','title'=>'MS Word Assignment',
            'description'=>'Complete exercise','link_url'=>'https://example.com/assignment',
            'due_date'=>now()->addWeek()->toDateString(),'is_pinned'=>true,
        ]);
        return [$student,$assignment];
    }
}
