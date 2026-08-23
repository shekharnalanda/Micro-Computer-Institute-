<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Support\AdmissionStore;
use App\Support\AttendanceStore;
use App\Support\CertificateStore;
use App\Support\ExamResultStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['mci-admissions.json','mci-attendance.json','mci-exam-results.json','mci-certificates.json'] as $file) {
            @unlink(storage_path('app/'.$file));
        }
    }

    protected function tearDown(): void
    {
        foreach (['mci-admissions.json','mci-attendance.json','mci-exam-results.json','mci-certificates.json'] as $file) {
            @unlink(storage_path('app/'.$file));
        }
        parent::tearDown();
    }

    public function test_admitted_student_can_securely_access_all_portal_records(): void
    {
        Course::create([
            'code' => 'DCA', 'title' => 'Diploma in Computer Applications',
            'duration' => '6 Months', 'fee_amount' => 6000,
            'level' => 'Foundation', 'summary' => 'Office skills', 'is_active' => true,
        ]);
        $student = AdmissionStore::add([
            'student_name' => 'Portal Student', 'guardian_name' => 'Guardian',
            'phone' => '+91 98765 43210', 'city' => 'Bihar Sharif', 'course_code' => 'DCA',
            'course_fee' => 6000, 'dob' => '2005-01-01', 'gender' => 'Male',
            'qualification' => '12th', 'address' => 'Bihar Sharif', 'email' => '',
            'preferred_time' => 'Morning', 'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        AdmissionStore::updateStudentRecord($student['id'], [
            'roll_no' => 'MCI-DCA-040', 'batch_name' => 'Morning Batch',
            'batch_time' => '08:00 AM', 'joining_date' => now()->subMonth()->toDateString(),
            'student_status' => 'active',
        ]);
        AdmissionStore::addPaymentTransaction($student['id'], 2000, now()->toDateString(), 'upi', 'PORTAL-UPI-01', 'First fee');
        AttendanceStore::saveBulk(now()->subDay()->toDateString(), [$student['id'] => 'present']);
        AttendanceStore::saveBulk(now()->toDateString(), [$student['id'] => 'absent'], [$student['id'] => 'Medical']);
        $result = ExamResultStore::add([
            'student_id' => $student['id'], 'exam_name' => 'Portal Test', 'exam_date' => now()->toDateString(),
            'subjects' => [['name'=>'Computer','max_marks'=>100,'obtained_marks'=>88,'status'=>'pass']],
            'max_total' => 100, 'obtained_total' => 88, 'percentage' => 88, 'grade' => 'A',
            'result_status' => 'pass', 'remarks' => 'Very good',
        ]);
        $certificate = CertificateStore::add([
            'student_id' => $student['id'], 'type' => 'merit', 'title' => 'Merit Certificate',
            'issue_date' => now()->toDateString(), 'completion_date' => null, 'grade' => 'A',
            'description' => 'Outstanding performance',
        ]);

        $this->get(route('student.dashboard'))->assertRedirect(route('student.login'));
        $this->post(route('student.login.submit'), [
            'application_no' => $student['application_no'], 'phone' => '0000000000',
        ])->assertSessionHasErrors('application_no');

        $this->post(route('student.login.submit'), [
            'application_no' => strtolower($student['application_no']), 'phone' => '9876543210',
        ])->assertRedirect(route('student.dashboard'));

        $this->get(route('student.dashboard'))
            ->assertOk()
            ->assertHeader('cache-control', 'no-store, private')
            ->assertSee('Portal Student')->assertSee('MCI-DCA-040')
            ->assertSee('PORTAL-UPI-01')->assertSee('50.0%')
            ->assertSee('Portal Test')->assertSee('Merit Certificate')
            ->assertSee($certificate['verification_code']);

        $this->get(route('student.results.marksheet', $result['id']))
            ->assertOk()->assertSee('STATEMENT OF MARKS')->assertSee('Portal Student');
        $this->get(route('student.certificates.print', $certificate['id']))
            ->assertOk()->assertSee('Merit Certificate')->assertSee('Portal Student');

        $this->post(route('student.logout'))->assertRedirect(route('student.login'));
        $this->get(route('student.dashboard'))->assertRedirect(route('student.login'));
    }

    public function test_student_cannot_open_another_students_private_documents(): void
    {
        $first = $this->admittedStudent('First Student', '9876543210');
        $second = $this->admittedStudent('Second Student', '9999999999');
        $otherResult = ExamResultStore::add([
            'student_id' => $second['id'], 'exam_name' => 'Private Result', 'exam_date' => now()->toDateString(),
            'subjects' => [['name'=>'Computer','max_marks'=>100,'obtained_marks'=>80,'status'=>'pass']],
            'max_total' => 100, 'obtained_total' => 80, 'percentage' => 80, 'grade' => 'A',
            'result_status' => 'pass', 'remarks' => null,
        ]);
        $otherCertificate = CertificateStore::add([
            'student_id' => $second['id'], 'type' => 'completion', 'title' => 'Private Certificate',
            'issue_date' => now()->toDateString(), 'completion_date' => now()->toDateString(),
            'grade' => null, 'description' => null,
        ]);

        $this->post(route('student.login.submit'), [
            'application_no' => $first['application_no'], 'phone' => '9876543210',
        ])->assertRedirect(route('student.dashboard'));
        $this->get(route('student.results.marksheet', $otherResult['id']))->assertNotFound();
        $this->get(route('student.certificates.print', $otherCertificate['id']))->assertNotFound();
    }

    private function admittedStudent(string $name, string $phone): array
    {
        $student = AdmissionStore::add([
            'student_name' => $name, 'guardian_name' => 'Guardian', 'phone' => $phone,
            'city' => 'Bihar Sharif', 'course_code' => 'DCA', 'course_fee' => 4500,
            'dob' => '2005-01-01', 'gender' => 'Male', 'qualification' => '12th',
            'address' => 'Bihar Sharif', 'email' => '', 'preferred_time' => 'Morning', 'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        return AdmissionStore::find($student['id']);
    }
}
