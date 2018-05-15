<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Count extends Model
{

    protected $table = 'current_count';

    protected $guarded = ['id'];

    public $timestamps = false;

}
