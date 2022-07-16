<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleUser extends Model
{
    use HasFactory;
    protected $table = 'role_users';
    protected $fillable = [
        'role_id',
    ];
    protected $primaryKey = 'user_id';
}
