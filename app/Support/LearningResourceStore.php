<?php

namespace App\Support;

use Illuminate\Support\Str;

class LearningResourceStore
{
    private static function path(): string
    {
        return storage_path('app/mci-learning-resources.json');
    }

    public static function all(): array
    {
        $items = is_readable(self::path()) ? json_decode((string) file_get_contents(self::path()), true) : [];
        $items = is_array($items) ? array_values($items) : [];
        usort($items, fn (array $a, array $b): int => (($b['is_pinned'] ?? false) <=> ($a['is_pinned'] ?? false)) ?: strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return $items;
    }

    public static function add(array $data): array
    {
        $items = self::all();
        $item = array_merge($data, [
            'id' => (string) Str::uuid(),
            'is_active' => true,
            'created_at' => now()->toIso8601String(),
        ]);
        array_unshift($items, $item);
        self::write($items);
        return $item;
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $item) {
            if (($item['id'] ?? '') === $id) return $item;
        }
        return null;
    }

    public static function toggle(string $id): bool
    {
        return self::update($id, function (array $item): array {
            $item['is_active'] = ! ($item['is_active'] ?? true);
            return $item;
        });
    }

    public static function remove(string $id): bool
    {
        $items = self::all();
        $before = count($items);
        $items = array_values(array_filter($items, fn (array $item): bool => ($item['id'] ?? '') !== $id));
        if ($before === count($items)) return false;
        self::write($items);
        return true;
    }

    private static function update(string $id, callable $callback): bool
    {
        $items = self::all();
        $changed = false;
        foreach ($items as &$item) {
            if (($item['id'] ?? '') === $id) {
                $item = $callback($item);
                $item['updated_at'] = now()->toIso8601String();
                $changed = true;
                break;
            }
        }
        unset($item);
        if ($changed) self::write($items);
        return $changed;
    }

    private static function write(array $items): void
    {
        file_put_contents(self::path(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
