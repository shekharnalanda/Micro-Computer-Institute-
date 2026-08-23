<?php

namespace App\Support;

use Illuminate\Support\Str;

class AssignmentSubmissionStore
{
    private static function path(): string
    {
        return storage_path('app/mci-assignment-submissions.json');
    }

    public static function all(): array
    {
        $items = is_readable(self::path()) ? json_decode((string) file_get_contents(self::path()), true) : [];
        $items = is_array($items) ? array_values($items) : [];
        usort($items, fn (array $a, array $b): int => strcmp($b['submitted_at'] ?? '', $a['submitted_at'] ?? ''));
        return $items;
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $item) if (($item['id'] ?? '') === $id) return $item;
        return null;
    }

    public static function forStudent(string $studentId): array
    {
        return array_values(array_filter(self::all(), fn (array $item): bool => ($item['student_id'] ?? '') === $studentId));
    }

    public static function submit(array $data): array
    {
        $items = self::all();
        foreach ($items as &$item) {
            if (($item['student_id'] ?? '') === $data['student_id'] && ($item['resource_id'] ?? '') === $data['resource_id']) {
                $item = array_merge($item, $data, [
                    'status' => 'submitted', 'marks' => null, 'feedback' => null,
                    'submitted_at' => now()->toIso8601String(), 'reviewed_at' => null,
                ]);
                self::write($items);
                return $item;
            }
        }
        unset($item);
        $item = array_merge($data, [
            'id' => (string) Str::uuid(), 'status' => 'submitted',
            'marks' => null, 'feedback' => null,
            'submitted_at' => now()->toIso8601String(), 'reviewed_at' => null,
        ]);
        array_unshift($items, $item);
        self::write($items);
        return $item;
    }

    public static function review(string $id, array $data): bool
    {
        $items = self::all();
        $changed = false;
        foreach ($items as &$item) {
            if (($item['id'] ?? '') === $id) {
                $item['status'] = 'reviewed';
                $item['marks'] = $data['marks'];
                $item['feedback'] = $data['feedback'] ?? null;
                $item['reviewed_at'] = now()->toIso8601String();
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
        $items = array_values(array_filter($items, fn (array $item): bool => ($item['id'] ?? '') !== $id));
        if ($before === count($items)) return false;
        self::write($items);
        return true;
    }

    public static function removeForStudents(array $studentIds): int
    {
        $items=self::all(); $before=count($items);
        $items=array_values(array_filter($items,fn(array $item):bool=>!in_array($item['student_id']??'',$studentIds,true)));
        self::write($items);
        return $before-count($items);
    }

    private static function write(array $items): void
    {
        file_put_contents(self::path(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
