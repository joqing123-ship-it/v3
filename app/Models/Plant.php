<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    /** @use HasFactory<\Database\Factories\PlantFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'diseaseId',
        'image',
        'confidence'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
