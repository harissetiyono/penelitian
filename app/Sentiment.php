<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sentiment extends Model
{
    protected $fillable = ['name', 'content','source','sentiment'];
}
