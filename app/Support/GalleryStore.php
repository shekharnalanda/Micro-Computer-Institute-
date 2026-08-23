<?php

namespace App\Support;

use Illuminate\Support\Str;

class GalleryStore
{
    private static function path(): string
    {
        return storage_path('app/mci-gallery.json');
    }

    public static function all(): array
    {
        $items = is_readable(self::path()) ? json_decode((string) file_get_contents(self::path()), true) : [];

        return is_array($items) ? array_values($items) : [];
    }

    public static function published(): array
    {
        return array_values(array_filter(self::all(), fn (array $item) => (bool) ($item['is_active'] ?? false)));
    }

    public static function add(array $data): void
    {
        $items = self::all();
        array_unshift($items, [
            'id' => (string) Str::uuid(),
            'title' => $data['title'],
            'caption' => $data['caption'] ?? '',
            'path' => $data['path'],
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

    public static function remove(string $id): ?array
    {
        $items = self::all();
        $removed = null;
        $items = array_values(array_filter($items, function (array $item) use ($id, &$removed) {
            if (($item['id'] ?? '') === $id) {
                $removed = $item;
                return false;
            }
            return true;
        }));
        if ($removed) self::write($items);

        return $removed;
    }

    private static function write(array $items): void
    {
        file_put_contents(self::path(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
