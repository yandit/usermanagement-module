<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Cartalyst\Sentinel\Native\Facades\Sentinel;

class SeedRoleUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Sentinel::getRoleRepository()->createModel()->create([
            'name' => 'Superadmin',
            'slug' => 'superadmin',
        ]);
    }
}
