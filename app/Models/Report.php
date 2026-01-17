<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reason',
        'resolved',
    ];
    public function reportable()
    {
        return $this->morphTo();
    }
    public function user()
    {
     return $this->belongsTo(User::class);

    }
}
