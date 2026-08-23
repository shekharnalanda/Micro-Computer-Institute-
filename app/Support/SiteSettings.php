<?php

namespace App\Support;

class SiteSettings
{
    public static function defaults(): array
    {
        return [
            'phone' => '+91 93347 79133, +91 70047 73247',
            'whatsapp' => '919334779133',
            'email' => 'mcieducationalgroup@gmail.com',
            'address_line' => 'MCI Campus, Quamruddin Ganj',
            'city' => 'Bihar Sharif',
            'district' => 'Nalanda',
            'state' => 'Bihar',
            'pin' => '803101',
            'job_location' => 'Bihar Sharif',
            'job_role' => 'Computer Operator',
            'admission_notice' => 'Online & Offline Admissions Open · प्रवेश प्रारंभ',
            'hero_title' => 'Learn computer skills.',
            'hero_highlight' => 'Build your future.',
            'hero_text_en' => 'Industry-relevant computer education, practical lab training, online study resources and career guidance for every learner.',
            'hero_text_hi' => 'व्यावहारिक कंप्यूटर प्रशिक्षण, ऑनलाइन अध्ययन सामग्री और रोजगार मार्गदर्शन के साथ सीखें और आगे बढ़ें।',
            'highlight_two_value' => '100%',
            'highlight_two_label' => 'Practical Learning',
            'highlight_three_value' => 'Online',
            'highlight_three_label' => 'Study Support',
            'why_title' => 'More than a certificate—',
            'why_highlight' => 'a confident skillset.',
            'why_lead' => 'Clear teaching, repeated practice, useful projects and course-wise learning materials.',
        ];
    }

    public static function all(): array
    {
        $path = storage_path('app/mci-settings.json');
        $stored = is_readable($path) ? json_decode((string) file_get_contents($path), true) : [];

        return array_merge(self::defaults(), is_array($stored) ? $stored : []);
    }

    public static function get(string $key, mixed $fallback = null): mixed
    {
        return self::all()[$key] ?? $fallback;
    }

    public static function update(array $data): void
    {
        $path = storage_path('app/mci-settings.json');
        file_put_contents(
            $path,
            json_encode(array_merge(self::all(), $data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }
}
