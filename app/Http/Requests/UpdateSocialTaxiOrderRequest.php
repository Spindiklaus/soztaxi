<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialTaxiOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type_order' => 'required|integer|in:1,2,3',
            'client_id' => 'required|exists:fio_dtrns,id',
            'client_tel' => 'required|string|max:255',
            'adres_otkuda' => 'required|string|max:255',
            'adres_kuda' => 'required|string|max:255',
            'pz_nom' => 'required|string|max:255',
            'pz_data' => 'required|date',
            'visit_data' => 'required|date',
            'taxi_id' => 'required|exists:taxis,id',
            'komment' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'type_order.required' => 'Тип заказа обязателен для заполнения',
            'type_order.integer' => 'Тип заказа должен быть целым числом',
            'type_order.in' => 'Неверный тип заказа',
            'client_id.required' => 'Клиент обязателен для выбора',
            'client_id.exists' => 'Выбранный клиент не существует',
            'client_tel.required' => 'Телефон клиента обязателен для заполнения',
            'client_tel.string' => 'Телефон клиента должен быть строкой',
            'client_tel.max' => 'Телефон клиента не может быть длиннее 255 символов',
            'adres_otkuda.required' => 'Адрес отправки обязателен для заполнения',
            'adres_otkuda.string' => 'Адрес отправки должен быть строкой',
            'adres_otkuda.max' => 'Адрес отправки не может быть длиннее 255 символов',
            'adres_kuda.required' => 'Адрес назначения обязателен для заполнения',
            'adres_kuda.string' => 'Адрес назначения должен быть строкой',
            'adres_kuda.max' => 'Адрес назначения не может быть длиннее 255 символов',
            'pz_nom.required' => 'Номер заказа обязателен для заполнения',
            'pz_nom.string' => 'Номер заказа должен быть строкой',
            'pz_nom.max' => 'Номер заказа не может быть длиннее 255 символов',
            'pz_data.required' => 'Дата заказа обязательна для заполнения',
            'pz_data.date' => 'Дата заказа должна быть корректной датой',
            'visit_data.required' => 'Дата поездки обязательна для заполнения',
            'visit_data.date' => 'Дата поездки должна быть корректной датой',
            'taxi_id.required' => 'Оператор такси обязателен для выбора',
            'taxi_id.exists' => 'Выбранный оператор такси не существует',
            'komment.max' => 'Комментарий не может быть длиннее 1000 символов',
        ];
    }
}