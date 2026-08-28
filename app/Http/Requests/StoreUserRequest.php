<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'account_no' => 'required|string|unique:users',
            'name' => 'required|string|max:255',
            'username' => 'sometimes|string|unique:users',
            'email' => 'required|unique:users',
            'phone' => 'sometimes',
            'password' => 'required|string|min:8',
            'linked_id' => 'required|int',
        ];
    }
}
