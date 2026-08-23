<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Support\GalleryStore;
use App\Support\NoticeStore;
use App\Support\JobStore;
use App\Support\SiteSettings;
use Database\Seeders\DatabaseSeeder;
use Throwable;

class HomeController extends Controller
{
    public function index()
    {
        try {
            if (! Course::query()->exists() || ! User::query()->where('is_admin', true)->exists()) {
                $seeder = app()->make(DatabaseSeeder::class);
                $seeder->setContainer(app());
                $seeder->run();
            }

            $courses = Course::where('is_active', true)->orderBy('sort_order')->orderBy('title')->get();

            $settings = SiteSettings::all();
            $gallery = GalleryStore::published();
            $notices = NoticeStore::published();
            $jobs = JobStore::published();

            return view('home', compact('courses', 'settings', 'gallery', 'notices', 'jobs'));
        } catch (Throwable $exception) {
            return $this->setupErrorResponse($exception->getMessage());
        }
    }

    private function setupErrorResponse(string $message)
    {
        $safeMessage = preg_replace(
            '/(password|pwd)\s*[=:]\s*[^\s,;]+/i',
            '$1=[hidden]',
            $message
        );
        $safeMessage = e(substr((string) $safeMessage, 0, 1200));
        $environment = e(app()->environment());

        $html = <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MCI setup needs attention</title>
    <style>
        body{margin:0;background:#eef4fb;color:#17324d;font-family:Arial,sans-serif}
        main{max-width:760px;margin:8vh auto;padding:32px;background:#fff;border-radius:18px;box-shadow:0 12px 40px #17324d22}
        h1{color:#075cab} code,pre{background:#f3f6f9;border-radius:8px}
        pre{padding:16px;white-space:pre-wrap;word-break:break-word;border-left:4px solid #f59e0b}
    </style>
</head>
<body><main>
    <h1>MCI setup needs attention</h1>
    <p>Laravel is running, but database initialization needs attention.</p>
    <p><strong>Environment:</strong> <code>{$environment}</code></p>
    <pre>{$safeMessage}</pre>
</main></body></html>
HTML;

        return response($html, 503, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
