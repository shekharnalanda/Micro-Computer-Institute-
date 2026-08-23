<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ProductionAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_release_readiness_audit_passes_required_application_checks(): void
    {
        User::factory()->create(['is_admin'=>true]);
        config([
            'app.env'=>'production','app.debug'=>false,'app.url'=>'https://mciedu.com',
            'app.timezone'=>'Asia/Kolkata','mail.from.address'=>'mcieducationalgroup@gmail.com',
        ]);
        $audit=ProductionAuditService::run();
        $this->assertTrue($audit['ready'],collect($audit['checks'])->where('status','fail')->pluck('name','detail')->toJson());
        $this->assertSame(0,$audit['failed']);
        $this->assertGreaterThanOrEqual(25,$audit['passed']);
    }

    public function test_production_audit_is_admin_only_and_json_is_private(): void
    {
        $admin=User::factory()->create(['is_admin'=>true]);
        $this->get(route('admin.audit.index'))->assertRedirect(route('admin.login'));

        $this->actingAs($admin)->get(route('admin.audit.index'))
            ->assertOk()->assertSee('Production Audit & Launch Readiness')
            ->assertSee('FINAL GO-LIVE GATE');
        $this->actingAs($admin)->get(route('admin.audit.json'))
            ->assertOk()->assertHeader('cache-control','no-store, private')
            ->assertJsonStructure(['checks','passed','warnings','failed','ready','checked_at']);
    }

    public function test_public_responses_include_complete_security_headers(): void
    {
        $response=$this->get(route('home'));
        $response->assertOk()
            ->assertHeader('x-content-type-options','nosniff')
            ->assertHeader('x-frame-options','SAMEORIGIN')
            ->assertHeader('referrer-policy','strict-origin-when-cross-origin')
            ->assertHeader('cross-origin-opener-policy','same-origin');
        $this->assertStringContainsString("default-src 'self'",$response->headers->get('content-security-policy'));
        $this->assertStringContainsString("form-action 'self'",$response->headers->get('content-security-policy'));
    }

    public function test_login_redirect_route_is_cache_safe(): void
    {
        $this->get('/login')->assertRedirect('/admin/login');
        $this->assertTrue(app('router')->getRoutes()->getByName('login')->getActionName()!=='Closure');
    }
}
