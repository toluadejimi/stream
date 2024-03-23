<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    protected $fillable = ['device_name'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
