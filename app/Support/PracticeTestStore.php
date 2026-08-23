<?php

namespace App\Support;

use Illuminate\Support\Str;

class PracticeTestStore
{
    private static function testsPath(): string { return storage_path('app/mci-practice-tests.json'); }
    private static function attemptsPath(): string { return storage_path('app/mci-practice-attempts.json'); }

    public static function all(): array
    {
        $items = self::read(self::testsPath());
        $items=array_map([self::class,'withAssessmentMetadata'],$items);
        usort($items, fn(array $a,array $b): int => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return $items;
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $item) if (($item['id'] ?? '') === $id) return $item;
        return null;
    }

    public static function installStarterSets(array $courseCodes): int
    {
        $allowed=array_flip(array_map('strtoupper',$courseCodes));
        $installed=collect(self::all())->filter(fn(array $test):bool=>!empty($test['starter_key']))->keyBy('starter_key');
        $count=0;
        foreach(StarterPracticeTests::all() as $test){
            if(!isset($allowed[strtoupper($test['course_code'])])) continue;
            $current=$installed->get($test['starter_key']);
            if($current){ self::syncStarterSet($current['id'],$test); }
            else { self::add($test); $count++; }
        }
        return $count;
    }

    private static function syncStarterSet(string $id,array $definition): void
    {
        $items=self::all(); $changed=false;
        foreach($items as &$item){
            if(($item['id']??'')!==$id) continue;
            $preserved=['id'=>$item['id'],'is_active'=>$item['is_active']??true,'created_at'=>$item['created_at']??now()->toIso8601String()];
            $updated=array_merge($definition,$preserved);
            if($item!==$updated){$item=$updated;$changed=true;}
            break;
        }
        unset($item); if($changed) self::write(self::testsPath(),$items);
    }

    public static function add(array $data): array
    {
        $items = self::all();
        $test = array_merge($data, ['id'=>(string) Str::uuid(),'is_active'=>true,'created_at'=>now()->toIso8601String()]);
        array_unshift($items,$test); self::write(self::testsPath(),$items); return $test;
    }

    public static function toggle(string $id): bool
    {
        $items=self::all(); $changed=false;
        foreach($items as &$item) if(($item['id']??'')===$id){$item['is_active']=!($item['is_active']??true);$changed=true;break;}
        unset($item); if($changed) self::write(self::testsPath(),$items); return $changed;
    }

    public static function remove(string $id): bool
    {
        $items=self::all(); $before=count($items);
        $items=array_values(array_filter($items,fn(array $item):bool=>($item['id']??'')!==$id));
        if($before===count($items)) return false;
        self::write(self::testsPath(),$items);
        $attempts=array_values(array_filter(self::attempts(),fn(array $row):bool=>($row['test_id']??'')!==$id));
        self::write(self::attemptsPath(),$attempts); return true;
    }

    public static function attempts(): array
    {
        $items=self::read(self::attemptsPath());
        usort($items,fn(array $a,array $b):int=>strcmp($b['submitted_at']??'',$a['submitted_at']??''));
        return $items;
    }

    public static function attemptsForStudent(string $studentId): array
    {
        return array_values(array_filter(self::attempts(),fn(array $row):bool=>($row['student_id']??'')===$studentId));
    }

    public static function recordAttempt(array $data): array
    {
        $items=self::attempts();
        $attempt=array_merge($data,['id'=>(string) Str::uuid(),'submitted_at'=>now()->toIso8601String()]);
        array_unshift($items,$attempt); self::write(self::attemptsPath(),$items); return $attempt;
    }

    public static function removeAttemptsForStudents(array $studentIds): int
    {
        $items=self::attempts(); $before=count($items);
        $items=array_values(array_filter($items,fn(array $row):bool=>!in_array($row['student_id']??'',$studentIds,true)));
        self::write(self::attemptsPath(),$items); return $before-count($items);
    }

    private static function read(string $path): array
    {
        $items=is_readable($path)?json_decode((string)file_get_contents($path),true):[];
        return is_array($items)?array_values($items):[];
    }

    private static function write(string $path,array $items): void
    {
        file_put_contents($path,json_encode($items,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX);
    }

    private static function withAssessmentMetadata(array $test): array
    {
        if(isset($test['assessment_order'])) return $test;
        if(preg_match('/-set-([1-5])$/',(string)($test['starter_key']??''),$match)){
            $order=(int)$match[1];
            $types=[1=>'practice',2=>'practice',3=>'terminal',4=>'terminal',5=>'final'];
            $weights=[1=>10,2=>10,3=>20,4=>20,5=>40];
            $test['assessment_order']=$order;
            $test['assessment_type']=$types[$order];
            $test['assessment_weight']=$weights[$order];
        }
        return $test;
    }
}
