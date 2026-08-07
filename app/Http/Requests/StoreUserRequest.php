<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Propaganistas\LaravelPhone\Rules\Phone;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', (new Phone())->country('ML'), 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(Role::where('name', '!=', 'superadmin')->pluck('name'))],
        ];
    }
}
