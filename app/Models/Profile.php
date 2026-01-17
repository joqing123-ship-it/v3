<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'profile_image',
        'phone',
        'description',
        'user_id',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
