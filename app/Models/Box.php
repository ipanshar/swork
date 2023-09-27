<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'application_id',
        'rate',
        'salary_id',
        'user_id',
        'organization_id',
        'status_id',
    ];
}
