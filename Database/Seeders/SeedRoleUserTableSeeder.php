<?php

namespace Modules\UserManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        DB::table('roles')->insert([
            'slug' => 'superadmin',
            'name' => 'Superadmin',
            'permissions' => '{"admin.create":true,"admin.index":true,"admin.store":true,"admin.list":true,"admin.edit":true,"admin.delete":true,"role.create":true,"role.index":true,"role.edit":true,"role.delete":true,"rolemanagement.create":true,"role.store":true,"role.list":true,"role.update":true,"rolemanagement.store":true}',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);  
    }
}
