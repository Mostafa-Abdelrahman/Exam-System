<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'major_id',
        'specialization',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }
}
