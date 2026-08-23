<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $enquiries = $this->filteredQuery($request)->latest()->paginate(25)->withQueryString();

        return view('admin.enquiries.index', [
            'enquiries' => $enquiries,
            'courses' => Enquiry::whereNotNull('course_code')->where('course_code', '!=', '')->distinct()->orderBy('course_code')->pluck('course_code'),
            'totalCount' => Enquiry::count(),
            'newCount' => Enquiry::where('status', 'new')->count(),
            'contactedCount' => Enquiry::where('status', 'contacted')->count(),
            'closedCount' => Enquiry::where('status', 'closed')->count(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'mci-enquiries-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Student', 'Phone', 'Email', 'City', 'Course', 'Status', 'Message']);

            $this->filteredQuery($request)->latest()->chunk(250, function ($enquiries) use ($handle): void {
                foreach ($enquiries as $enquiry) {
                    fputcsv($handle, [
                        $enquiry->created_at?->format('Y-m-d H:i'),
                        $this->safeCsv($enquiry->name),
                        $this->safeCsv($enquiry->phone),
                        $this->safeCsv($enquiry->email),
                        $this->safeCsv($enquiry->city),
                        $this->safeCsv($enquiry->course_code),
                        $enquiry->status,
                        $this->safeCsv($enquiry->message),
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function updateStatus(Request $request, Enquiry $enquiry)
    {
        $data = $request->validate(['status' => ['required', 'in:new,contacted,closed']]);
        $enquiry->update($data);

        return back()->with('success', 'Enquiry status updated.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return back()->with('success', 'Enquiry deleted.');
    }

    private function filteredQuery(Request $request): Builder
    {
        return Enquiry::query()
            ->when($request->filled('q'), function (Builder $query) use ($request): void {
                $term = trim((string) $request->q);
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('city', 'like', "%{$term}%")
                        ->orWhere('course_code', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->status))
            ->when($request->filled('course'), fn (Builder $query) => $query->where('course_code', $request->course))
            ->when($request->filled('from'), fn (Builder $query) => $query->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn (Builder $query) => $query->whereDate('created_at', '<=', $request->to));
    }

    private function safeCsv(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
}
