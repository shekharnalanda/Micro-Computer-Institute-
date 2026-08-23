<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\CertificateStore;
use App\Support\DemoDataManager;
use App\Support\ExamResultStore;
use App\Support\PracticeTestStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataModeTest extends TestCase
{
    use RefreshDatabase;
    private array $files=['mci-admissions.json','mci-practice-tests.json','mci-practice-attempts.json','mci-exam-results.json','mci-certificates.json'];
    protected function setUp():void{parent::setUp();foreach($this->files as $file)@unlink(storage_path('app/'.$file));}
    protected function tearDown():void{foreach($this->files as $file)@unlink(storage_path('app/'.$file));parent::tearDown();}

    public function test_admin_installs_and_removes_fully_labeled_demo_showcase():void
    {
        foreach([['DCA','DCA'],['TALLY','Tally']] as [$code,$title]) Course::create(['code'=>$code,'title'=>$title,'duration'=>'6 Months','fee_amount'=>6000,'level'=>'Career','summary'=>'Course','is_active'=>true]);
        $admin=User::factory()->create(['is_admin'=>true]);
        $this->actingAs($admin)->post(route('admin.demo-data.install'))->assertRedirect();
        $demo=collect(AdmissionStore::all())->where('is_demo',true);
        $this->assertCount(10,$demo);
        $this->assertCount(50,collect(PracticeTestStore::attempts())->whereIn('student_id',$demo->pluck('id')));
        $this->assertCount(10,collect(ExamResultStore::all())->where('is_demo',true));
        $this->assertCount(10,collect(CertificateStore::all())->where('is_demo',true));
        $certificate=collect(CertificateStore::all())->firstWhere('is_demo',true);
        $this->get(route('certificates.verify',['code'=>$certificate['verification_code']]))->assertOk()->assertSee('Certificate not found');
        $this->actingAs($admin)->delete(route('admin.demo-data.destroy'))->assertRedirect();
        $this->assertCount(0,AdmissionStore::all());
        $this->assertCount(0,PracticeTestStore::attempts());
    }
}
