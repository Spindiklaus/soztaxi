<?php
// app/Http/Requests/ReturnFromTaxiRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReturnFromTaxiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Проверяем, что пользователь - администратор
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500', // Причина возврата
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Причина возврата обязательна.',
            'reason.string' => 'Причина должна быть строкой.',
            'reason.max' => 'Причина не может быть длиннее :max символов.',
        ];
    }
}