<?php

namespace Modules\UserManagement\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\UserManagement\Entities\Role;
use Sentinel;

class RoleManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $permissions = [];
		$permissionConfig = config('permissions');
        $roles = Role::get();
		$gg = [];

		$n = 0;
        foreach($permissionConfig as $permission){
            $apa = [];
            foreach($roles as $role){
                $fullOfPermissions = (array) json_decode($role['permissions']);
                $checked = false;
                if( isset($fullOfPermissions[$permission['code']]) && $fullOfPermissions[$permission['code']]){
                    $checked = true;
                    
                    // parent checked condition
                    $gg[$permission['parent'].'_'.$role['slug']] = [
                        'parent'=>$permission['parent'], 
                        'role'=>$role['slug']
                    ];
                }
                $apa[] = [
                    'slug' => $role['slug'],
                    'name' => $role['name'],
                    'checked' => $checked,
                ];

                $permissions[$n] = $permission + ['roles'=>$apa];
            }
            $n++;
        }        

        // parent checked condition
        if($gg){
            foreach($permissions as $index=>$ppp){                
                if($ppp['parent'] == '0'){                    
                    foreach($ppp['roles'] as $indexNull=>$roleNull){
                        if(isset($gg[$ppp['code'].'_'.$roleNull['slug']])){                            
                            $roleNull['checked'] = true;
                            $permissions[$index]['roles'][$indexNull]['checked'] = true;                            
                        }
                    }                    
                }
            }
        }

        #dd($permissions);

		return view('usermanagement::rolemanagement.index', ['roles'=>$roles, 'permissions'=>$permissions]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $posts = $request->except(['_token']);
        $transforms = [
            'index' => 'list',
            'create' => 'store',
            'edit' => 'update',
        ];

        foreach($posts as $k=>$v){            
            $role = Role::where('slug', $k)->first();
            if($role){
                foreach($v as $key=>$val){                
                    $exp = explode('.', $key);
                    if(isset($exp[1]) && in_array($exp[1], ['index','create','edit'])){
                        $v[$exp[0].'.'.$transforms[$exp[1]]] = (bool) $val;                        
                    }
                    $v[$key] = (bool) $val;
                }
                $role->permissions = json_encode($v);
                $role->save();
            }            
        }

        // set new permission to current user
        $user = Sentinel::getUser();
        $role = $user->roles()->first();
        setMenuSession($role);
        
        $request->session()->flash('message', __('usermanagement::admin.create_success'));
        return redirect()->route('rolemanagement.create');
    }
}
