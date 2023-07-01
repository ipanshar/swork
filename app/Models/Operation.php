<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;
    protected $fillable = [
        'box_id',
        'application_id',
        'article_id',
        'num',
        'rate_sum',
        'work_sum',
        'salary_id',
        'user_id',
    ];
}
