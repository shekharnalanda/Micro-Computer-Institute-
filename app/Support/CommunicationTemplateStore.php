<?php

namespace App\Support;

use Illuminate\Support\Str;

class CommunicationTemplateStore
{
    private static function path(): string { return storage_path('app/mci-communication-templates.json'); }

    public static function all(): array
    {
        $items=is_readable(self::path())?json_decode((string)file_get_contents(self::path()),true):[];
        if(!is_array($items)||!count($items)){ $items=self::starters(); self::write($items); }
        return array_values($items);
    }

    public static function find(string $id): ?array
    {
        foreach(self::all() as $item)if(($item['id']??'')===$id)return $item;
        return null;
    }

    public static function add(array $data): array
    {
        $items=self::all(); $item=array_merge($data,['id'=>(string)Str::uuid(),'created_at'=>now()->toIso8601String()]);
        array_unshift($items,$item); self::write($items); return $item;
    }

    public static function remove(string $id): bool
    {
        $items=self::all(); $before=count($items);
        $items=array_values(array_filter($items,fn(array $item):bool=>($item['id']??'')!==$id));
        if($before===count($items))return false; self::write($items); return true;
    }

    public static function render(array $template,array $student): array
    {
        $replace=[
            '{student_name}'=>$student['student_name']??'Student',
            '{course}'=>$student['course_code']??'',
            '{application_no}'=>$student['application_no']??'',
            '{balance}'=>'₹'.number_format((float)($student['balance_amount']??0),2),
            '{portal_url}'=>route('student.login'),
        ];
        return ['subject'=>strtr($template['subject']??'Micro Computer Institute',$replace),'body'=>strtr($template['body']??'',$replace)];
    }

    private static function starters(): array
    {
        $now=now()->toIso8601String();
        return [
            ['id'=>'starter-fee','name'=>'Fee Due Reminder','channel'=>'both','category'=>'fee','subject'=>'MCI Fee Reminder','body'=>'Dear {student_name}, your pending course fee is {balance}. Please contact Micro Computer Institute. Student Portal: {portal_url}','created_at'=>$now],
            ['id'=>'starter-assignment','name'=>'Assignment Reminder','channel'=>'both','category'=>'assignment','subject'=>'Assignment Reminder – {course}','body'=>'Dear {student_name}, please check and submit your pending assignments in the MCI Student Portal: {portal_url}','created_at'=>$now],
            ['id'=>'starter-result','name'=>'Result Published','channel'=>'both','category'=>'result','subject'=>'Your MCI result is available','body'=>'Dear {student_name}, your latest result is now available in the Student Portal: {portal_url}','created_at'=>$now],
            ['id'=>'starter-general','name'=>'General Student Message','channel'=>'both','category'=>'general','subject'=>'Important update from MCI','body'=>'Dear {student_name}, an important update is available for your {course} course. Please visit: {portal_url}','created_at'=>$now],
        ];
    }

    private static function write(array $items): void
    {
        file_put_contents(self::path(),json_encode($items,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX);
    }
}
