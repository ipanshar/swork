<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    protected $fillable = [
        'status_id',
        'organization_id',
        'razbivka',
        'subject_id',
        'description',
        'create_user_id',
        'update_user_id'
    ];
}
