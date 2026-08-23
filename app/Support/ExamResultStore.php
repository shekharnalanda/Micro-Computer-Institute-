<?php

namespace App\Support;

use Illuminate\Support\Str;

class ExamResultStore
{
    private static function path(): string
    {
        return storage_path('app/mci-exam-results.json');
    }

    public static function all(): array
    {
        $items = is_readable(self::path()) ? json_decode((string) file_get_contents(self::path()), true) : [];
        $items = is_array($items) ? array_values($items) : [];
        usort($items, fn (array $a, array $b): int => strcmp(($b['exam_date'] ?? '').($b['created_at'] ?? ''), ($a['exam_date'] ?? '').($a['created_at'] ?? '')));
        return $items;
    }

    public static function find(string $id): ?array
    {
        return collect(self::all())->firstWhere('id', $id);
    }

    public static function add(array $data): array
    {
        $items = self::all();
        $item = array_merge($data, [
            'id' => (string) Str::uuid(),
            'result_no' => 'MCI-RESULT-'.now()->format('ymd').'-'.strtoupper(Str::random(5)),
            'created_at' => now()->toIso8601String(),
        ]);
        array_unshift($items, $item);
        self::write($items);
        return $item;
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
        self::write($items); return $before-count($items);
    }

    private static function write(array $items): void
    {
        file_put_contents(self::path(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
