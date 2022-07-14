<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',        
        'permissions',
        'last_login',
        'first_name',
        'last_name',

        'name',
        'phone',
        'address',
    ];

    public function role()
    {
        return $this->hasOne(RoleUser::class);
    }


    public function scopeAdminUser($query){
        $query
            ->select('users.*','activations.completed')
            ->join('role_users', 'role_users.user_id', 'users.id')
            ->join('roles', 'roles.id', 'role_users.role_id')
            ->join('activations', 'activations.user_id', 'users.id');
            // ->whereNotIn('roles.slug', config('user.public'));
        return $query;
    }
    
}
