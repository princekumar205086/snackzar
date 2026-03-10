<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', "unique:users,email,{$userId}"],
            'phone' => ['sometimes', 'string', 'size:10', "unique:users,phone,{$userId}"],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
