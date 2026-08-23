<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Support\LearningResourceStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InstallCourseStudyMaterials extends Command
{
    protected $signature = 'mci:install-study-materials {--force : Re-copy packaged PDF files}';
    protected $description = 'Install MCI Foundation Study Pack Volume 1 for every configured course';

    public function handle(): int
    {
        $titles = [
            'ADCA' => 'Advanced Diploma in Computer Applications',
            'EXCEL' => 'Advanced Excel & MIS',
            'AI' => 'AI Tools for Study & Work',
            'CCC' => 'Course on Computer Concepts',
            'DATA' => 'Data Entry & Office Assistant',
            'DIGITAL' => 'Digital Marketing',
            'DCA' => 'Diploma in Computer Applications',
            'DTP' => 'DTP & Graphic Design',
            'HARDWARE' => 'Hardware & Networking',
            'PYTHON' => 'Python Programming',
            'TALLY' => 'Tally Prime with GST',
            'WEB' => 'Web Design & Development',
        ];

        $existing = collect(LearningResourceStore::all())->pluck('seed_key')->filter()->all();
        $installed = 0;
        $skipped = 0;

        foreach ($titles as $code => $title) {
            if (! Course::where('code', $code)->exists()) {
                $this->warn("Skipped {$code}: course not found.");
                $skipped++;
                continue;
            }

            $filename = strtolower($code).'-foundation-study-pack.pdf';
            $source = resource_path('study-materials/'.$filename);
            $target = 'learning-materials/mci-'.strtolower($code).'-foundation-v1.pdf';
            $seedKey = 'mci-foundation-v1-'.strtolower($code);

            if (! is_file($source)) {
                $this->error("Missing packaged PDF: {$filename}");
                return self::FAILURE;
            }

            if ($this->option('force') || ! Storage::disk('local')->exists($target)) {
                Storage::disk('local')->put($target, file_get_contents($source));
            }

            if (in_array($seedKey, $existing, true)) {
                $this->line("Already published: {$code}");
                $skipped++;
                continue;
            }

            LearningResourceStore::add([
                'seed_key' => $seedKey,
                'course_code' => $code,
                'type' => 'notes',
                'title' => $title.' - Foundation Study Pack (Volume 1)',
                'description' => 'Course roadmap, module notes, practical lab activities, assignments, viva questions, revision guide and assessment rubric.',
                'link_url' => '',
                'file_path' => $target,
                'file_name' => strtoupper($code).'-Foundation-Study-Pack-Volume-1.pdf',
                'file_size' => filesize($source),
                'due_date' => null,
                'is_pinned' => true,
            ]);
            $installed++;
            $this->info("Published: {$code}");
        }

        $this->newLine();
        $this->info("Study materials installed: {$installed}; already available/skipped: {$skipped}.");
        return self::SUCCESS;
    }
}
