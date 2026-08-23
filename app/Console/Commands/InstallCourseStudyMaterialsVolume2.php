<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Support\LearningResourceStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InstallCourseStudyMaterialsVolume2 extends Command
{
    protected $signature = 'mci:install-study-materials-v2 {--force : Re-copy packaged PDF files}';
    protected $description = 'Install detailed MCI Study Pack Volume 2 for every configured course';

    public function handle(): int
    {
        $titles = [
            'ADCA'=>'Advanced Diploma in Computer Applications','EXCEL'=>'Advanced Excel & MIS',
            'AI'=>'AI Tools for Study & Work','CCC'=>'Course on Computer Concepts',
            'DATA'=>'Data Entry & Office Assistant','DIGITAL'=>'Digital Marketing',
            'DCA'=>'Diploma in Computer Applications','DTP'=>'DTP & Graphic Design',
            'HARDWARE'=>'Hardware & Networking','PYTHON'=>'Python Programming',
            'TALLY'=>'Tally Prime with GST','WEB'=>'Web Design & Development',
        ];
        $existing=collect(LearningResourceStore::all())->pluck('seed_key')->filter()->all();
        $installed=0; $skipped=0;

        foreach($titles as $code=>$title){
            if(!Course::where('code',$code)->exists()){ $this->warn("Skipped {$code}: course not found."); $skipped++; continue; }
            $filename=strtolower($code).'-detailed-study-pack-volume-2.pdf';
            $source=resource_path('study-materials/'.$filename);
            $target='learning-materials/mci-'.strtolower($code).'-detailed-v2.pdf';
            $seedKey='mci-detailed-v2-'.strtolower($code);
            if(!is_file($source)){ $this->error("Missing packaged PDF: {$filename}"); return self::FAILURE; }
            if($this->option('force')||!Storage::disk('local')->exists($target)) Storage::disk('local')->put($target,file_get_contents($source));
            if(in_array($seedKey,$existing,true)){ $this->line("Already published: {$code} Volume 2"); $skipped++; continue; }
            LearningResourceStore::add([
                'seed_key'=>$seedKey,'course_code'=>$code,'type'=>'notes',
                'title'=>$title.' - Detailed Chapter Notes & Worked Examples (Volume 2)',
                'description'=>'Four detailed chapters with concept notes, worked examples, lab assignments, review questions and a final applied project.',
                'link_url'=>'','file_path'=>$target,
                'file_name'=>strtoupper($code).'-Detailed-Study-Pack-Volume-2.pdf',
                'file_size'=>filesize($source),'due_date'=>null,'is_pinned'=>false,
            ]);
            $installed++; $this->info("Published: {$code} Volume 2");
        }
        $this->newLine(); $this->info("Volume 2 installed: {$installed}; already available/skipped: {$skipped}.");
        return self::SUCCESS;
    }
}
