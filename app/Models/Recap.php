<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recap extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recap_text',
        'source_journal_ids',
    ];

    protected $casts = [
        'source_journal_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
