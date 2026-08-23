<?php

namespace App\Support;

class AttendanceStore
{
    private static function path(): string
    {
        return storage_path('app/mci-attendance.json');
    }

    public static function all(): array
    {
        $items = is_readable(self::path()) ? json_decode((string) file_get_contents(self::path()), true) : [];
        return is_array($items) ? array_values($items) : [];
    }

    public static function forDate(string $date): array
    {
        $records = [];
        foreach (self::all() as $item) {
            if (($item['date'] ?? '') === $date) $records[$item['student_id']] = $item;
        }
        return $records;
    }

    public static function saveBulk(string $date, array $attendance, array $notes = []): void
    {
        $items = self::all();
        $submittedIds = array_keys($attendance);
        $items = array_values(array_filter($items, fn (array $item) => ($item['date'] ?? '') !== $date || ! in_array($item['student_id'] ?? '', $submittedIds, true)));
        foreach ($attendance as $studentId => $status) {
            $items[] = [
                'student_id' => (string) $studentId,
                'date' => $date,
                'status' => $status,
                'note' => trim((string) ($notes[$studentId] ?? '')),
                'marked_at' => now()->toIso8601String(),
            ];
        }
        file_put_contents(self::path(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
