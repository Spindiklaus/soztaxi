<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Taxi;
use App\Models\Order;

class TaxiController extends BaseController {

    public function index(Request $request) {
        // Фильтрация
        $query = Taxi::with('user');

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->input('name')}%");
        }

        if ($request->filled('life')) {
            $query->where('life', $request->input('life'));
        }

        // Сортировка
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'asc');

        $taxis = $query->orderBy($sort, $direction)->paginate(10);

        // Подготовь данные для JS
        $taxisJs = $taxis->getCollection()->transform(function ($taxi) {
            return [
        'id' => $taxi->id,
        'name' => $taxi->name,
        'koef' => $taxi->koef,
        'posadka' => $taxi->posadka,
        'zena1_auto' => $taxi->zena1_auto,
        'zena2_auto' => $taxi->zena2_auto,
        'zena1_gaz' => $taxi->zena1_gaz,
        'zena2_gaz' => $taxi->zena2_gaz,
        'komment' => $taxi->komment,
        'life' => $taxi->life,
            ];
        });
        return view('taxis.index', compact('taxis', 'sort', 'direction', 'taxisJs'));
    }

    public function create() {
        return view('taxis.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'koef' => 'required|numeric',
            'posadka' => 'required|numeric',
        ]);
        
        // Добавляем текущего пользователя
        $request->merge(['user_id' => auth()->id()]);

        Taxi::create($request->all());

        return redirect()->route('taxis.index')->with('success', 'Оператор такси успешно создан.');
    }

    public function show(Taxi $taxi) {
        return view('taxis.show', compact('taxi'));
    }

    public function edit(Taxi $taxi) {
        return view('taxis.edit', compact('taxi'));
    }

    public function update(TaxiUpdateRequest $request, Taxi $taxi) {
        //$taxi->update($request->all());
        // Обновляем основные данные такси
        $taxi->update($request->only([
            'name', 'life', 'koef', 'posadka', 'koef50', 'posadka50',
            'zena1_auto', 'zena2_auto', 'zena1_gaz', 'zena2_gaz', 'komment'
        ]));

        // Проверяем, была ли нажата кнопка "Обновить цены по новым тарифам"
        if ($request->input('action') === 'update_prices') {
            $updateDate = $request->input('update_date');

            if ($updateDate) {
                // Преобразуем дату в формат Carbon
                $updateDate = \Carbon\Carbon::createFromFormat('d.m.Y', $updateDate);

                // Находим заказы, удовлетворяющие условиям
                $orders = Order::where('taxi_id', $taxi->id)
                    ->where(function($query) use ($updateDate) {
                        // Для type_order 2 и 3: visit_data >= указанной даты
                        $query->where(function($q) use ($updateDate) {
                            $q->whereIn('type_order', [2, 3])
                              ->whereDate('visit_data', '>=', $updateDate);
                        })
                        // Для type_order 1: visit_data >= указанной даты И taxi_sent_at не null
                        ->orWhere(function($q) use ($updateDate) {
                            $q->where('type_order', 1)
                              ->whereDate('visit_data', '>=', $updateDate)
                              ->whereNotNull('taxi_sent_at')
                              ->where('taxi_way', '>=', 0);
                        });
                    })
                    ->get();
                    
                $ordersToUpdate = [];
                $ordersBefore = [];

                foreach ($orders as $order) {
                    // Сохраняем текущие значения
                    $ordersBefore[$order->id] = [
                        'taxi_price' => $order->taxi_price,
                        'taxi_vozm' => $order->taxi_vozm,
                    ];

                    // Рассчитываем новые значения в зависимости от типа заказа
                    switch ($order->type_order) {
                        case 2: // Легковое авто
                            if ($order->zena_type == 1) { // Одна сторона
                                $newPrice = $taxi->zena1_auto;
                            } else { // Обе стороны
                                $newPrice = $taxi->zena2_auto;
                            }
                            $order->taxi_price = $newPrice;
                            $order->taxi_vozm = $newPrice;
                            break;
                        case 3: // ГАЗель
                            if ($order->zena_type == 1) { // Одна сторона
                                $newPrice = $taxi->zena1_gaz;
                            } else { // Обе стороны
                                $newPrice = $taxi->zena2_gaz;
                            }
                            $order->taxi_price = $newPrice;
                            $order->taxi_vozm = $newPrice;
                            break;
                        case 1: // Соцтакси
                            if ($order->skidka_dop_all == 100) {
                                $newPrice = $taxi->koef * $order->taxi_way + $taxi->posadka;
                                $order->taxi_price = $newPrice;
                                $order->taxi_vozm = $newPrice;
                            } elseif ($order->skidka_dop_all == 50) {
                                $newPrice = $taxi->koef * $order->taxi_way + $taxi->posadka;
                                $newVozm = $taxi->koef50 * $order->taxi_way + $taxi->posadka50;
                                $order->taxi_price = $newPrice;
                                $order->taxi_vozm = $newVozm;
                            }
                            break;
                    }
                    
                   // Проверяем, были ли изменения в ценах
                    $wasChanged = ($order->taxi_price != $ordersBefore[$order->id]['taxi_price'] || 
                                  $order->taxi_vozm != $ordersBefore[$order->id]['taxi_vozm']);

                    if ($wasChanged) {
                        // Добавляем комментарий об изменении тарифов
                        $currentComment = $order->komment ?? '';
                        $changeComment = 'Изменены тарифы ' . \Carbon\Carbon::now()->format('d.m.Y H:i') . 
                                   ' (цена поездки: ' . $ordersBefore[$order->id]['taxi_price'] . ' → ' . $order->taxi_price . 
                                   ', возм: ' . $ordersBefore[$order->id]['taxi_vozm'] . ' → ' . $order->taxi_vozm . ')';

                        if ($currentComment) {
                            $order->komment = $currentComment . '\n' . $changeComment;
                        } else {
                            $order->komment = $changeComment;
                        }

                        // Сохраняем информацию об изменении только для заказов, в которых произошли изменения
                        $ordersToUpdate[$order->id] = [
                            'taxi_price' => $order->taxi_price,
                            'taxi_vozm' => $order->taxi_vozm,
                        ];
                    } else {
                        // Если изменений не было, не добавляем заказ в отчет об изменениях
                        unset($ordersBefore[$order->id]);
                    }
                }

                // Обновляем заказы в базе данных
                foreach ($orders as $order) {
                    $order->save();
                }
            
                // Устанавливаем flash-сообщение с информацией об изменениях
                $request->session()->flash('price_update_info', [
                    'count' => count($ordersToUpdate),
                    'orders_before' => $ordersBefore,
                    'orders_after' => $ordersToUpdate,
                ]);
            }  else {
                // Если дата не указана, устанавливаем ошибку
                return redirect()->route('taxis.edit', $taxi)
                    ->withErrors(['update_date' => 'Пожалуйста, укажите дату для обновления цен'])
                    ->withInput();
            }
            
            // Не редиректим, а возвращаем ту же страницу с обновленной информацией
            return view('taxis.edit', compact('taxi'));
            
        }
         return redirect()->route('taxis.index')->with('success', 'Оператор такси успешно обновлен');
    }

    public function destroy(Taxi $taxi) {
    try {
        $taxi->delete();
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        // Проверяем, является ли ошибка ограничением внешнего ключа
        if (str_contains($e->getMessage(), 'foreign key constraint')) {
            return response()->json([
                'success' => false,
                'message' => 'Невозможно удалить оператора такси, так как существуют заказы, связанные с ним. Сначала удалите или измените эти заказы.'
            ], 400);
        }
        
        // Другая ошибка
        return response()->json([
            'success' => false,
            'message' => 'Ошибка при удалении оператора такси: ' . $e->getMessage()
        ], 400);
    }
}

}
