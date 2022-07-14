<?php

namespace Modules\UserManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('id');
        $appends = [];
        if($id){            
            $appends['email'] = "required|email|unique:users,email,{$id},id";
        }

        return array_merge([
            'email' => 'required|email|max:50|unique:users,email',
            'name' => 'required|min:2|max:190',
            'role' => 'required|exists:roles,id',
            'phone' => 'required|numeric|digits_between:10,15',
            'address' => 'required|max:250',
        ], $appends);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
