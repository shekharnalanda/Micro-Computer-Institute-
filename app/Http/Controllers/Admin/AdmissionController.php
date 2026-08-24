<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\AdmissionStore;
use App\Support\CertificateStore;
use App\Support\ExamResultStore;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdmissionController extends Controller
{
    public function index(Request $request)
    {
        $allItems = $this->withFinancialDefaults(AdmissionStore::all());
        $items = $allItems;
        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $course = trim((string) $request->query('course'));

        $items = array_values(array_filter($items, function (array $item) use ($search, $status, $course) {
            $haystack = strtolower(($item['application_no'] ?? '').' '.($item['student_name'] ?? '').' '.($item['phone'] ?? '').' '.($item['guardian_name'] ?? ''));
            return (! $search || str_contains($haystack, strtolower($search)))
                && (! $status || ($item['status'] ?? '') === $status)
                && (! $course || ($item['course_code'] ?? '') === $course);
        }));

        return view('admin.admissions.index', [
            'applications' => $items,
            'allApplications' => $allItems,
            'totalFees' => collect($allItems)->sum('course_fee'),
            'totalPaid' => collect($allItems)->sum('paid_amount'),
            'totalBalance' => collect($allItems)->sum('balance_amount'),
        ]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $data = $request->validate(['status' => ['required','in:pending,contacted,verified,admitted,rejected']]);
        abort_unless(AdmissionStore::updateStatus($id, $data['status']), 404);

        return back()->with('success', 'Application status updated.');
    }

    public function updatePayment(Request $request, string $id)
    {
        $data = $request->validate([
            'course_fee' => ['required','numeric','min:0','max:99999999.99'],
            'paid_amount' => ['required','numeric','min:0','lte:course_fee'],
            'payment_note' => ['nullable','string','max:255'],
        ]);
        abort_unless(AdmissionStore::updatePayment($id, (float) $data['course_fee'], (float) $data['paid_amount'], $data['payment_note'] ?? null), 404);

        return back()->with('success', 'Fee record updated successfully.');
    }

    public function addPayment(Request $request, string $id)
    {
        $data = $request->validate([
            'amount' => ['required','numeric','gt:0','max:99999999.99'],
            'payment_date' => ['required','date','before_or_equal:today'],
            'mode' => ['required','in:cash,upi,bank,card,other'],
            'reference' => ['nullable','string','max:100'],
            'note' => ['nullable','string','max:255'],
        ]);
        $item = AdmissionStore::find($id);
        abort_unless($item, 404);
        $item = $this->withFinancialDefaults([$item])[0];
        if ((float) $data['amount'] > (float) $item['balance_amount']) {
            throw ValidationException::withMessages(['amount' => 'Payment cannot be greater than the outstanding balance.']);
        }
        abort_unless(AdmissionStore::addPaymentTransaction(
            $id, (float) $data['amount'], $data['payment_date'], $data['mode'],
            $data['reference'] ?? null, $data['note'] ?? null
        ), 404);

        return back()->with('success', 'New fee payment recorded successfully.');
    }

    public function paymentReceipt(string $id, string $paymentId)
    {
        $item = AdmissionStore::find($id);
        abort_unless($item, 404);
        $application = $this->withFinancialDefaults([$item])[0];
        $payment = collect($application['payments'])->firstWhere('id', $paymentId);
        abort_unless($payment, 404);

        return view('admin.admissions.payment-receipt', [
            'application' => $application,
            'payment' => $payment,
            'course' => Course::where('code', $application['course_code'] ?? '')->first(),
            'settings' => SiteSettings::all(),
        ]);
    }

    public function students(Request $request)
    {
        $items = array_values(array_filter($this->withFinancialDefaults(AdmissionStore::all()), fn (array $item) => ($item['status'] ?? '') === 'admitted'));
        $search = strtolower(trim((string) $request->query('search')));
        $course = trim((string) $request->query('course'));
        $paymentStatus = trim((string) $request->query('payment_status'));
        $batch = strtolower(trim((string) $request->query('batch')));
        $studentStatus = trim((string) $request->query('student_status'));
        $items = array_values(array_filter($items, function (array $item) use ($search, $course, $paymentStatus, $batch, $studentStatus): bool {
            $haystack = strtolower(($item['application_no'] ?? '').' '.($item['student_name'] ?? '').' '.($item['guardian_name'] ?? '').' '.($item['phone'] ?? ''));
            return (! $search || str_contains($haystack, $search))
                && (! $course || ($item['course_code'] ?? '') === $course)
                && (! $paymentStatus || ($item['payment_status'] ?? '') === $paymentStatus)
                && (! $batch || str_contains(strtolower(($item['batch_name'] ?? '').' '.($item['batch_time'] ?? '')), $batch))
                && (! $studentStatus || ($item['student_status'] ?? 'active') === $studentStatus);
        }));

        $latestResults = collect(ExamResultStore::all())->groupBy('student_id')->map->first();
        $latestCertificates = collect(CertificateStore::all())->groupBy('student_id')->map->first();

        return view('admin.students.index', [
            'students' => $items,
            'latestResults' => $latestResults,
            'latestCertificates' => $latestCertificates,
            'studentCount' => count($items),
            'totalPaid' => collect($items)->sum('paid_amount'),
            'totalBalance' => collect($items)->sum('balance_amount'),
            'courses' => Course::orderBy('title')->get(['code','title']),
        ]);
    }

    public function updateStudent(Request $request, string $id)
    {
        $data = $request->validate([
            'roll_no' => ['nullable','string','max:40'],
            'batch_name' => ['nullable','string','max:100'],
            'batch_time' => ['nullable','string','max:100'],
            'joining_date' => ['nullable','date'],
            'student_status' => ['required','in:active,completed,discontinued'],
            'photo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = 'mci-student-'.now()->format('YmdHis').'-'.strtolower(\Illuminate\Support\Str::random(8)).'.'.$photo->getClientOriginalExtension();
            if (! is_dir(public_path('uploads/student-photos'))) mkdir(public_path('uploads/student-photos'), 0755, true);
        $photo->move(public_path('uploads/student-photos'), $photoName);
            $data['photo_path'] = 'uploads/student-photos/'.$photoName;
        }
        abort_unless(AdmissionStore::updateStudentRecord($id, $data), 404);

        return back()->with('success', 'Student academic record updated.');
    }

    public function studentCard(string $id)
    {
        $item = AdmissionStore::find($id);
        abort_unless($item && ($item['status'] ?? '') === 'admitted', 404);
        $student = $this->withFinancialDefaults([$item])[0];

        $student['course_record'] = Course::where('code', $student['course_code'] ?? '')->first();
        return view('admin.students.card', [
            'students' => [$student],
            'settings' => SiteSettings::all(),
        ]);
    }


    public function studentCards(Request $request)
    {
        $ids = array_values(array_unique(array_slice((array) $request->input('ids', []), 0, 150)));
        $students = array_values(array_filter(array_map(fn ($id) => AdmissionStore::find((string) $id), $ids), fn ($item) => $item && ($item['status'] ?? '') === 'admitted'));
        abort_if(empty($students), 422, 'Select at least one admitted student.');
        $students = array_map(function (array $student): array {
            $student['course_record'] = Course::where('code', $student['course_code'] ?? '')->first();
            return $student;
        }, $students);
        return view('admin.students.card', ['students' => $students, 'settings' => SiteSettings::all()]);
    }

    public function feeDues(Request $request)
    {
        $items = array_values(array_filter(
            $this->withFinancialDefaults(AdmissionStore::all()),
            fn (array $item): bool => !($item['is_demo']??false) && ($item['status'] ?? '') === 'admitted' && (float) $item['balance_amount'] > 0
        ));
        $search = strtolower(trim((string) $request->query('search')));
        $course = trim((string) $request->query('course'));
        $paymentStatus = trim((string) $request->query('payment_status'));
        $age = trim((string) $request->query('age'));

        $items = array_values(array_filter($items, function (array $item) use ($search, $course, $paymentStatus, $age): bool {
            $haystack = strtolower(($item['application_no'] ?? '').' '.($item['roll_no'] ?? '').' '.($item['student_name'] ?? '').' '.($item['phone'] ?? ''));
            $start = $item['joining_date'] ?? $item['created_at'] ?? now()->toDateString();
            $days = max(0, now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($start)->startOfDay(), true));
            return (! $search || str_contains($haystack, $search))
                && (! $course || ($item['course_code'] ?? '') === $course)
                && (! $paymentStatus || ($item['payment_status'] ?? '') === $paymentStatus)
                && (! $age || ($age === '30' && $days >= 30) || ($age === '60' && $days >= 60) || ($age === '90' && $days >= 90));
        }));

        $items = array_map(function (array $item): array {
            $start = $item['joining_date'] ?? $item['created_at'] ?? now()->toDateString();
            $item['due_age_days'] = max(0, (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($start)->startOfDay(), true));
            $item['last_payment_date'] = collect($item['payments'])->max('payment_date');
            return $item;
        }, $items);
        usort($items, fn (array $a, array $b): int => $b['due_age_days'] <=> $a['due_age_days']);

        return view('admin.fees.dues', [
            'students' => $items,
            'totalDue' => collect($items)->sum('balance_amount'),
            'unpaidCount' => collect($items)->where('payment_status', 'unpaid')->count(),
            'partialCount' => collect($items)->where('payment_status', 'partial')->count(),
            'courses' => Course::orderBy('title')->get(['code','title']),
        ]);
    }

    public function feeDuesExport(Request $request): StreamedResponse
    {
        $response = $this->feeDues($request);
        $items = $response->getData()['students'];

        return response()->streamDownload(function () use ($items) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Application No','Roll No','Student','Phone','Course','Total Fee','Paid','Balance','Payment Status','Pending Days','Last Payment']);
            foreach ($items as $item) {
                fputcsv($output, [
                    $item['application_no'] ?? '', $item['roll_no'] ?? '', $item['student_name'] ?? '',
                    $item['phone'] ?? '', $item['course_code'] ?? '', $item['course_fee'], $item['paid_amount'],
                    $item['balance_amount'], $item['payment_status'], $item['due_age_days'], $item['last_payment_date'] ?? '',
                ]);
            }
            fclose($output);
        }, 'mci-fee-dues-'.date('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }


    public function feeCollections(Request $request)
    {
        $report = $this->filteredPaymentTransactions($request);

        return view('admin.fees.collections', array_merge($report, [
            'courses' => Course::orderBy('title')->get(['code','title']),
        ]));
    }

    public function feeCollectionsExport(Request $request): StreamedResponse
    {
        $report = $this->filteredPaymentTransactions($request);
        $transactions = $report['transactions'];

        return response()->streamDownload(function () use ($transactions) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Receipt No','Payment Date','Student','Application No','Roll No','Course','Amount','Mode','Reference','Note']);
            foreach ($transactions as $row) {
                fputcsv($output, [
                    $row['receipt_no'], $row['payment_date'], $row['student_name'], $row['application_no'],
                    $row['roll_no'], $row['course_code'], $row['amount'], strtoupper($row['mode']),
                    $row['reference'] ?? '', $row['note'] ?? '',
                ]);
            }
            fclose($output);
        }, 'mci-fee-collections-'.date('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function receipt(string $id)
    {
        $item = AdmissionStore::find($id);
        abort_unless($item, 404);
        $application = $this->withFinancialDefaults([$item])[0];
        $course = Course::where('code', $application['course_code'] ?? '')->first();

        return view('admin.admissions.receipt', [
            'application' => $application,
            'course' => $course,
            'settings' => SiteSettings::all(),
        ]);
    }

    public function destroy(string $id)
    {
        abort_unless(AdmissionStore::remove($id), 404);

        return back()->with('success', 'Application deleted.');
    }

    public function export(): StreamedResponse
    {
        $items = $this->withFinancialDefaults(array_values(array_filter(AdmissionStore::all(),fn(array $item):bool=>!($item['is_demo']??false))));

        return response()->streamDownload(function () use ($items) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Application No','Date','Student','DOB','Gender','Guardian','Phone','Email','Address','City','Qualification','Course','Course Fee','Paid','Balance','Payment Status','Receipt No','Preferred Time','Admission Status']);
            foreach ($items as $item) {
                fputcsv($output, [
                    $item['application_no'] ?? '', $item['created_at'] ?? '', $item['student_name'] ?? '',
                    $item['dob'] ?? '', $item['gender'] ?? '', $item['guardian_name'] ?? '', $item['phone'] ?? '',
                    $item['email'] ?? '', $item['address'] ?? '', $item['city'] ?? '', $item['qualification'] ?? '',
                    $item['course_code'] ?? '', $item['course_fee'], $item['paid_amount'], $item['balance_amount'],
                    $item['payment_status'], $item['receipt_no'] ?? '', $item['preferred_time'] ?? '', $item['status'] ?? '',
                ]);
            }
            fclose($output);
        }, 'mci-admissions-fees-'.date('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }


    private function filteredPaymentTransactions(Request $request): array
    {
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->query('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();
        $course = trim((string) $request->query('course'));
        $mode = trim((string) $request->query('mode'));
        $search = strtolower(trim((string) $request->query('search')));
        $transactions = [];

        foreach ($this->withFinancialDefaults(AdmissionStore::all()) as $application) {
            if($application['is_demo']??false) continue;
            foreach ($application['payments'] as $payment) {
                $date = \Carbon\Carbon::parse($payment['payment_date']);
                $haystack = strtolower(($application['student_name'] ?? '').' '.($application['application_no'] ?? '').' '.($application['roll_no'] ?? '').' '.($payment['receipt_no'] ?? '').' '.($payment['reference'] ?? ''));
                if ($date->lt($from) || $date->gt($to)
                    || ($course && ($application['course_code'] ?? '') !== $course)
                    || ($mode && ($payment['mode'] ?? '') !== $mode)
                    || ($search && ! str_contains($haystack, $search))) {
                    continue;
                }
                $transactions[] = array_merge($payment, [
                    'student_id' => $application['id'],
                    'student_name' => $application['student_name'] ?? '',
                    'application_no' => $application['application_no'] ?? '',
                    'roll_no' => $application['roll_no'] ?? '',
                    'course_code' => $application['course_code'] ?? '',
                ]);
            }
        }
        usort($transactions, fn (array $a, array $b): int => strcmp(($b['payment_date'] ?? '').($b['created_at'] ?? ''), ($a['payment_date'] ?? '').($a['created_at'] ?? '')));
        $collection = collect($transactions);

        return [
            'transactions' => $transactions,
            'totalCollected' => (float) $collection->sum('amount'),
            'transactionCount' => $collection->count(),
            'cashCollected' => (float) $collection->where('mode', 'cash')->sum('amount'),
            'digitalCollected' => (float) $collection->whereIn('mode', ['upi','bank','card'])->sum('amount'),
            'dailyTotals' => $collection->groupBy('payment_date')->map(fn ($rows) => (float) $rows->sum('amount'))->sortKeysDesc(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ];
    }

    private function withFinancialDefaults(array $items): array
    {
        $fees = Course::pluck('fee_amount', 'code');
        return array_map(function (array $item) use ($fees): array {
            $fee = (float) ($item['course_fee'] ?? $fees[$item['course_code'] ?? ''] ?? 0);
            $paid = (float) ($item['paid_amount'] ?? 0);
            $item['course_fee'] = $fee;
            $item['paid_amount'] = $paid;
            $item['balance_amount'] = (float) ($item['balance_amount'] ?? max(0, $fee - $paid));
            $item['payment_status'] = $item['payment_status'] ?? ($paid <= 0 ? 'unpaid' : ($item['balance_amount'] > 0 ? 'partial' : 'paid'));
            $item['payments'] = is_array($item['payments'] ?? null) ? $item['payments'] : [];
            return $item;
        }, $items);
    }
}
