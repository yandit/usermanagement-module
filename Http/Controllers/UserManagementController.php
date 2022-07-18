<?php

namespace Modules\UserManagement\Http\Controllers;

use Activation;
use Sentinel;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\UserManagement\Emails\AdminActivation;
use Modules\UserManagement\Entities\Role;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Http\Requests\UserProfileRequest;
use Modules\UserManagement\Http\Requests\UserRequest;
use Modules\UserManagement\Transformers\AdminResource;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('usermanagement::admin.index');
    }

    /**
     * Display a listing of the resource.
     * @return 
     */
    public function list(Request $request)
    {
        $allGet = $request->all();
        $data = [];
        $countTable = 0;
        $headerCode = 200;
        $returnErrors = null;

        try{
            $columnIndex = $allGet['order'][0]['column'];
            $searchValue = $allGet['search']['value'];
            $startDate = $allGet['startDate'];
            $endDate = $allGet['endDate'];

            $userModel = User::query()->adminUser();

            if($columnIndex == 0){
                $userModel = $userModel->orderBy('id' , $allGet['order'][0]['dir']);
            }else{
                $userModel = $userModel->orderBy( $allGet['columns'][$columnIndex]['data'] , $allGet['order'][0]['dir']);
            }
            if($searchValue){
                $userModel = $userModel->where('users.name', 'like', "%{$searchValue}%");
            }
            if($startDate && $endDate){
                $userModel = $userModel->whereRaw('DATE(users.created_at) BETWEEN ? AND ?',[$startDate,$endDate]);
            }
            $countTable = $userModel->count();
            $preData = $userModel                
                ->limit($allGet['length'])
                ->offset($allGet['start'])
                ->get();

            $data = AdminResource::collection($preData);

        } catch(\Exception $e){
            $returnErrors = [ ['field'=>'database', 'message'=>'Internal Server Error '.$e->getMessage()] ];
            $headerCode = 500;
        }

        return response()->json(
            [
                'data' => $data,
                'draw' => $allGet['draw'],
                'recordsFiltered' => $countTable,
                'recordsTotal' => $countTable,
                'total_page' => ( (int) (20 / $allGet['length']) ),
                'old_start' => ((int) $allGet['start']),
                'errors' => $returnErrors,
            ],
            $headerCode
        );
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $roleModel = Sentinel::getRoleRepository()->createModel();
        $roles = $roleModel->get();

        return view('usermanagement::admin.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     * @param UserRequest $request
     * @return Renderable
     */
    public function store(UserRequest $request)
    {
        $post = $request->input();
        $role = Sentinel::findRoleById($post['role']);
        if($role){
            $credentials = [
                'email' => $post['email'],
                'password' => time(),
                'name' => $post['name'],
                'phone' => !empty($post['phone']) ? $post['phone'] : '',
                'address' => !empty($post['address']) ? $post['address'] : '',                
            ];
            
            $user = Sentinel::register($credentials);
            if($user){

                $user->name = $post['name'];
                $user->phone = $post['phone'];
                $user->address = $post['address'];
                $user->save();

                $role->users()->attach($user);
                
                // sentinel user activation
                $activation = Activation::create($user);

                // Send Email                
                $params = [
                    'name' => $user->name,
                    'url' => route('activate.form', ['code' => $activation->code]),
                ];

                Mail::to($user)->send(new AdminActivation($params));
                $request->session()->flash('message', __('usermanagement::admin.create_success'));
            }
        }
        return redirect()->route('admin.index');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('usermanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(User $user)
    {
        $roleModel = Sentinel::getRoleRepository()->createModel();
        $roles = $roleModel->get();
        return view('usermanagement::admin.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     * @param UserRequest $request
     * @param User $user
     * @return Renderable
     */
    public function update(UserRequest $request, User $user)
    {
        $post = $request->input();                    
        $user->name = $post['name'];
        $user->phone = !empty($post['phone']) ? $post['phone'] : '';
        $user->address = !empty($post['address']) ? $post['address'] : '';            

        if(!isset($user->role->role_id)){
            $role = Sentinel::findRoleById($post['role']);
            $role->users()->attach($user);
        }else if($user->role->role_id != $post['role']){
            // detach
            $roleDe = Sentinel::findRoleById($user->role->role_id);
            $roleDe->users()->detach($user);

            // attach
            $role = Sentinel::findRoleById($post['role']);
            $role->users()->attach($user);
        }
        $user->save();

        $request->session()->flash('message', __('usermanagement::admin.update_success'));
        return redirect()->route('admin.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function delete(Request $request, User $user)
    {
        $user->delete();
        $request->session()->flash('message', __('usermanagement::admin.delete_success'));
        return redirect()->route('admin.index');
    }

    public function login()
    {
        return view('usermanagement::auth.login');
    }

    public function logout(Request $request){
        Sentinel::logout();
        $request->session()->flush();
        return redirect()->route('admin.login');
    }

    public function doLogin(Request $request)
    {   
        $post = $request->input();
        $message = '';
        try{
            $credentials = [
                'email'    => $post['username'],
                'password' => $post['password'],
            ];
            if($auth = Sentinel::authenticate($credentials)){
                $role = $auth->roles()->first();
                $roleData = collect(config('usermanagement.roles'))
                    ->where('role_slug', $auth->role->slug)
                    ->first();

                setMenuSession($role);
                return redirect()->route($roleData['route']);
            }            
            $message = __('usermanagement::admin.auth_failed');
        }catch(\Cartalyst\Sentinel\Checkpoints\NotActivatedException $e){
            $message = $e->getMessage();
        }catch(\Cartalyst\Sentinel\Checkpoints\ThrottlingException $e){
            $message = $e->getMessage();
        }

        return redirect()
            ->route('admin.login')
            ->withInput()
            ->with(['message'=>$message]);                
    }

    public function activateForm($code, Request $request)
    {
        $message = $request->session()->get('message');
        if(!$message){
            // sentinel set expired. do this.
            $activation = Activation::createModel()->where('code', $code)->first();

            if($activation){
                $user = Sentinel::findById($activation->user_id);
                if ($activation = Activation::completed($user)){
                    $message = __('usermanagement::admin.activation_already_active');
                }
            }else{
                $message = __('usermanagement::admin.activation_not_found');
            }
        }
        return view('usermanagement::admin.active', ['code'=>$code, 'message'=>$message]);                    
    }

    public function activateWithPassword($code, Request $request)
    {
        $activation = Activation::createModel()->where('code', $code)->first();
        $message = __('cms.activation_not_found');
        $post = $request->input();
        if($activation){
            $user = Sentinel::findById($activation->user_id);
            if ($activation = Activation::completed($user)){
                $message = __('cms.activation_already_active');
            }
            else{
                // Activation not found or not completed
                $activate = Activation::complete($user, $code);
                $password = $post['password'];
                Sentinel::update($user, ['password' => $password]);

                if($activate){
                    $message = __('usermanagement::admin.activation_success');
                }else{
                    $message = __('usermanagement::admin.activation_failed');
                }
            }
        }
        
        return redirect()
            ->route('activate.form', ['code'=>$code])
            ->with(['message'=>$message]);
    }

    public function profile()
    {
        $roles = Role::get();
        return view('usermanagement::admin.profile', [
            'user' => Sentinel::getUser(),
            'roles' => $roles,
        ]);
    }

    public function updateProfile(UserProfileRequest $request)
    {
        $user = Sentinel::getUser();
        $user->name = $request->input('name');
        $user->phone = !empty($request->input('phone')) ? $request->input('phone') : '';
        $user->address = !empty($request->input('address')) ? $request->input('address') : '';

        if(!empty($request->input('password')) && !empty($request->input('repeat_password'))){
            if($request->password === $request->repeat_password){
                $user->password = Hash::make($request->password);
            }
        }
        $user->save();

        $request->session()->flash('message', __('usermanagement::admin.update_profile'));
        return redirect()->route('admin.profile');
    }
}
