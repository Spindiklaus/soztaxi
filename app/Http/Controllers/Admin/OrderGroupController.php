<?php

// app/Http/Controllers/Admin/OrderGroupController.php

namespace App\Http\Controllers\Admin;

use App\Models\OrderGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Импортируем Validator

class OrderGroupController extends BaseController // 
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orderGroups = OrderGroup::orderBy('visit_date', 'desc')->paginate(25); // Пагинация
        return view('order-groups.index', compact('orderGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Для OrderGroup, возможно, не нужно отдельной формы создания,
        // так как группы создаются через логику группировки.
        // Но если нужно, реализуйте здесь.
        // return view('admin.order_groups.create');
        abort(404); // Пока возвращаем 404, если создание не предусмотрено вручную
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Аналогично, сохранение происходит через логику группировки.
        // abort(404); // Пока возвращаем 404, если сохранение не предусмотрено вручную
        // Или реализуйте логику, если нужно создавать группы вручную.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'visit_data' => [
                'required',
                'date',
            ],            
            // Добавьте другие поля, если есть
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $orderGroup = OrderGroup::create($validator->validated());

        return redirect()->route('admin.order-groups.index')->with('success', 'Группа успешно создана.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderGroup $orderGroup) // Model Binding: Laravel автоматически найдет OrderGroup по ID
    {
        // Загружаем связанные заказы для отображения
        $orderGroup->load('orders.client'); // Загружаем заказы и их клиентов
        return view('order-groups.show', compact('orderGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrderGroup $orderGroup)
    {
        return view('order-groups.edit', compact('orderGroup'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderGroup $orderGroup)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'visit_data' => [
                'required',
                'date',
            ],
            // Добавьте другие поля, если есть
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $orderGroup->update($validator->validated());

        return redirect()->route('order-groups.index')->with('success', 'Группа успешно обновлена.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderGroup $orderGroup)
    {
        // Важно: Удаление группы может повлиять на связанные заказы.
        // Перед удалением вызывается deleting(OrderGroup $orderGroup)
        // $orderGroup->orders()->update(['order_group_id' => null]); // Пример

        $orderGroup->delete();

        return redirect()->route('order-groups.index')->with('success', 'Группа успешно удалена.');
    }
}