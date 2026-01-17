<?php

namespace App\Models;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;


    protected $fillable = [
        'post_id',
        'user_id',
        'content',
    ];
    protected $table = 'comments';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
    public function replies(){
        return $this->hasMany(Reply::class);
    }
    public function reports()
    {
        return $this->morphMany(report::class, 'reportable');
    }
}
