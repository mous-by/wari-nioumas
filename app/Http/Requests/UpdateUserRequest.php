<?php

namespace App\Http\Requests;

use App\Rules\MalianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isSuperadmin = $this->route('user')?->hasRole('superadmin');

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', new MalianPhone(), Rule::unique('users', 'phone')->ignore($this->route('user'))],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => $isSuperadmin
                ? ['nullable']
                : ['required', Rule::in(Role::where('name', '!=', 'superadmin')->pluck('name'))],
        ];
    }
}
