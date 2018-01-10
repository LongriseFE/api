<?php

namespace App\Http\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = [
        'uId', 'user', 'content','do','total','balance','created_at','updated_at'
    ];
}
