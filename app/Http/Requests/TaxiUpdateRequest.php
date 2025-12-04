<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxiUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'life' => 'required|boolean',
            'koef' => 'required|numeric',
            'posadka' => 'required|numeric',
            'koef50' => 'nullable|numeric',
            'posadka50' => 'nullable|numeric',
            'zena1_auto' => 'nullable|numeric',
            'zena2_auto' => 'nullable|numeric',
            'zena1_gaz' => 'nullable|numeric',
            'zena2_gaz' => 'nullable|numeric',
            'komment' => 'nullable|string',
            'update_date' => 'nullable|date_format:d.m.Y', // Валидация даты оставлена
        ];
    }
    
    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Поле "Наименование" обязательно для заполнения.',
            'name.string' => 'Поле "Наименование" должно быть строкой.',
            'life.required' => 'Поле "Статус" обязательно для заполнения.',
            'life.boolean' => 'Поле "Статус" должно быть логическим значением.',
            'koef.required' => 'Поле "Стоимость 1 км пути" обязательно для заполнения.',
            'koef.numeric' => 'Поле "Стоимость 1 км пути" должно быть числом.',
            'posadka.required' => 'Поле "Стоимость посадки" обязательно для заполнения.',
            'posadka.numeric' => 'Поле "Стоимость посадки" должно быть числом.',
            'update_date.date_format' => 'Некорректный формат даты. Используйте DD.MM.YYYY',
        ];
    }
}