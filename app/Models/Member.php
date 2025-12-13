<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class , 'team_id','unique_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
