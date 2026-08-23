<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\ExamResultStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExamResultTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        @unlink(storage_path('app/mci-admissions.json'));
        @unlink(storage_path('app/mci-exam-results.json'));
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/mci-admissions.json'));
        @unlink(storage_path('app/mci-exam-results.json'));
        parent::tearDown();
    }

    public function test_admin_can_publish_calculated_result_and_print_marksheet(): void
    {
        Course::create([
            'code' => 'DCA', 'title' => 'Diploma in Computer Applications',
            'duration' => '6 Months', 'fee_amount' => 4500,
            'level' => 'Foundation', 'summary' => 'Office skills', 'is_active' => true,
        ]);
        $student = AdmissionStore::add([
            'student_name' => 'Result Student', 'guardian_name' => 'Guardian',
            'phone' => '9876543210', 'city' => 'Bihar Sharif', 'course_code' => 'DCA',
            'course_fee' => 4500, 'dob' => '2005-01-01', 'gender' => 'Male',
            'qualification' => '12th', 'address' => 'Bihar Sharif', 'email' => '',
            'preferred_time' => 'Morning', 'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        AdmissionStore::updateStudentRecord($student['id'], [
            'roll_no' => 'MCI-DCA-020', 'batch_name' => 'Morning',
            'batch_time' => '08:00 AM', 'joining_date' => now()->subMonth()->toDateString(),
            'student_status' => 'active',
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.results.store'), [
            'student_id' => $student['id'],
            'exam_name' => 'DCA Final Examination',
            'exam_date' => now()->toDateString(),
            'subject_names' => ['Computer Fundamentals','MS Office','Internet'],
            'max_marks' => [100,100,100],
            'obtained_marks' => [92,84,79],
            'remarks' => 'Excellent performance',
        ])->assertRedirect()->assertSessionHas('success');

        $result = ExamResultStore::all()[0];
        $this->assertSame(255.0, (float) $result['obtained_total']);
        $this->assertSame(85.0, (float) $result['percentage']);
        $this->assertSame('A', $result['grade']);
        $this->assertSame('pass', $result['result_status']);

        $this->get(route('admin.results.index'))
            ->assertOk()->assertSee('Result Student')->assertSee('85.0%')->assertSee('Print Marksheet');
        $this->get(route('admin.results.marksheet', $result['id']))
            ->assertOk()->assertSee('STATEMENT OF MARKS')->assertSee('MCI-DCA-020')
            ->assertSee('Computer Fundamentals')->assertSee('255.00')->assertSee('RESULT: PASS');
    }

    public function test_subject_failure_and_invalid_marks_are_handled_safely(): void
    {
        $student = AdmissionStore::add([
            'student_name' => 'Safe Marks Student', 'guardian_name' => 'Guardian',
            'phone' => '9876543210', 'city' => 'Bihar Sharif', 'course_code' => 'DCA',
            'course_fee' => 4500, 'dob' => '2005-01-01', 'gender' => 'Male',
            'qualification' => '12th', 'address' => 'Bihar Sharif', 'email' => '',
            'preferred_time' => 'Morning', 'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.results.store'), [
            'student_id' => $student['id'], 'exam_name' => 'Monthly Test',
            'exam_date' => now()->toDateString(), 'subject_names' => ['Theory','Practical'],
            'max_marks' => [100,100], 'obtained_marks' => [30,90],
        ])->assertRedirect();
        $this->assertSame('fail', ExamResultStore::all()[0]['result_status']);
        $this->assertSame('F', ExamResultStore::all()[0]['grade']);

        $this->post(route('admin.results.store'), [
            'student_id' => $student['id'], 'exam_name' => 'Invalid Test',
            'exam_date' => now()->toDateString(), 'subject_names' => ['Theory'],
            'max_marks' => [100], 'obtained_marks' => [120],
        ])->assertSessionHasErrors('obtained_marks');
        $this->assertCount(1, ExamResultStore::all());
    }
}
