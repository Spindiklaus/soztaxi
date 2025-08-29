<?php

namespace App\Http\Controllers\Operator;

use App\Models\Order;
use Illuminate\Http\Request;

class SocialTaxiOrderController extends BaseController {

    // Показать список заказов (включая удаленные)
    public function index(Request $request) {
        $query = Order::with(['currentStatus.statusOrder', 'client'])->withTrashed(); // Включаем удаленные
        // Фильтрация
        if ($request->has('pz_nom')) {
            $query->where('pz_nom', 'like', '%' . $request->input('pz_nom') . '%');
        }
        if ($request->has('type_order')) {
            $query->where('type_order', $request->input('type_order'));
        }

        // Сортировка по умолчанию - по дате приема заказа DESC
        $query->orderBy('pz_data', 'desc');

        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.index', compact('orders'));
    }

    // Показать форму создания заказа
    public function create() {
        return view('social-taxi-orders.create');
    }

    // Сохранить новый заказ
    public function store(Request $request) {
        $validated = $request->validate([
            'type_order' => 'required|integer|in:1,2,3',
            'client_id' => 'required|exists:clients,id',
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
            'client_id' => 'required|exists:clients,id',
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
        $order = Order::find($id);

        if (!$order) {
            return redirect()->route('social-taxi-orders.index')->with('error', 'Заказ не найден.');
        }

        // Принудительно устанавливаем deleted_at
        $order->deleted_at = now();
        $order->save();

        return redirect()->route('social-taxi-orders.index')->with('success', 'Заказ успешно удален.');
    }

    public function restore(Order $order) {
        if ($order->trashed()) {
            $order->restore();
            return redirect()->route('social-taxi-orders.index')->with('success', 'Заказ успешно восстановлен.');
        }

        return redirect()->route('social-taxi-orders.index')->with('error', 'Заказ не был удален.');
    }

}
