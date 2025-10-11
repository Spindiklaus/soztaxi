<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderGroup;
use App\Services\OrderGroupingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderGroupingController extends BaseController
{
    protected $groupingService;

    public function __construct(OrderGroupingService $groupingService)
    {
        $this->groupingService = $groupingService;
    }

    // Показать форму выбора даты
    public function showGroupingForm()
    {
        return view('orders.grouping_form'); // Blade шаблон для выбора даты
    }

    // Обработать выбор даты и показать заказы для группировки
    public function showOrdersForGrouping(Request $request)
    {
        $request->validate([
            'grouping_date' => 'required|date',
        ]);

        $selectedDate = Carbon::parse($request->input('grouping_date'))->startOfDay();
        $endDate = $selectedDate->copy()->endOfDay();

        // Получаем заказы типа 1 (соцтакси) на выбранную дату, не закрытые и не отмененные
        $orders = Order::where('type_order', 1)
            ->whereNull('closed_at')
            ->whereNull('cancelled_at')
            ->whereBetween('visit_data', [$selectedDate, $endDate])
            ->with(['client']) // Загружаем связь client (метод в модели Order)   
            ->orderBy('visit_data')
            ->orderBy('adres_otkuda')
            ->orderBy('adres_kuda')
            ->get();
        
        // Получаем толерантность времени из сервиса
        $timeTolerance = $this->groupingService->getTimeToleranceMinutes(); // Убедитесь, что в сервисе есть геттер

        // Генерируем потенциальные группы
        $potentialGroups = $this->groupingService->findPotentialGroupsForDate($orders);

        return view('orders.grouping_view', compact('orders', 'potentialGroups', 'selectedDate', 'timeTolerance'));
    }

    // Обработать выбор группировки пользователем и сохранить
    public function processGrouping(Request $request)
    {
        $request->validate([
            'selected_groups' => 'required|array',
            'selected_groups.*.order_ids' => 'required|array|max:4',
            'selected_groups.*.order_ids.*' => 'required|exists:orders,id',
        ]);

        $selectedGroups = $request->input('selected_groups');

        \DB::transaction(function () use ($selectedGroups) {
            foreach ($selectedGroups as $groupData) {
                if (empty($groupData['order_ids'])) {
                    continue; // Пропускаем пустые группы
                }

                // Создаем новую группу в БД
                $orderGroup = OrderGroup::create([
                    'name' => 'Группировка ' . now()->format('Y-m-d H:i:s'), // Или генерируйте имя по-другому
                ]);

                $groupIds = $groupData['order_ids'];

                // Обновляем заказы, присваивая им ID новой группы
                Order::whereIn('id', $groupIds)->update(['order_group_id' => $orderGroup->id]);
            }
        });

        return redirect()->back()->with('success', 'Группировка успешно сохранена!');
    }
}