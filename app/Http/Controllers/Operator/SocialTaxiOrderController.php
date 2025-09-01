<?php

namespace App\Http\Controllers\Operator;

use App\Models\Order;
use Illuminate\Http\Request;

class SocialTaxiOrderController extends BaseController {

    // Показать список заказов (временно упрощенный вариант)
// Показать список заказов
    public function index(Request $request) {
        $showDeleted = $request->get('show_deleted', '0');

        if ($showDeleted == '1') {
            $query = Order::with(['currentStatus.statusOrder', 'client'])->withTrashed();
        } else {
            $query = Order::with(['currentStatus.statusOrder', 'client']);
        }

        // Фильтрация только по непустым значениям
        if ($request->filled('pz_nom')) {  // filled() вместо has() + проверки на пустоту проверяет, что значение существует и не является пустой строкой.
            $query->where('pz_nom', 'like', '%' . $request->input('pz_nom') . '%');
        }
        if ($request->filled('type_order')) {
            $query->where('type_order', $request->input('type_order'));
        }

        // Сортировка
        $query->orderBy('pz_data', 'desc');

        $orders = $query->paginate(15)->appends($request->all());

        $params = $request->only(['show_deleted', 'pz_nom', 'type_order', 'page']);
        return view('social-taxi-orders.index', compact('orders', 'showDeleted', 'params'));
    }

    // Показать форму создания заказа
    public function create() {
        return view('social-taxi-orders.create');
    }

    // Сохранить новый заказ
    public function store(Request $request) {
        $validated = $request->validate([
            'type_order' => 'required|integer|in:1,2,3',
            'client_id' => 'required|exists:fio_dtrns,id', // Исправлено на fio_dtrns
            'client_tel' => 'required|string|max:255',
            'adres_otkuda' => 'required|string|max:255',
            'adres_kuda' => 'required|string|max:255',
            'pz_nom' => 'required|string|max:255',
            'pz_data' => 'required|date',
            'visit_data' => 'required|date',
            'taxi_id' => 'required|exists:taxis,id',
            'komment' => 'nullable|string',
        ]);

        Order::create($validated);

        return redirect()->route('social-taxi-orders.index')->with('success', 'Заказ успешно создан.');
    }

    // Показать конкретный заказ
    public function show(Order $order) {
        return view('social-taxi-orders.show', compact('order'));
    }

    // Показать форму редактирования заказа
    public function edit(Order $order) {
        return view('social-taxi-orders.edit', compact('order'));
    }

    // Обновить заказ
    public function update(Request $request, Order $order) {
        $validated = $request->validate([
            'type_order' => 'required|integer|in:1,2,3',
            'client_id' => 'required|exists:fio_dtrns,id', // Исправлено на fio_dtrns
            'client_tel' => 'required|string|max:255',
            'adres_otkuda' => 'required|string|max:255',
            'adres_kuda' => 'required|string|max:255',
            'pz_nom' => 'required|string|max:255',
            'pz_data' => 'required|date',
            'visit_data' => 'required|date',
            'taxi_id' => 'required|exists:taxis,id',
            'komment' => 'nullable|string',
        ]);

        $order->update($validated);

        return redirect()->route('social-taxi-orders.index')->with('success', 'Заказ успешно обновлен.');
    }

    // Удалить заказ (мягкое удаление)
    public function destroy($id) {
        // Загружаем заказ с учетом удаленных записей
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            // Сохраняем параметры запроса при редиректе
            $params = request()->only(['show_deleted', 'pz_nom', 'type_order', 'page']);
            return redirect()->route('social-taxi-orders.index', $params)->with('error', 'Заказ не найден.');
        }

        // Принудительно устанавливаем deleted_at
        $order->deleted_at = now();
        $order->save();

        // Сохраняем параметры запроса при редиректе
        $params = request()->only(['show_deleted', 'pz_nom', 'type_order', 'page']);
        return redirect()->route('social-taxi-orders.index', $params)->with('success', 'Заказ успешно удален.');
    }

    public function restore($id) {
        // Загружаем заказ с учетом удаленных записей
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return redirect()->route('social-taxi-orders.index')->with('error', 'Заказ не найден.');
        }

        if ($order->trashed()) {
            $order->restore();
            // Сохраняем параметры запроса при редиректе
            $params = request()->only(['show_deleted', 'pz_nom', 'type_order', 'page']);
            return redirect()->route('social-taxi-orders.index', $params)->with('success', 'Заказ успешно восстановлен.');
        }

        // Сохраняем параметры запроса при редиректе
        $params = request()->only(['show_deleted', 'pz_nom', 'type_order', 'page']);
        return redirect()->route('social-taxi-orders.index', $params)->with('error', 'Заказ не был удален.');
    }

}
