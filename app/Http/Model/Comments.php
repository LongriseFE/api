<?php

namespace App\Http\Model;
use Illuminate\Notifications\Notifiable;
use App\Http\Model\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class Comments extends Model
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
        'uId', 'topicId', 'topicType','parentId','fromId','toId','content','created_at','updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];
    public function childCategory() {
      $comments = $this->hasMany('App\Http\Model\Comments', 'parentId', 'uId')
      ->join('users', function ($join) {
        $join->on('comments.fromId', '=', 'users.uId');
      })->select('users.name as fromname', 'users.cover as fromface', 'comments.*');
      return $comments;
    }

    public function children()
    {
        return $this->childCategory()->with('children');
    }
}
