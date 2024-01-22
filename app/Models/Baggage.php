<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baggage extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'organization_id',
        'user_id',
        'bagstatus_id',
        'cell',
        'description',
    ];
}
