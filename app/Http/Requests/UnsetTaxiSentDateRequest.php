<?php
// app/Http/Requests/UnsetTaxiSentDateRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnsetTaxiSentDateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d',
            'taxi_id' => 'nullable|integer|exists:taxis,id',
        ];
    }
    public function messages()
    {
        return [
            'date_from.required' => 'Поле "Дата поездки от" обязательно для заполнения.',
            'date_from.date_format' => 'Поле "Дата поездки от" должно быть в формате даты (например, 2025-10-04).',
            'date_to.required' => 'Поле "Дата поездки до" обязательно для заполнения.',
            'date_to.date_format' => 'Поле "Дата поездки до" должно быть в формате даты (например, 2025-10-04).',
            'taxi_id.integer' => 'Поле "ID такси" должно быть числом.',
            'taxi_id.exists' => 'Выбранное такси не существует.',
        ];
    }
}