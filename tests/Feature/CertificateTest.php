<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\CertificateStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        @unlink(storage_path('app/mci-admissions.json'));
        @unlink(storage_path('app/mci-certificates.json'));
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/mci-admissions.json'));
        @unlink(storage_path('app/mci-certificates.json'));
        parent::tearDown();
    }

    public function test_admin_can_issue_print_and_publicly_verify_certificate(): void
    {
        Course::create([
            'code' => 'DCA', 'title' => 'Diploma in Computer Applications',
            'duration' => '6 Months', 'fee_amount' => 4500,
            'level' => 'Foundation', 'summary' => 'Office skills', 'is_active' => true,
        ]);
        $student = AdmissionStore::add([
            'student_name' => 'Certified Student', 'guardian_name' => 'Guardian',
            'phone' => '9876543210', 'city' => 'Bihar Sharif', 'course_code' => 'DCA',
            'course_fee' => 4500, 'dob' => '2005-01-01', 'gender' => 'Male',
            'qualification' => '12th', 'address' => 'Bihar Sharif', 'email' => '',
            'preferred_time' => 'Morning', 'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        AdmissionStore::updateStudentRecord($student['id'], [
            'roll_no' => 'MCI-DCA-030', 'batch_name' => 'Morning',
            'batch_time' => '08:00 AM', 'joining_date' => now()->subMonths(6)->toDateString(),
            'student_status' => 'completed',
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.certificates.store'), [
            'student_id' => $student['id'],
            'type' => 'completion',
            'title' => 'Certificate of Course Completion',
            'issue_date' => now()->toDateString(),
            'completion_date' => now()->subDay()->toDateString(),
            'grade' => 'A',
            'description' => 'Successfully completed practical computer training.',
        ])->assertRedirect()->assertSessionHas('success');

        $certificate = CertificateStore::all()[0];
        $this->assertStringStartsWith('MCI-CERT-', $certificate['certificate_no']);
        $this->assertMatchesRegularExpression('/^MCI-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $certificate['verification_code']);

        $this->get(route('admin.certificates.index'))
            ->assertOk()->assertSee('Certified Student')->assertSee($certificate['verification_code']);
        $this->get(route('admin.certificates.print', $certificate['id']))
            ->assertOk()->assertSee('Certificate of Course Completion')->assertSee('MCI-DCA-030')->assertSee($certificate['verification_code']);

        $this->get(route('certificates.verify', ['code' => strtolower($certificate['verification_code'])]))
            ->assertOk()->assertSee('Authentic MCI Certificate')->assertSee('Certified Student')->assertSee('Diploma in Computer Applications');
    }

    public function test_revoked_or_unknown_certificate_does_not_verify(): void
    {
        $student = AdmissionStore::add([
            'student_name' => 'Revoked Student', 'guardian_name' => 'Guardian',
            'phone' => '9876543210', 'city' => 'Bihar Sharif', 'course_code' => 'DCA',
            'course_fee' => 4500, 'dob' => '2005-01-01', 'gender' => 'Male',
            'qualification' => '12th', 'address' => 'Bihar Sharif', 'email' => '',
            'preferred_time' => 'Morning', 'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        $certificate = CertificateStore::add([
            'student_id' => $student['id'], 'type' => 'participation',
            'title' => 'Participation Certificate', 'issue_date' => now()->toDateString(),
            'completion_date' => null, 'grade' => null, 'description' => 'Workshop',
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->delete(route('admin.certificates.destroy', $certificate['id']))
            ->assertRedirect()->assertSessionHas('success');
        $this->get(route('certificates.verify', ['code' => $certificate['verification_code']]))
            ->assertOk()->assertSee('Certificate not found')->assertDontSee('Authentic MCI Certificate');
    }
}
