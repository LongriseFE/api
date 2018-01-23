<?php

namespace App\Http\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
        'uId', 'area', 'name','total','year','grade','gift','created_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
