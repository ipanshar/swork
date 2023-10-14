<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Smena extends Model
{
    use HasFactory;
    protected $fillable = [
        'personal_id',
        'oklad',
        'percent',
        'acrued',
        'user_id'
    ];
}
