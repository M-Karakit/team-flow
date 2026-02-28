<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => ['sometimes', 'string', 'max:255'],
            'description'       => ['sometimes', 'nullable', 'string'],
            'status'            => ['sometimes', 'in:active,on_hold,archived'],
            'due_date'          => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'members'           => ['sometimes', 'array'],
            'members.*.id'      => ['required_with:members', 'exists:users,id'],
            'members.*.role'    => ['sometimes', 'in:manager,member'],
        ];
    }
}
