<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\Taxi;

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

    public function update(Request $request, Taxi $taxi) {
        $request->validate([
            'name' => 'required|string|max:255',
            'koef' => 'required|numeric',
            'posadka' => 'required|numeric',
        ]);

        $taxi->update($request->all());

        return redirect()->route('taxis.index')->with('success', 'Оператор такси успешно обновлён.');
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
