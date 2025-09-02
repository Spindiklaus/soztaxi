<?php

namespace App\Http\Controllers\Operator;

use App\Queries\OrderQueryBuilder;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSocialTaxiOrderRequest;
use App\Http\Requests\UpdateSocialTaxiOrderRequest;

class SocialTaxiOrderController extends BaseController {

    protected $queryBuilder;

    public function __construct(OrderQueryBuilder $queryBuilder) {
        $this->queryBuilder = $queryBuilder;
    }

    // Показать список заказов
    public function index(Request $request) {
        // По умолчанию показываем только неудаленные записи
        $showDeleted = $request->get('show_deleted', '0');

        $sort = $request->get('sort', 'pz_data');
        $direction = $request->get('direction', 'desc');

        $query = $this->queryBuilder->build($request, $showDeleted == '1');
        $orders = $query->paginate(15)->appends($request->all());

        return view('social-taxi-orders.index', compact(
                        'orders',
                        'showDeleted',
                        'sort',
                        'direction'
        ));
    }

    // Показать форму создания заказа
    public function create() {
        return view('social-taxi-orders.create');
    }

    // Сохранить новый заказ
    public function store(StoreSocialTaxiOrderRequest $request) {
        Order::create($request->validated());

        return redirect()->route('social-taxi-orders.index')->with('success', 'Заказ успешно создан.');
    }

    // Показать конкретный заказ
    public function show($id) {

        \Log::info('Попытка открыть заказ', ['order_id' => $id]);

        try {
            // Сначала попробуем найти заказ
            $order = Order::withTrashed()->find($id);

            if (!$order) {
                \Log::warning('Заказ не найден', ['order_id' => $id]);
                return redirect()->route('social-taxi-orders.index')
                                ->with('error', 'Заказ не найден.');
            }

            \Log::info('Найден заказ', ['order_id' => $order->id, 'deleted_at' => $order->deleted_at]);

            // Загружаем все необходимые отношения
            $order->load(['client', 'category', 'dopus', 'statusHistory.statusOrder', 'user']);

            return view('social-taxi-orders.show', compact('order'));
        } catch (\Exception $e) {
            return redirect()->route('social-taxi-orders.index')
                            ->with('error', 'ЗПроизошла ошибка при открытии заказа.');
        }
    }

    // Показать форму редактирования заказа
    public function edit(Order $order) {
        return view('social-taxi-orders.edit', compact('order'));
    }

    // Обновить заказ
    public function update(UpdateSocialTaxiOrderRequest $request, Order $order) {
        $order->update($request->validated());

        return redirect()->route('social-taxi-orders.index')->with('success', 'Заказ успешно обновлен.');
    }

    // Удалить заказ (мягкое удаление)
    public function destroy($id) {
        // Загружаем заказ с учетом удаленных записей
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return redirect()->back()->with('error', 'Заказ не найден.');
        }

        // Принудительно устанавливаем deleted_at
        $order->deleted_at = now();
        $order->save();

        return redirect()->back()->with('success', 'Заказ успешно удален.');
    }

    public function restore($id) {
        // Загружаем заказ с учетом удаленных записей
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return redirect()->back()->with('error', 'Заказ не найден.');
        }

        if ($order->trashed()) {
            $order->restore();
            return redirect()->back()->with('success', 'Заказ успешно восстановлен.');
        }

        return redirect()->back()->with('error', 'Заказ не был удален.');
    }

}
