<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name',
        'size',
        'subject_id',
        'organization_id',
        'user_id'
    ];
}
