<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passive extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'credit',
        'pay_date'
    ];
}
