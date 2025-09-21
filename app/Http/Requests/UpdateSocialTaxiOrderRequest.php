<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UpdateSocialTaxiOrderRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $now = now();
        $minVisitDate = $now->copy()->addDay()->startOfDay();
        $maxVisitDate = $now->copy()->addMonths(6)->endOfDay();
//         $request = request();
//        \Log::info('Валидация visit_obratno', [
//            'type_order' => $request->type_order ?? 'not_set',
//            'adres_obratno' => $request->adres_obratno ?? 'not_set',
//            'zena_type' => $request->zena_type ?? 'not_set',
//            'visit_obratno_value' => $value ?? 'null',
//            'has_adres_obratno' => !empty($request->adres_obratno),
//            'has_visit_obratno' => !empty($value),
//            'condition_1' => ($request->type_order == 2 || $request->type_order == 3),
//            'condition_2' => !empty($request->adres_obratno) && empty($value),
//            'all_request_data_keys' => array_keys($request->all())
//        ]);

        return [
            'client_id' => 'required|exists:fio_dtrns,id',
            'visit_data' => [
                'required',
                'date',
                'after:' . $minVisitDate->format('Y-m-d H:i:s'),
                'before:' . $maxVisitDate->format('Y-m-d H:i:s'),
                function ($attribute, $value, $fail) {
                    $this->validateVisitTime($attribute, $value, $fail);
                }
            ],
            'visit_obratno' => 'nullable|date|after:visit_data',
            'adres_otkuda' => 'required|string|max:255',
            'adres_kuda' => 'required|string|max:255',
            'adres_obratno' => [
                'nullable',
                'max:255',
                'required_if:zena_type,2',
                'prohibited_if:zena_type,1'
            ],
            'category_id' => 'required|exists:categories,id',
            'client_tel' => 'required|string|max:255',
            'client_invalid' => 'nullable|string|max:255',
            'client_sopr' => 'nullable|string|max:255',
            'pz_nom' => 'required|string|max:255',
            'pz_data' => 'required|date',
            'type_order' => 'required|integer|in:1,2,3',
            'user_id' => 'required|integer|exists:users,id',
            'taxi_id' => 'required|exists:taxis,id',
            'taxi_price' => 'nullable|numeric',
            'taxi_way' => 'nullable|numeric',
            'taxi_sent_at' => 'nullable|date',
            'taxi_vozm' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    $this->validateTaxiVozm($attribute, $value, $fail);
                }
            ],
            'closed_at' => 'nullable|date',
            'komment' => 'nullable|string',
            'predv_way' => 'nullable|numeric|min:0|max:100',
            'zena_type' => 'required|integer|in:1,2',
            'dopus_id' => 'nullable|exists:skidka_dops,id',
            'skidka_dop_all' => [
                'nullable',
                'integer',
                'in:50,100',
                function ($attribute, $value, $fail) {
                    $this->validateSkidkaDopAll($attribute, $value, $fail);
                }
            ],
            'kol_p_limit' => [
                'integer',
                'in:10,26',
                function ($attribute, $value, $fail) {
                    $this->validateKolPLimit($attribute, $value, $fail);
                }
            ],
            'category_skidka' => 'nullable|integer|in:50,100',
            'category_limit' => 'nullable|integer|min:10|max:10',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages() {
        $now = now();
        $minVisitDate = $now->copy()->addDay()->startOfDay();
        $maxVisitDate = $now->copy()->addMonths(6)->endOfDay();

        return [
            'client_id.required' => 'Клиент обязателен для выбора.',
            'client_id.exists' => 'Выбранный клиент не существует.',
            'visit_data.required' => 'Дата поездки обязательна для заполнения.',
            'visit_data.date' => 'Дата поездки должна быть корректной датой.',
            'visit_data.after' => 'Дата поездки должна быть не раньше завтрашней даты (' . $minVisitDate->format('d.m.Y') . ').',
            'visit_data.before' => 'Дата поездки должна быть не позже чем через полгода (' . $maxVisitDate->format('d.m.Y') . ').',
            'visit_obratno.date' => 'Дата обратной поездки должна быть корректной датой.',
            'visit_obratno.after' => 'Дата обратной поездки должна быть позже даты основной поездки.',
            'adres_otkuda.required' => 'Адрес отправки обязателен для заполнения.',
            'adres_otkuda.string' => 'Адрес отправки должен быть строкой.',
            'adres_otkuda.max' => 'Адрес отправки не может быть длиннее 255 символов.',
            'adres_kuda.required' => 'Адрес назначения обязателен для заполнения.',
            'adres_kuda.string' => 'Адрес назначения должен быть строкой.',
            'adres_kuda.max' => 'Адрес назначения не может быть длиннее 255 символов.',
            'adres_obratno.max' => 'Обратный адрес не может быть длиннее 255 символов.',
            'adres_obratno.required_if' => 'При типе поездки "в обе стороны" обратный адрес обязателен для заполнения.',
            'adres_obratno.prohibited_if' => 'При типе поездки "в одну сторону" поле обратного адреса должно быть пустым.',
            'category_id.required' => 'Категория обязательна для выбора.',
            'category_id.exists' => 'Выбранная категория не существует.',
            'client_tel.required' => 'Телефон для связи обязателен.',
            'client_tel.string' => 'Телефон клиента должен быть строкой.',
            'client_tel.max' => 'Телефон клиента не может быть длиннее 255 символов.',
            'client_invalid.string' => 'Удостоверение инвалида должно быть строкой.',
            'client_invalid.max' => 'Удостоверение инвалида не может быть длиннее 255 символов.',
            'client_sopr.string' => 'Сопровождающий должен быть строкой.',
            'client_sopr.max' => 'Сопровождающий не может быть длиннее 255 символов.',
            'pz_nom.required' => 'Номер заказа обязателен для заполнения.',
            'pz_nom.string' => 'Номер заказа должен быть строкой.',
            'pz_nom.max' => 'Номер заказа не может быть длиннее 255 символов.',
            'pz_data.required' => 'Дата заказа обязательна для заполнения.',
            'pz_data.date' => 'Дата заказа должна быть корректной датой.',
            'type_order.required' => 'Тип заказа обязателен для выбора.',
            'type_order.integer' => 'Тип заказа должен быть целым числом.',
            'type_order.in' => 'Недопустимый тип заказа.',
            'user_id.required' => 'Оператор обязателен для выбора.',
            'user_id.integer' => 'ID оператора должен быть целым числом.',
            'user_id.exists' => 'Выбранный оператор не существует.',
            'taxi_id.required' => 'Выбор оператора такси обязателен для сохранения заказа.',
            'taxi_id.exists' => 'Выбранный оператор такси не существует.',
            'taxi_price.numeric' => 'Цена такси должна быть числом.',
            'taxi_way.numeric' => 'Дальность такси должна быть числом.',
            'taxi_sent_at.date' => 'Дата отправки в такси должна быть корректной датой.',
            'closed_at.date' => 'Дата закрытия должна быть корректной датой.',
            'komment.string' => 'Комментарий должен быть строкой.',
            'predv_way.numeric' => 'Предварительная дальность должна быть числом.',
            'predv_way.min' => 'Предварительная дальность поездки не может быть отрицательной.',
            'predv_way.max' => 'Предварительная дальность поездки не может быть больше 100км.',
            'zena_type.required' => 'Тип поездки обязателен для выбора.',
            'zena_type.integer' => 'Тип поездки должен быть целым числом.',
            'zena_type.in' => 'Недопустимый тип поездки. Выберите 1 (в одну сторону) или 2 (в обе стороны).',
            'dopus_id.exists' => 'Выбранные дополнительные условия не существуют.',
            'skidka_dop_all.integer' => 'Скидка по дополнительным условиям должна быть целым числом.',
            'skidka_dop_all.in' => 'Скидка по поездке может быть только 50 или 100%.',
            'kol_p_limit.integer' => 'Лимит поездок должен быть целым числом.',
            'kol_p_limit.in' => 'Лимит поездок может быть только 10 или 26 поездок в месяц.',
            'category_skidka.integer' => 'Скидка по категории должна быть целым числом.',
            'category_skidka.in' => 'Скидка по категории может быть только 50 или 100%.',
            'category_limit.integer' => 'Лимит по категории должен быть целым числом.',
            'category_limit.in' => 'Лимит поездок по категории может быть только 10.',
        ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $request = request();
  
        // Проверяем дату обратной поездки
        if (!empty($request->visit_obratno)) {
            // Проверяем, что это корректная дата
            if (!strtotime($request->visit_obratno)) {
                $validator->errors()->add('visit_obratno', 'Дата обратной поездки должна быть корректной датой.');
                return;
            }
            
            // Проверяем, что дата позже основной поездки
            if (!empty($request->visit_data)) {
                $visitData = strtotime($request->visit_data);
                $visitObratno = strtotime($request->visit_obratno);
                if ($visitObratno <= $visitData) {
                    $validator->errors()->add('visit_obratno', 'Дата обратной поездки должна быть позже даты основной поездки.');
                    return;
                }
            }
            
            // Проверяем, что дата совпадает по дням (день, месяц, год)
                $visitDataDate = date('Y-m-d', $visitData);
                $visitObratnoDate = date('Y-m-d', $visitObratno);
                
                if ($visitDataDate !== $visitObratnoDate) {
                    $validator->errors()->add('visit_obratno', 'Дата обратной поездки должна быть в тот же день, что и основная поездка.');
                    return;
                }
            
            
        }
        
        // Основная логика валидации
        if (($request->type_order == 2 || $request->type_order == 3)) {
            // Для легкового авто и ГАЗели
            if (!empty($request->adres_obratno) && empty($request->visit_obratno)) {
                // Если есть обратный адрес, то дата обязательна
                $validator->errors()->add('visit_obratno', 'При наличии обратного адреса дата обратной поездки обязательна.');
            }
            if (empty($request->adres_obratno) && !empty($request->visit_obratno)) {
                // Если нет обратного адреса, то дата должна быть null
                $validator->errors()->add('visit_obratno', 'При отсутствии обратного адреса дата обратной поездки должна быть пустой.');
            }
        } else {
            // Для соцтакси дата всегда должна быть null
            if (!empty($request->visit_obratno)) {
                $validator->errors()->add('visit_obratno', 'Для соцтакси дата обратной поездки должна быть пустой.');
            }
        }
    });
}
    
    
    /**
     * Validate visit time restrictions
     */
    private function validateVisitTime($attribute, $value, $fail) {
        $visitTime = Carbon::parse($value);
        $visitHour = $visitTime->hour;

        // Проверяем, что час не в запрещенном диапазоне
        if ($visitHour >= 22 || $visitHour < 6) {
            $fail('Время поездки не может быть с 22:00 до 06:00.');
        }

        // Проверяем, что у клиента нет поездок в течение ±1 часа
        if (!empty($this->client_id) && $value) {
            $startTime = $visitTime->copy()->subHour();
            $endTime = $visitTime->copy()->addHour();

            // Получаем ID текущего заказа из параметров маршрута
            $currentOrderId = $this->route('social_taxi_order')->id;
            $existingOrder = \App\Models\Order::where('client_id', $this->client_id)
                    ->whereBetween('visit_data', [$startTime, $endTime])
                    ->whereNull('deleted_at')
                    ->whereNull('cancelled_at')
                    ->where('id', '!=', $currentOrderId)  // Исключаем текущий заказ  
                    ->first();

            if ($existingOrder) {
                $existingTime = Carbon::parse($existingOrder->visit_data)->format('d.m.Y H:i');
                $fail("У данного клиента уже есть поездка на {$existingTime}. Нельзя создавать поездки в течение часа друг от друга.");
            }
//            \Log::debug('Current order ID:', ['id' => $currentOrderId]);
        }
    }

    /**
     * Validate taxi vozvratshenie
     */
    private function validateTaxiVozm($attribute, $value, $fail) {
        // Для легкового авто и ГАЗели (zena_type != 1) проверяем, что taxi_vozm = taxi_price
        if (($this->type_order == 2 || $this->type_order == 3) &&
                abs((float) $this->taxi_price - (float) $value) > 0.00000000001) {
            $fail('Для легкового авто и ГАЗели сумма возмещения должна равняться цене поездки.');
        }
    }

    /**
     * Validate skidka dop all
     */
    private function validateSkidkaDopAll($attribute, $value, $fail) {
        $type = $this->route('type');
        // Для легкового авто и ГАЗели скидка должна быть 100%
        if (($type == 2 || $type == 3) && $value != 100) {
            $fail('Для легкового авто и ГАЗели скидка должна быть всегда 100%.');
        }
    }

    /**
     * Validate kol p limit
     */
    private function validateKolPLimit($attribute, $value, $fail) {
        // Проверяем для всех типов заказов
        if ($value && $this->client_id && $this->visit_data) {
            $visitDate = Carbon::parse($this->visit_data);

            // Используем существующую функцию хелпера для получения количества поездок клиента в месяц
            $clientTripsCount = getClientTripsCountInMonthByVisitDate($this->client_id, $visitDate);

            // Проверяем, не превышает ли лимит (учитываем, что создается новый заказ)
            if ($clientTripsCount >= $value) {
                $fail("Клиент уже совершил {$clientTripsCount} поездок из доступных {$value} в этом месяце. Невозможно создать новый заказ.");
            }
        }
    }

}
