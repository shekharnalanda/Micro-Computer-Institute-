<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\LearningResourceStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LearningResourceController extends Controller
{
    public function index(Request $request)
    {
        $search = strtolower(trim((string) $request->query('search')));
        $course = trim((string) $request->query('course'));
        $type = trim((string) $request->query('type'));
        $items = array_values(array_filter(LearningResourceStore::all(), function (array $item) use ($search, $course, $type): bool {
            $haystack = strtolower(($item['title'] ?? '').' '.($item['description'] ?? '').' '.($item['course_code'] ?? ''));
            return (! $search || str_contains($haystack, $search))
                && (! $course || ($item['course_code'] ?? '') === $course)
                && (! $type || ($item['type'] ?? '') === $type);
        }));

        return view('admin.learning.index', [
            'resources' => $items,
            'courses' => Course::orderBy('title')->get(['code','title']),
            'activeCount' => collect($items)->where('is_active', true)->count(),
            'assignmentCount' => collect($items)->where('type', 'assignment')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_code' => ['required','string','exists:courses,code'],
            'type' => ['required','in:notes,video,assignment,practice,link'],
            'title' => ['required','string','max:180'],
            'description' => ['nullable','string','max:1000'],
            'link_url' => ['nullable','url','max:1000','starts_with:http://,https://','required_without:material_file'],
            'material_file' => ['nullable','file','mimes:pdf','max:25600','required_without:link_url'],
            'due_date' => ['nullable','date'],
            'is_pinned' => ['nullable','boolean'],
        ]);
        unset($data['material_file']);
        if ($request->hasFile('material_file')) {
            $file = $request->file('material_file');
            $data['file_path'] = $file->storeAs('learning-materials', Str::uuid().'.pdf', 'local');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['link_url'] = '';
        }
        $data['is_pinned'] = $request->boolean('is_pinned');
        LearningResourceStore::add($data);
        return back()->with('success', 'Learning resource published successfully.');
    }

    public function toggle(string $id)
    {
        abort_unless(LearningResourceStore::toggle($id), 404);
        return back()->with('success', 'Resource visibility updated.');
    }

    public function destroy(string $id)
    {
        $resource = LearningResourceStore::find($id);
        abort_unless(LearningResourceStore::remove($id), 404);
        if (! empty($resource['file_path'])) Storage::disk('local')->delete($resource['file_path']);
        return back()->with('success', 'Learning resource deleted.');
    }

    public function download(string $id)
    {
        $resource = LearningResourceStore::find($id);
        abort_unless($resource && ! empty($resource['file_path']) && Storage::disk('local')->exists($resource['file_path']), 404);
        return Storage::disk('local')->download($resource['file_path'], $resource['file_name'] ?? 'study-material.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
