<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Journal extends Model
{
    use HasFactory, SoftDeletes; 

    protected $fillable = [
        'user_id',
        'mood',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
