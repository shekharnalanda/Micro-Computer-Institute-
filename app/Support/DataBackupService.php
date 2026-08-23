<?php

namespace App\Support;

use App\Models\Course;
use App\Models\Enquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DataBackupService
{
    public const VERSION=1;
    public const RUNTIME_FILES=[
        'mci-settings.json','mci-gallery.json','mci-notices.json','mci-jobs.json',
        'mci-admissions.json','mci-attendance.json','mci-exam-results.json','mci-certificates.json',
        'mci-learning-resources.json','mci-assignment-submissions.json','mci-practice-tests.json',
        'mci-practice-attempts.json','mci-communication-templates.json',
    ];

    public static function create(): array
    {
        $runtime=[];
        foreach(self::RUNTIME_FILES as $file){
            $path=storage_path('app/'.$file);
            $data=is_readable($path)?json_decode((string)file_get_contents($path),true):[];
            $runtime[$file]=is_array($data)?$data:[];
        }
        $payload=[
            'format'=>'mci-computer-education-backup','version'=>self::VERSION,
            'created_at'=>now()->toIso8601String(),'application'=>config('app.name'),
            'runtime'=>$runtime,
            'database'=>[
                'courses'=>Course::all()->map(fn(Course $model):array=>$model->getAttributes())->all(),
                'enquiries'=>Enquiry::all()->map(fn(Enquiry $model):array=>$model->getAttributes())->all(),
            ],
        ];
        return ['payload'=>$payload,'signature'=>self::sign($payload)];
    }

    public static function verify(array $backup): array
    {
        $payload=$backup['payload']??null; $signature=(string)($backup['signature']??'');
        if(!is_array($payload)||($payload['format']??'')!=='mci-computer-education-backup'||($payload['version']??null)!==self::VERSION)
            throw new RuntimeException('Invalid or unsupported MCI backup file.');
        if(!hash_equals(self::sign($payload),$signature))throw new RuntimeException('Backup signature is invalid. The file may be changed or from another installation.');
        foreach(array_keys((array)($payload['runtime']??[])) as $file)if(!in_array($file,self::RUNTIME_FILES,true))throw new RuntimeException('Backup contains an unsupported data file.');
        return $payload;
    }

    public static function restore(array $backup): array
    {
        $payload=self::verify($backup);
        self::saveSnapshot();
        DB::transaction(function()use($payload):void{
            Course::query()->delete();
            Enquiry::query()->delete();
            if(count($payload['database']['courses']??[]))Course::insert($payload['database']['courses']);
            if(count($payload['database']['enquiries']??[]))Enquiry::insert($payload['database']['enquiries']);
        });
        foreach(self::RUNTIME_FILES as $file){
            $data=$payload['runtime'][$file]??[];
            file_put_contents(storage_path('app/'.$file),json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX);
        }
        return ['files'=>count(self::RUNTIME_FILES),'courses'=>count($payload['database']['courses']??[]),'enquiries'=>count($payload['database']['enquiries']??[])];
    }

    public static function status(): array
    {
        $files=[]; $total=0;
        foreach(self::RUNTIME_FILES as $file){$path=storage_path('app/'.$file);$size=is_file($path)?filesize($path):0;$total+=$size;$files[]=['name'=>$file,'size'=>$size,'exists'=>is_file($path),'modified'=>is_file($path)?filemtime($path):null];}
        return ['files'=>$files,'total_size'=>$total,'courses'=>Course::count(),'enquiries'=>Enquiry::count()];
    }

    private static function saveSnapshot(): void
    {
        $dir=storage_path('app/backups');if(!is_dir($dir))mkdir($dir,0755,true);
        $path=$dir.'/pre-restore-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(4)).'.json';
        file_put_contents($path,json_encode(self::create(),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX);
        $files=glob($dir.'/pre-restore-*.json')?:[];rsort($files);
        foreach(array_slice($files,10) as $old)@unlink($old);
    }

    private static function sign(array $payload): string
    {
        return hash_hmac('sha256',json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),(string)config('app.key'));
    }
}
