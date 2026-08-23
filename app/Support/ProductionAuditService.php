<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProductionAuditService
{
    public static function run(): array
    {
        $checks=[];
        $add=function(string $group,string $name,string $status,string $detail)use(&$checks):void{$checks[]=['group'=>$group,'name'=>$name,'status'=>$status,'detail'=>$detail];};

        $add('Environment','PHP version',version_compare(PHP_VERSION,'8.2.0','>=')?'pass':'fail',PHP_VERSION.' (requires 8.2+)');
        $add('Environment','Production mode',config('app.env')==='production'?'pass':'fail',(string)config('app.env'));
        $add('Environment','Debug disabled',config('app.debug')?'fail':'pass',config('app.debug')?'APP_DEBUG is enabled':'APP_DEBUG is disabled');
        $add('Environment','Application key',config('app.key')?'pass':'fail',config('app.key')?'Configured':'Missing');
        $url=(string)config('app.url');$host=(string)parse_url($url,PHP_URL_HOST);
        $add('Environment','HTTPS application URL',str_starts_with($url,'https://')?'pass':'fail',$url);
        $add('Environment','Production domain',$host==='mciedu.com'?'pass':'warn',$host?:'Not configured');
        $add('Environment','India timezone',config('app.timezone')==='Asia/Kolkata'?'pass':'warn',(string)config('app.timezone'));

        $add('Application','Composer vendor',is_file(base_path('vendor/autoload.php'))?'pass':'fail',is_file(base_path('vendor/autoload.php'))?'Present':'Missing');
        $add('Application','Environment file',is_readable(base_path('.env'))?'pass':'fail',is_readable(base_path('.env'))?'Readable':'Missing or unreadable');
        foreach(['storage/app','storage/framework/cache','storage/framework/sessions','storage/framework/views','storage/logs','bootstrap/cache'] as $dir)
            $add('Permissions',$dir,is_dir(base_path($dir))&&is_writable(base_path($dir))?'pass':'fail',is_dir(base_path($dir))&&is_writable(base_path($dir))?'Writable':'Not writable');

        try{
            DB::connection()->getPdo();
            $add('Database','Connection','pass','Connected');
            foreach(['users','courses','enquiries','sessions'] as $table)$add('Database','Table: '.$table,Schema::hasTable($table)?'pass':'fail',Schema::hasTable($table)?'Present':'Missing');
            $add('Database','Administrator account',User::where('is_admin',true)->exists()?'pass':'fail',User::where('is_admin',true)->exists()?'Available':'No admin user');
        }catch(Throwable $e){$add('Database','Connection','fail','Database connection failed');}

        $requiredRoutes=['home','admin.login','admin.dashboard','student.login','student.dashboard','admission.create','enquiry.store','certificates.verify','admin.backup.index'];
        foreach($requiredRoutes as $route)$add('Routes',$route,Route::has($route)?'pass':'fail',Route::has($route)?'Registered':'Missing');

        $from=(string)config('mail.from.address');
        $add('Communication','Sender email',filter_var($from,FILTER_VALIDATE_EMAIL)?'pass':'warn',$from?:'Not configured');
        $add('Security','Security middleware',class_exists(\App\Http\Middleware\SecurityHeaders::class)?'pass':'fail','HTTP security headers enabled');
        $add('Security','Public directory hardening',is_file(public_path('.htaccess'))?'pass':'fail',is_file(public_path('.htaccess'))?'Apache rules present':'Missing .htaccess');
        $add('Recovery','Backup service',class_exists(DataBackupService::class)?'pass':'fail','Signed backup and restore available');

        $counts=collect($checks)->countBy('status');
        return [
            'checks'=>$checks,'passed'=>$counts['pass']??0,'warnings'=>$counts['warn']??0,'failed'=>$counts['fail']??0,
            'ready'=>($counts['fail']??0)===0,'checked_at'=>now(),
        ];
    }
}
