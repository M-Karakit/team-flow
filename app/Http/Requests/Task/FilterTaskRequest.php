<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class FilterTaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'due_date'      => ['nullable', 'date_format:Y-m-d'],
            'due_date_from' => ['nullable', 'date_format:Y-m-d'],
            'due_date_to'   => ['nullable', 'date_format:Y-m-d', 'after_or_equal:due_date_from'],
            'due'           => ['nullable', 'in:overdue,today,this_week'],
            'assigned_to'   => ['nullable', 'exists:users,id'],
            'status'        => ['nullable', 'in:todo,in_progress,in_review,done'],
            'priority'      => ['nullable', 'in:low,medium,high,critical'],
        ];
    }
}
