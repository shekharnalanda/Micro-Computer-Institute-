<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\AttendanceStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::disk('local')->delete(['mci-admissions.json','mci-attendance.json']);
    }

    protected function tearDown(): void
    {
        Storage::disk('local')->delete(['mci-admissions.json','mci-attendance.json']);
        parent::tearDown();
    }

    public function test_admin_can_mark_and_export_daily_attendance(): void
    {
        $student = AdmissionStore::add([
            'student_name' => 'Attendance Student',
            'guardian_name' => 'Guardian',
            'phone' => '9876543210',
            'city' => 'Bihar Sharif',
            'course_code' => 'DCA',
            'course_fee' => 4500,
            'dob' => '2005-01-01',
            'gender' => 'Male',
            'qualification' => '12th',
            'address' => 'Bihar Sharif',
            'email' => '',
            'preferred_time' => 'Morning',
            'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        AdmissionStore::updateStudentRecord($student['id'], [
            'roll_no' => 'MCI-DCA-001',
            'batch_name' => 'DCA Morning',
            'batch_time' => '08:00 AM',
            'joining_date' => '2026-08-22',
            'student_status' => 'active',
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.attendance.index', ['date' => '2026-08-22']))
            ->assertOk()->assertSee('Attendance Student')->assertSee('DAILY ATTENDANCE REGISTER');

        $this->post(route('admin.attendance.store'), [
            'date' => '2026-08-22',
            'attendance' => [$student['id'] => 'present'],
            'notes' => [$student['id'] => 'On time'],
        ])->assertRedirect();

        $record = AttendanceStore::forDate('2026-08-22')[$student['id']];
        $this->assertSame('present', $record['status']);
        $this->assertSame('On time', $record['note']);

        $this->get(route('admin.attendance.export', ['date' => '2026-08-22']))->assertOk();
    }

    public function test_admin_can_view_and_export_monthly_attendance_report(): void
    {
        $student = AdmissionStore::add([
            'student_name' => 'Monthly Student',
            'guardian_name' => 'Guardian',
            'phone' => '9876543210',
            'city' => 'Bihar Sharif',
            'course_code' => 'DCA',
            'course_fee' => 4500,
            'dob' => '2005-01-01',
            'gender' => 'Male',
            'qualification' => '12th',
            'address' => 'Bihar Sharif',
            'email' => '',
            'preferred_time' => 'Morning',
            'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');
        AdmissionStore::updateStudentRecord($student['id'], [
            'roll_no' => 'MCI-DCA-002',
            'batch_name' => 'DCA Morning',
            'batch_time' => '08:00 AM',
            'joining_date' => '2026-08-01',
            'student_status' => 'active',
        ]);
        AttendanceStore::saveBulk('2026-08-20', [$student['id'] => 'present']);
        AttendanceStore::saveBulk('2026-08-21', [$student['id'] => 'absent']);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.attendance.report', ['month' => '2026-08']))
            ->assertOk()->assertSee('Monthly Student')->assertSee('50%');

        $this->get(route('admin.attendance.report.export', ['month' => '2026-08']))->assertOk();
    }

}
