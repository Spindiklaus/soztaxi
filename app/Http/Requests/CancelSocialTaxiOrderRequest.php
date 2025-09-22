<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CancelSocialTaxiOrderRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $social_taxi_order = $this->route('social_taxi_order');
        
        return [
            'reason' => 'required|string|max:1000',
            'cancelled_at' => [
                'required',
                'date',
                'before_or_equal:now',
                function ($attribute, $value, $fail) use ($social_taxi_order) {
                    // Проверяем, что дата отмены раньше даты поездки
                    if ($social_taxi_order && $social_taxi_order->visit_data) {
                        $cancelDate = Carbon::parse($value);
                        $visitDate = Carbon::parse($social_taxi_order->visit_data);

                        if ($cancelDate >= $visitDate) {
                            $fail('Дата отмены должна быть раньше даты поездки (' . $visitDate->format('d.m.Y H:i') . ').');
                        }
                    }
                }
            ],
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Причина отмены обязательна для заполнения.',
            'reason.string' => 'Причина отмены должна быть строкой.',
            'reason.max' => 'Причина отмены не может быть длиннее 1000 символов.',
            'cancelled_at.required' => 'Дата отмены обязательна для заполнения.',
            'cancelled_at.date' => 'Дата отмены должна быть корректной датой.',
            'cancelled_at.before_or_equal' => 'Дата отмены не может быть в будущем.',
            'user_id.required' => 'Оператор обязателен для выбора.',
            'user_id.integer' => 'ID оператора должен быть целым числом.',
            'user_id.exists' => 'Выбранный оператор не существует.',
        ];
    }
}