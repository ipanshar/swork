<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    use HasFactory;
    protected $fillable = [
        'organization_id',
        'service_id',
        'service_count',
        'accrued',
        'salary_id',
        'status_id',
        'user_id',
    ];
}

