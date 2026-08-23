<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Support\PracticeTestStore;
use App\Support\StarterPracticeTests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StarterPracticeTestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        @unlink(storage_path('app/mci-practice-tests.json'));
        @unlink(storage_path('app/mci-practice-attempts.json'));
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('app/mci-practice-tests.json'));
        @unlink(storage_path('app/mci-practice-attempts.json'));
        parent::tearDown();
    }

    public function test_starter_library_covers_every_center_course_with_valid_questions(): void
    {
        $expected=['DCA','ADCA','CCC','TALLY','EXCEL','DTP','WEB','PYTHON','DIGITAL','HARDWARE','AI','DATA'];
        $sets=StarterPracticeTests::all();
        $this->assertCount(60,$sets);
        $this->assertSame($expected,collect($sets)->pluck('course_code')->unique()->values()->all());
        foreach($expected as $course){
            $courseSets=collect($sets)->where('course_code',$course)->sortBy('assessment_order')->values();
            $this->assertCount(5,$courseSets);
            $this->assertSame(['practice','practice','terminal','terminal','final'],$courseSets->pluck('assessment_type')->all());
            $this->assertSame([10,10,20,20,40],$courseSets->pluck('assessment_weight')->all());
        }
        foreach($sets as $set){
            $this->assertCount(5,$set['questions']);
            $this->assertSame(40,$set['pass_percentage']);
            foreach($set['questions'] as $question){
                $this->assertCount(4,$question['options']);
                $this->assertArrayHasKey($question['correct'],$question['options']);
            }
        }
    }

    public function test_admin_page_installs_sets_for_active_courses_once_only(): void
    {
        Course::create(['code'=>'DCA','title'=>'DCA','duration'=>'6 Months','fee_amount'=>6000,'level'=>'Foundation','summary'=>'Course','is_active'=>true]);
        Course::create(['code'=>'TALLY','title'=>'Tally','duration'=>'3 Months','fee_amount'=>6000,'level'=>'Career','summary'=>'Course','is_active'=>true]);
        Course::create(['code'=>'WEB','title'=>'Web','duration'=>'6 Months','fee_amount'=>6000,'level'=>'Technical','summary'=>'Course','is_active'=>false]);
        $admin=User::factory()->create(['is_admin'=>true]);

        $this->actingAs($admin)->get(route('admin.practice.index'))
            ->assertOk()->assertSee('10 ready-made course assessment sets installed automatically.')
            ->assertSee('DCA Practice Test 1')->assertSee('TALLY Final Test');
        $this->assertCount(10,PracticeTestStore::all());

        $this->actingAs($admin)->get(route('admin.practice.index'))->assertOk()
            ->assertDontSee('installed automatically');
        $this->assertCount(10,PracticeTestStore::all());
    }
}
