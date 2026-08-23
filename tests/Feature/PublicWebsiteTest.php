<?php

namespace Tests\Feature;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_active_courses(): void
    {
        Course::create(['code'=>'DCA','title'=>'Diploma in Computer Applications','duration'=>'6 Months','level'=>'Foundation','summary'=>'Office skills','fee_amount'=>4500,'fee_note'=>'Registration included','is_active'=>true]);
        $this->get('/')->assertOk()->assertSee('Micro Computer Institute')->assertSee('Diploma in Computer Applications', false)->assertSee('4500', false);
    }

    public function test_enquiry_is_saved(): void
    {
        $this->postJson('/enquiry',['name'=>'Test Student','phone'=>'9876543210','course'=>'DCA','website'=>''])->assertOk()->assertJson(['success'=>true]);
        $this->assertDatabaseHas('enquiries',['phone'=>'9876543210','course_code'=>'DCA']);
    }
}
