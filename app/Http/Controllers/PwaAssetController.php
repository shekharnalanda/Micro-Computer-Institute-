<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PwaAssetController extends Controller
{
    public function manifest(): BinaryFileResponse
    {
        return response()->file(
            public_path('manifest.webmanifest'),
            [
                'Content-Type' => 'application/manifest+json; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]
        );
    }

    public function serviceWorker(): BinaryFileResponse
    {
        return response()->file(
            public_path('service-worker.js'),
            [
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Service-Worker-Allowed' => '/',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ]
        );
    }

    public function icon192(): BinaryFileResponse
    {
        return response()->file(
            public_path('images/app-icon-192.png'),
            [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public,public, max-age=86400',
            ]
        );
    }

    public function icon512(): BinaryFileResponse
    {
        return response()->file(
            public_path('images/app-icon-512.png'),
            [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }

    public function guidedLabImage(): BinaryFileResponse
    {
        return response()->file(
            public_path('images/mci-guided-practical-lab.webp'),
            [
                'Content-Type' => 'image/webp',
                'Cache-Control' => 'public, max-age=86400',
            ]
        );
    }
}
