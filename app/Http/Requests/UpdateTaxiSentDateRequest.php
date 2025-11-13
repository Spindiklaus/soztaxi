<?php
// app/Http/Requests/UpdateTaxiSentDateRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaxiSentDateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'taxi_sent_at' => 'required|date_format:Y-m-d\TH:i',
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d',
            'taxi_id' => 'nullable|integer|exists:taxis,id',
        ];
    }
    public function messages()
    {
        return [
            'taxi_sent_at.required' => 'Поле "Дата передачи в такси" обязательно для заполнения.',
            'taxi_sent_at.date_format' => 'Поле "Дата передачи в такси" должно быть в формате даты и времени (например, 2025-10-04T14:30).',
            'date_from.required' => 'Поле "Дата поездки от" обязательно для заполнения.',
            'date_from.date_format' => 'Поле "Дата поездки от" должно быть в формате даты (например, 2025-10-04).',
            'date_to.required' => 'Поле "Дата поездки до" обязательно для заполнения.',
            'date_to.date_format' => 'Поле "Дата поездки до" должно быть в формате даты (например, 2025-10-04).',
            'taxi_id.integer' => 'Поле "ID такси" должно быть числом.',
            'taxi_id.exists' => 'Выбранное такси не существует.',
        ];
    }
}