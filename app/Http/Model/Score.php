<?php

namespace App\Http\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    public $timestamps = true;
    public function freshTimestamp() {
        return time();
    }
    public function fromDateTime($value) {
        return $value;
    }
    public function getDateFormat() {
        return 'U';
    }
    protected $fillable = [
        'uId', 'user', 'content','do','total','balance','created_at','updated_at'
    ];
}
