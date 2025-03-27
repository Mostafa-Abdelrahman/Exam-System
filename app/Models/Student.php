<?php

namespace App\Models; // Ensure this is correct

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'major_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id','id');
    }
}
