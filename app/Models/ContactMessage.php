<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'address',
        'query',
        'ip_address',
        'user_agent',
    ];
}
