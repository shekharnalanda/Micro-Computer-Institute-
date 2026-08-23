<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DataBackupService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class BackupController extends Controller
{
    public function index()
    {
        return view('admin.backup.index',['status'=>DataBackupService::status()]);
    }

    public function download()
    {
        $backup=DataBackupService::create();
        $json=json_encode($backup,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return response($json,200,[
            'Content-Type'=>'application/json; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="MCI-Computer-Backup-'.now()->format('Y-m-d-His').'.json"',
            'Cache-Control'=>'no-store, private',
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file'=>['required','file','max:10240'],
            'confirmation'=>['required','in:RESTORE MCI DATA'],
        ]);
        try{
            $decoded=json_decode((string)file_get_contents($request->file('backup_file')->getRealPath()),true,512,JSON_THROW_ON_ERROR);
            $result=DataBackupService::restore($decoded);
        }catch(Throwable $e){
            report($e);
            throw ValidationException::withMessages(['backup_file'=>'Restore failed: '.$e->getMessage()]);
        }
        return back()->with('success',"Backup restored: {$result['files']} data files, {$result['courses']} courses and {$result['enquiries']} enquiries.");
    }
}
