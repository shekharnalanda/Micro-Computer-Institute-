<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\LearningResourceStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        @unlink(storage_path('app/mci-learning-resources.json'));
        @unlink(storage_path('app/mci-admissions.json'));
        $this->course('DCA', 'Diploma in Computer Applications');
        $this->course('TALLY', 'Tally Prime with GST');
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/mci-learning-resources.json'));
        @unlink(storage_path('app/mci-admissions.json'));
        parent::tearDown();
    }

    public function test_admin_can_publish_filter_toggle_and_delete_resources(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $response = $this->actingAs($admin)->post(route('admin.learning.store'), [
            'course_code' => 'DCA',
            'type' => 'assignment',
            'title' => 'MS Word Practical Assignment',
            'description' => 'Complete the formatting exercise.',
            'link_url' => 'https://example.com/word-assignment',
            'due_date' => now()->addWeek()->toDateString(),
            'is_pinned' => '1',
        ]);
        $response->assertRedirect()->assertSessionHas('success');

        $resource = LearningResourceStore::all()[0];
        $this->actingAs($admin)->get(route('admin.learning.index', ['search' => 'word', 'course' => 'DCA', 'type' => 'assignment']))
            ->assertOk()->assertSee('MS Word Practical Assignment')->assertSee('Open Resource');

        $this->actingAs($admin)->patch(route('admin.learning.toggle', $resource['id']))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertFalse(LearningResourceStore::all()[0]['is_active']);

        $this->actingAs($admin)->delete(route('admin.learning.destroy', $resource['id']))
            ->assertRedirect()->assertSessionHas('success');
        $this->assertCount(0, LearningResourceStore::all());
    }

    public function test_student_sees_only_active_resources_for_own_course(): void
    {
        $dca = LearningResourceStore::add([
            'course_code' => 'DCA', 'type' => 'notes', 'title' => 'DCA Computer Fundamentals Notes',
            'description' => 'Read chapter one.', 'link_url' => 'https://example.com/dca-notes',
            'due_date' => null, 'is_pinned' => true,
        ]);
        LearningResourceStore::add([
            'course_code' => 'TALLY', 'type' => 'video', 'title' => 'Private Tally Video',
            'description' => 'Tally students only.', 'link_url' => 'https://example.com/tally-video',
            'due_date' => null, 'is_pinned' => false,
        ]);
        $hidden = LearningResourceStore::add([
            'course_code' => 'DCA', 'type' => 'assignment', 'title' => 'Hidden DCA Assignment',
            'description' => 'Not published.', 'link_url' => 'https://example.com/hidden',
            'due_date' => now()->addDays(3)->toDateString(), 'is_pinned' => false,
        ]);
        LearningResourceStore::toggle($hidden['id']);

        $student = AdmissionStore::add([
            'student_name' => 'Learning Student', 'guardian_name' => 'Guardian',
            'phone' => '9876543210', 'city' => 'Bihar Sharif', 'course_code' => 'DCA',
            'course_fee' => 6000, 'dob' => '2005-01-01', 'gender' => 'Male',
            'qualification' => '12th', 'address' => 'Bihar Sharif', 'email' => '',
            'preferred_time' => 'Morning', 'message' => '',
        ]);
        AdmissionStore::updateStatus($student['id'], 'admitted');

        $this->post(route('student.login.submit'), [
            'application_no' => $student['application_no'], 'phone' => '9876543210',
        ])->assertRedirect(route('student.dashboard'));

        $this->get(route('student.dashboard'))->assertOk()
            ->assertSee('Study Materials & Assignments', false)
            ->assertSee('DCA Computer Fundamentals Notes')
            ->assertSee('https://example.com/dca-notes', false)
            ->assertDontSee('Private Tally Video')
            ->assertDontSee('Hidden DCA Assignment');
    }

    public function test_admin_cannot_publish_unsafe_or_unknown_resource(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post(route('admin.learning.store'), [
            'course_code' => 'UNKNOWN', 'type' => 'notes', 'title' => 'Unsafe',
            'link_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors(['course_code','link_url']);
        $this->assertCount(0, LearningResourceStore::all());
    }

    private function course(string $code, string $title): void
    {
        Course::create([
            'code' => $code, 'title' => $title, 'duration' => '6 Months',
            'fee_amount' => 6000, 'level' => 'Foundation',
            'summary' => 'Computer education course', 'is_active' => true,
        ]);
    }
}
