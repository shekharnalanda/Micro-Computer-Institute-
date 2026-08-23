<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enquiry;
use App\Models\User;
use App\Support\AdmissionStore;
use App\Support\DataBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DataBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach(DataBackupService::RUNTIME_FILES as $file)@unlink(storage_path('app/'.$file));
        $this->clearSnapshots();
    }

    protected function tearDown(): void
    {
        foreach(DataBackupService::RUNTIME_FILES as $file)@unlink(storage_path('app/'.$file));
        $this->clearSnapshots();
        parent::tearDown();
    }

    public function test_signed_backup_restores_runtime_and_database_data_with_snapshot(): void
    {
        $course=Course::create(['code'=>'DCA','title'=>'Diploma in Computer Applications','duration'=>'6 Months','fee_amount'=>6000,'level'=>'Foundation','summary'=>'Course','modules'=>['Office'],'careers'=>[],'is_active'=>true]);
        Enquiry::create(['name'=>'Backup Enquiry','phone'=>'9876543210','course_code'=>'DCA','status'=>'new']);
        $student=AdmissionStore::add([
            'student_name'=>'Backup Student','guardian_name'=>'Guardian','phone'=>'9876543210','city'=>'Bihar Sharif',
            'course_code'=>'DCA','course_fee'=>6000,'dob'=>'2005-01-01','gender'=>'Male','qualification'=>'12th',
            'address'=>'Bihar Sharif','email'=>'','preferred_time'=>'Morning','message'=>'',
        ]);
        $backup=DataBackupService::create();
        $this->assertSame('mci-computer-education-backup',$backup['payload']['format']);

        Course::query()->delete();
        Enquiry::query()->delete();
        @unlink(storage_path('app/mci-admissions.json'));
        $result=DataBackupService::restore($backup);

        $this->assertSame(13,$result['files']);
        $this->assertDatabaseHas('courses',['code'=>'DCA']);
        $this->assertDatabaseHas('enquiries',['name'=>'Backup Enquiry']);
        $this->assertSame('Backup Student',AdmissionStore::find($student['id'])['student_name']);
        $this->assertCount(1,glob(storage_path('app/backups/pre-restore-*.json'))?:[]);
    }

    public function test_tampered_or_foreign_backup_is_rejected(): void
    {
        Course::create(['code'=>'DCA','title'=>'DCA','duration'=>'6 Months','fee_amount'=>6000,'level'=>'Foundation','summary'=>'Course','is_active'=>true]);
        $backup=DataBackupService::create();
        $backup['payload']['database']['courses'][0]['title']='Tampered Title';
        $this->expectException(\RuntimeException::class);
        DataBackupService::restore($backup);
    }

    public function test_admin_can_download_and_restore_backup_but_guest_cannot(): void
    {
        Course::create(['code'=>'DCA','title'=>'DCA','duration'=>'6 Months','fee_amount'=>6000,'level'=>'Foundation','summary'=>'Course','is_active'=>true]);
        $admin=User::factory()->create(['is_admin'=>true]);

        $this->get(route('admin.backup.download'))->assertRedirect(route('admin.login'));
        $download=$this->actingAs($admin)->get(route('admin.backup.download'));
        $download->assertOk()->assertHeader('content-type','application/json; charset=UTF-8');
        $backup=json_decode($download->getContent(),true);

        $this->actingAs($admin)->post(route('admin.backup.restore'),[
            'backup_file'=>UploadedFile::fake()->createWithContent('backup.json',json_encode($backup)),
            'confirmation'=>'WRONG',
        ])->assertSessionHasErrors('confirmation');

        $this->actingAs($admin)->post(route('admin.backup.restore'),[
            'backup_file'=>UploadedFile::fake()->createWithContent('backup.json',json_encode($backup)),
            'confirmation'=>'RESTORE MCI DATA',
        ])->assertRedirect()->assertSessionHas('success');
    }

    private function clearSnapshots(): void
    {
        $dir=storage_path('app/backups');
        foreach(glob($dir.'/pre-restore-*.json')?:[] as $file)@unlink($file);
        if(is_dir($dir))@rmdir($dir);
    }
}
