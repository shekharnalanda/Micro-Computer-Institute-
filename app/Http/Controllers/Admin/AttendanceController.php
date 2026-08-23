<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\AdmissionStore;
use App\Support\AttendanceStore;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $this->date($request);
        $batch = strtolower(trim((string) $request->query('batch')));
        $students = $this->students($batch);
        $records = AttendanceStore::forDate($date);
        $counts = collect($records)->countBy('status');

        return view('admin.attendance.index', [
            'date' => $date,
            'batch' => $request->query('batch', ''),
            'students' => $students,
            'records' => $records,
            'presentCount' => $counts['present'] ?? 0,
            'absentCount' => $counts['absent'] ?? 0,
            'leaveCount' => $counts['leave'] ?? 0,
            'unmarkedCount' => max(0, count($students) - count($records)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required','date'],
            'attendance' => ['required','array'],
            'attendance.*' => ['required', Rule::in(['present','absent','leave'])],
            'notes' => ['nullable','array'],
            'notes.*' => ['nullable','string','max:160'],
            'batch' => ['nullable','string','max:100'],
        ]);
        AttendanceStore::saveBulk($data['date'], $data['attendance'], $data['notes'] ?? []);

        return redirect()->route('admin.attendance.index', array_filter(['date' => $data['date'], 'batch' => $data['batch'] ?? null]))
            ->with('success', 'Attendance saved for '.Carbon::parse($data['date'])->format('d M Y').'.');
    }

    public function export(Request $request): StreamedResponse
    {
        $date = $this->date($request);
        $students = $this->students(strtolower(trim((string) $request->query('batch'))));
        $records = AttendanceStore::forDate($date);

        return response()->streamDownload(function () use ($date, $students, $records) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Date','Roll No','Student','Application No','Course','Batch','Batch Time','Status','Note']);
            foreach ($students as $student) {
                $record = $records[$student['id']] ?? [];
                fputcsv($output, [
                    $date, $student['roll_no'] ?? '', $student['student_name'] ?? '', $student['application_no'] ?? '',
                    $student['course_code'] ?? '', $student['batch_name'] ?? '', $student['batch_time'] ?? '',
                    $record['status'] ?? 'unmarked', $record['note'] ?? '',
                ]);
            }
            fclose($output);
        }, 'mci-attendance-'.$date.'.csv', ['Content-Type' => 'text/csv']);
    }

    public function monthly(Request $request)
    {
        return view('admin.attendance.report', $this->monthlyData($request));
    }

    public function monthlyExport(Request $request): StreamedResponse
    {
        $data = $this->monthlyData($request);

        return response()->streamDownload(function () use ($data) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Month','Roll No','Student','Application No','Course','Batch','Present','Absent','Leave','Marked Days','Attendance Percentage']);
            foreach ($data['rows'] as $row) {
                fputcsv($output, [
                    $data['month'], $row['student']['roll_no'] ?? '', $row['student']['student_name'] ?? '',
                    $row['student']['application_no'] ?? '', $row['student']['course_code'] ?? '',
                    $row['student']['batch_name'] ?? '', $row['present'], $row['absent'], $row['leave'],
                    $row['marked'], $row['percentage'].'%',
                ]);
            }
            fclose($output);
        }, 'mci-monthly-attendance-'.$data['month'].'.csv', ['Content-Type' => 'text/csv']);
    }

    private function monthlyData(Request $request): array
    {
        $month = (string) $request->query('month', now()->format('Y-m'));
        try {
            $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        } catch (\Throwable) {
            $start = now()->startOfMonth();
            $month = $start->format('Y-m');
        }
        $end = $start->copy()->endOfMonth();
        $course = trim((string) $request->query('course'));
        $batch = strtolower(trim((string) $request->query('batch')));
        $students = array_values(array_filter(AdmissionStore::all(), function (array $student) use ($course, $batch): bool {
            $batchText = strtolower(($student['batch_name'] ?? '').' '.($student['batch_time'] ?? ''));
            return ($student['status'] ?? '') === 'admitted'
                && (! $course || ($student['course_code'] ?? '') === $course)
                && (! $batch || str_contains($batchText, $batch));
        }));
        $records = array_values(array_filter(AttendanceStore::all(), fn (array $record) => ($record['date'] ?? '') >= $start->toDateString() && ($record['date'] ?? '') <= $end->toDateString()));
        $byStudent = collect($records)->groupBy('student_id');
        $rows = array_map(function (array $student) use ($byStudent): array {
            $records = collect($byStudent->get($student['id'], []));
            $present = $records->where('status', 'present')->count();
            $absent = $records->where('status', 'absent')->count();
            $leave = $records->where('status', 'leave')->count();
            $marked = $records->count();
            return [
                'student' => $student,
                'present' => $present,
                'absent' => $absent,
                'leave' => $leave,
                'marked' => $marked,
                'percentage' => $marked > 0 ? round(($present / $marked) * 100, 1) : 0,
            ];
        }, $students);

        return [
            'month' => $month,
            'monthLabel' => $start->format('F Y'),
            'course' => $course,
            'batch' => $request->query('batch', ''),
            'courses' => Course::orderBy('title')->get(['code','title']),
            'rows' => $rows,
            'studentCount' => count($rows),
            'presentTotal' => collect($rows)->sum('present'),
            'absentTotal' => collect($rows)->sum('absent'),
            'leaveTotal' => collect($rows)->sum('leave'),
            'averagePercentage' => count($rows) ? round(collect($rows)->avg('percentage'), 1) : 0,
        ];
    }

    private function students(string $batch = ''): array
    {
        return array_values(array_filter(AdmissionStore::all(), function (array $student) use ($batch): bool {
            $isActive = ($student['status'] ?? '') === 'admitted' && ($student['student_status'] ?? 'active') === 'active';
            $batchText = strtolower(($student['batch_name'] ?? '').' '.($student['batch_time'] ?? ''));
            return $isActive && (! $batch || str_contains($batchText, $batch));
        }));
    }

    private function date(Request $request): string
    {
        try {
            return Carbon::parse((string) $request->query('date', today()->toDateString()))->toDateString();
        } catch (\Throwable) {
            return today()->toDateString();
        }
    }
}
