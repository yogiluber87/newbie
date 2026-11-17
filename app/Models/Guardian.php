<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function getRouteKeyName(){
        return 'uri';
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
