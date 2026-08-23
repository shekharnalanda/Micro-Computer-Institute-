<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\CommunicationTemplateStore;
use App\Support\NoticeStore;
use App\Support\StudentNotificationCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunicationCenterTest extends TestCase
{
    use RefreshDatabase;

    private array $files=['mci-admissions.json','mci-communication-templates.json','mci-notices.json','mci-learning-resources.json','mci-assignment-submissions.json','mci-exam-results.json'];

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

    public function test_starter_templates_render_student_variables_safely(): void
    {
        $templates=CommunicationTemplateStore::all();
        $this->assertCount(4,$templates);
        $rendered=CommunicationTemplateStore::render($templates[0],[
            'student_name'=>'Sujit Student','course_code'=>'DCA','application_no'=>'MCI-001','balance_amount'=>2500,
        ]);
        $this->assertStringContainsString('Sujit Student',$rendered['body']);
        $this->assertStringContainsString('₹2,500.00',$rendered['body']);
        $this->assertStringContainsString('/student/login',$rendered['body']);
    }

    public function test_admin_can_filter_students_use_templates_and_manage_custom_templates(): void
    {
        $student=$this->admittedStudent();
        $admin=User::factory()->create(['is_admin'=>true]);
        $this->actingAs($admin)->get(route('admin.communications.index',['template'=>'starter-fee','course'=>'DCA','dues'=>1]))
            ->assertOk()->assertSee('Communication Student')->assertSee('Fee Due Reminder')->assertSee('WhatsApp')->assertSee('₹6,000.00');

        $this->actingAs($admin)->post(route('admin.communications.store'),[
            'name'=>'Holiday Message','channel'=>'both','category'=>'general',
            'subject'=>'Holiday notice for {course}','body'=>'Dear {student_name}, center will remain closed tomorrow.',
        ])->assertRedirect()->assertSessionHas('success');
        $custom=collect(CommunicationTemplateStore::all())->firstWhere('name','Holiday Message');
        $this->assertNotNull($custom);
        $this->actingAs($admin)->delete(route('admin.communications.destroy',$custom['id']))->assertRedirect();
    }

    public function test_student_portal_shows_public_notice_and_private_fee_alert(): void
    {
        $student=$this->admittedStudent();
        NoticeStore::add([
            'title'=>'Center Holiday','title_hi'=>'केंद्र अवकाश','description'=>'Center closed on Sunday.',
            'type'=>'holiday','notice_date'=>now()->toDateString(),'expires_at'=>now()->addWeek()->toDateString(),
            'link'=>'','is_pinned'=>true,
        ]);
        $notifications=StudentNotificationCenter::forStudent($student);
        $this->assertTrue(collect($notifications)->contains('title','Center Holiday'));
        $this->assertTrue(collect($notifications)->contains('title','Fee payment reminder'));

        $this->withSession(['student_portal_id'=>$student['id']])->get(route('student.dashboard'))
            ->assertOk()->assertSee('Notification Center')->assertSee('Center Holiday')
            ->assertSee('केंद्र अवकाश')->assertSee('₹6,000.00');
    }

    private function admittedStudent(): array
    {
        $student=AdmissionStore::add([
            'student_name'=>'Communication Student','guardian_name'=>'Guardian','phone'=>'9876543210','city'=>'Bihar Sharif',
            'course_code'=>'DCA','course_fee'=>6000,'dob'=>'2005-01-01','gender'=>'Male','qualification'=>'12th',
            'address'=>'Bihar Sharif','email'=>'student@example.com','preferred_time'=>'Morning','message'=>'',
        ]);
        AdmissionStore::updateStatus($student['id'],'admitted');
        return AdmissionStore::find($student['id']);
    }
}
