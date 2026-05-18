<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LendITTag extends Model
{
    protected $table = 'lendit_tags';

    protected $fillable = [
        'name',
        'slug',
    ];
}
