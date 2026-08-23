<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

class NoticeStore
{
    private static function path(): string
    {
        return storage_path('app/mci-notices.json');
    }

    public static function all(): array
    {
        $items = is_readable(self::path()) ? json_decode((string) file_get_contents(self::path()), true) : [];
        $items = is_array($items) ? array_values($items) : [];

        usort($items, fn (array $a, array $b) => strcmp((string) ($b['notice_date'] ?? ''), (string) ($a['notice_date'] ?? '')));

        return $items;
    }

    public static function published(): array
    {
        $today = Carbon::today()->toDateString();
        $items = array_filter(self::all(), fn (array $item) =>
            (bool) ($item['is_active'] ?? false)
            && (empty($item['expires_at']) || $item['expires_at'] >= $today)
        );
        usort($items, function (array $a, array $b) {
            $pin = ((int) ($b['is_pinned'] ?? 0)) <=> ((int) ($a['is_pinned'] ?? 0));
            return $pin ?: strcmp((string) ($b['notice_date'] ?? ''), (string) ($a['notice_date'] ?? ''));
        });

        return array_values($items);
    }

    public static function add(array $data): void
    {
        $items = self::all();
        array_unshift($items, [
            'id' => (string) Str::uuid(),
            'title' => $data['title'],
            'title_hi' => $data['title_hi'] ?? '',
            'description' => $data['description'] ?? '',
            'type' => $data['type'],
            'notice_date' => $data['notice_date'],
            'expires_at' => $data['expires_at'] ?? null,
            'link' => $data['link'] ?? '',
            'is_pinned' => (bool) ($data['is_pinned'] ?? false),
            'is_active' => true,
            'created_at' => now()->toIso8601String(),
        ]);
        self::write($items);
    }

    public static function toggle(string $id): bool
    {
        $items = self::all();
        $changed = false;
        foreach ($items as &$item) {
            if (($item['id'] ?? '') === $id) {
                $item['is_active'] = ! (bool) ($item['is_active'] ?? false);
                $changed = true;
                break;
            }
        }
        unset($item);
        if ($changed) self::write($items);

        return $changed;
    }

    public static function remove(string $id): bool
    {
        $items = self::all();
        $before = count($items);
        $items = array_values(array_filter($items, fn (array $item) => ($item['id'] ?? '') !== $id));
        if (count($items) === $before) return false;
        self::write($items);

        return true;
    }

    private static function write(array $items): void
    {
        file_put_contents(self::path(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
