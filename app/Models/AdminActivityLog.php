<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function log($action, $description)
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            self::create([
                'admin_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }
    }
}
