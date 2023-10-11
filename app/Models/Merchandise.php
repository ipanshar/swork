<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchandise extends Model
{
    use HasFactory;
    protected $fillable = [
        'organization_id',
        'service_id',
        'service_count',
        'price',
        'rate',
        'accrued',
        'salary_id',
        'status_id',
        'user_id',
    ];
}
