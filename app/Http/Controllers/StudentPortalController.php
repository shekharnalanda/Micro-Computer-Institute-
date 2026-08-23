<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Support\AdmissionStore;
use App\Support\AttendanceStore;
use App\Support\AssignmentSubmissionStore;
use App\Support\CertificateStore;
use App\Support\CourseAssessment;
use App\Support\ExamResultStore;
use App\Support\LearningResourceStore;
use App\Support\PracticeTestStore;
use App\Support\SiteSettings;
use App\Support\StudentProgress;
use App\Support\StudentNotificationCenter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class StudentPortalController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($this->student($request)) return redirect()->route('student.dashboard');
        return view('student.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'application_no' => ['required','string','max:40'],
            'phone' => ['required','string','max:20'],
        ]);
        $applicationNo = strtoupper(trim($data['application_no']));
        $phone = substr(preg_replace('/\D+/', '', $data['phone']), -10);
        $student = collect(AdmissionStore::all())->first(function (array $item) use ($applicationNo, $phone): bool {
            $registered = substr(preg_replace('/\D+/', '', (string) ($item['phone'] ?? '')), -10);
            return ($item['status'] ?? '') === 'admitted'
                && hash_equals(strtoupper((string) ($item['application_no'] ?? '')), $applicationNo)
                && strlen($phone) === 10 && hash_equals($registered, $phone);
        });
        if (! $student) {
            throw ValidationException::withMessages(['application_no' => 'Application number or registered mobile number is incorrect.']);
        }
        $request->session()->regenerate();
        $request->session()->put('student_portal_id', $student['id']);
        return redirect()->route('student.dashboard');
    }

    public function dashboard(Request $request)
    {
        $student = $this->requireStudent($request);
        PracticeTestStore::installStarterSets([$student['course_code']??'']);
        $fee = (float) ($student['course_fee'] ?? 0);
        $paid = (float) ($student['paid_amount'] ?? 0);
        $student['course_fee'] = $fee;
        $student['paid_amount'] = $paid;
        $student['balance_amount'] = (float) ($student['balance_amount'] ?? max(0, $fee - $paid));
        $student['payment_status'] = $student['payment_status'] ?? ($paid <= 0 ? 'unpaid' : ($student['balance_amount'] > 0 ? 'partial' : 'paid'));
        $student['payments'] = is_array($student['payments'] ?? null) ? array_reverse($student['payments']) : [];

        $attendance = array_values(array_filter(AttendanceStore::all(), fn (array $row): bool => ($row['student_id'] ?? '') === $student['id']));
        usort($attendance, fn (array $a, array $b): int => strcmp($b['date'] ?? '', $a['date'] ?? ''));
        $attendanceCounts = collect($attendance)->countBy('status');
        $marked = count($attendance);
        $attendanceRate = $marked ? round((($attendanceCounts['present'] ?? 0) / $marked) * 100, 1) : 0;

        return view('student.dashboard', [
            'student' => $student,
            'progress' => StudentProgress::calculate($student),
            'notifications' => StudentNotificationCenter::forStudent($student),
            'course' => Course::where('code', $student['course_code'] ?? '')->first(),
            'attendance' => $attendance,
            'attendanceCounts' => $attendanceCounts,
            'attendanceRate' => $attendanceRate,
            'learningResources' => array_values(array_filter(LearningResourceStore::all(), fn (array $row): bool => ($row['is_active'] ?? true) && ($row['course_code'] ?? '') === ($student['course_code'] ?? ''))),
            'assignmentSubmissions' => collect(AssignmentSubmissionStore::forStudent($student['id']))->keyBy('resource_id'),
            'practiceTests' => array_values(array_filter(PracticeTestStore::all(), fn (array $row): bool => ($row['is_active'] ?? true) && ($row['course_code'] ?? '') === ($student['course_code'] ?? ''))),
            'practiceAttempts' => collect(PracticeTestStore::attemptsForStudent($student['id']))->groupBy('test_id')->map(fn($attempts)=>$attempts->sortByDesc('percentage')->first()),
            'assessment' => CourseAssessment::summary($student),
            'results' => array_values(array_filter(ExamResultStore::all(), fn (array $row): bool => ($row['student_id'] ?? '') === $student['id'])),
            'certificates' => array_values(array_filter(CertificateStore::all(), fn (array $row): bool => ($row['student_id'] ?? '') === $student['id'])),
        ]);
    }

    public function submitAssignment(Request $request)
    {
        $student = $this->requireStudent($request);
        $data = $request->validate([
            'resource_id' => ['required','string'],
            'answer_text' => ['nullable','string','max:5000','required_without:submission_url'],
            'submission_url' => ['nullable','url','max:1000','starts_with:http://,https://','required_without:answer_text'],
        ]);
        $resource = collect(LearningResourceStore::all())->first(fn (array $row): bool =>
            ($row['id'] ?? '') === $data['resource_id']
            && ($row['type'] ?? '') === 'assignment'
            && ($row['is_active'] ?? true)
            && ($row['course_code'] ?? '') === ($student['course_code'] ?? '')
        );
        abort_unless($resource, 404);
        AssignmentSubmissionStore::submit([
            'student_id' => $student['id'],
            'resource_id' => $resource['id'],
            'course_code' => $student['course_code'],
            'answer_text' => trim((string) ($data['answer_text'] ?? '')),
            'submission_url' => trim((string) ($data['submission_url'] ?? '')),
        ]);
        return back()->with('success', 'Assignment submitted successfully.');
    }

    public function downloadResource(Request $request, string $id)
    {
        $student = $this->requireStudent($request);
        $resource = LearningResourceStore::find($id);
        abort_unless(
            $resource && ($resource['is_active'] ?? true)
            && ($resource['course_code'] ?? '') === ($student['course_code'] ?? '')
            && ! empty($resource['file_path'])
            && Storage::disk('local')->exists($resource['file_path']),
            404
        );
        return Storage::disk('local')->download($resource['file_path'], $resource['file_name'] ?? 'study-material.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function practiceTest(Request $request, string $id)
    {
        $student=$this->requireStudent($request);
        $test=PracticeTestStore::find($id);
        abort_unless($test&&($test['is_active']??true)&&($test['course_code']??'')===($student['course_code']??''),404);
        return view('student.practice-test',['student'=>$student,'test'=>$test]);
    }

    public function submitPracticeTest(Request $request, string $id)
    {
        $student=$this->requireStudent($request);
        $test=PracticeTestStore::find($id);
        abort_unless($test&&($test['is_active']??true)&&($test['course_code']??'')===($student['course_code']??''),404);
        $data=$request->validate(['answers'=>['nullable','array'],'answers.*'=>['nullable','in:A,B,C,D']]);
        $answers=(array)($data['answers']??[]); $correct=0; $review=[];
        foreach($test['questions'] as $question){
            $selected=$answers[$question['id']]??null; $isCorrect=$selected===($question['correct']??null);
            if($isCorrect)$correct++;
            $review[]=['question_id'=>$question['id'],'selected'=>$selected,'correct'=>$question['correct'],'is_correct'=>$isCorrect];
        }
        $total=count($test['questions']); $percentage=$total?round(($correct/$total)*100,2):0;
        $attempt=PracticeTestStore::recordAttempt([
            'test_id'=>$test['id'],'student_id'=>$student['id'],'course_code'=>$student['course_code'],
            'correct_answers'=>$correct,'total_questions'=>$total,'percentage'=>$percentage,
            'status'=>$percentage>=(float)$test['pass_percentage']?'pass':'fail','review'=>$review,
        ]);
        CourseAssessment::publishIfEligible($student);
        return redirect()->route('student.practice.result',$attempt['id']);
    }

    public function practiceResult(Request $request, string $attemptId)
    {
        $student=$this->requireStudent($request);
        $attempt=collect(PracticeTestStore::attemptsForStudent($student['id']))->firstWhere('id',$attemptId);
        abort_unless($attempt,404);
        $test=PracticeTestStore::find($attempt['test_id']);
        abort_unless($test,404);
        return view('student.practice-result',['student'=>$student,'test'=>$test,'attempt'=>$attempt,'assessment'=>CourseAssessment::summary($student)]);
    }

    public function marksheet(Request $request, string $id)
    {
        $student = $this->requireStudent($request);
        $result = ExamResultStore::find($id);
        abort_unless($result && ($result['student_id'] ?? '') === $student['id'], 404);
        return view('admin.results.marksheet', [
            'result' => $result, 'student' => $student,
            'course' => Course::where('code', $student['course_code'] ?? '')->first(),
            'settings' => SiteSettings::all(),
        ]);
    }

    public function certificate(Request $request, string $id)
    {
        $student = $this->requireStudent($request);
        $certificate = CertificateStore::find($id);
        abort_unless($certificate && ($certificate['student_id'] ?? '') === $student['id'], 404);
        return view('admin.certificates.print', [
            'certificate' => $certificate, 'student' => $student,
            'course' => Course::where('code', $student['course_code'] ?? '')->first(),
            'settings' => SiteSettings::all(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('student_portal_id');
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect()->route('student.login');
    }

    private function student(Request $request): ?array
    {
        $id = (string) $request->session()->get('student_portal_id', '');
        $student = $id ? AdmissionStore::find($id) : null;
        return $student && ($student['status'] ?? '') === 'admitted' ? $student : null;
    }

    private function requireStudent(Request $request): array
    {
        $student = $this->student($request);
        if (! $student) {
            $request->session()->forget('student_portal_id');
            throw new HttpResponseException(redirect()->route('student.login'));
        }
        return $student;
    }
}
