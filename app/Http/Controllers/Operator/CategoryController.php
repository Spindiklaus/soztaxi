<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryController extends BaseController {

    public function index(Request $request) {
        
        // Получаем параметры фильтрации
        $query = Category::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->input('name')}%");
        }

        if ($request->filled('nmv')) {
            $query->where('nmv', $request->input('nmv'));
        }

        if ($request->filled('skidka')) {
            $query->where('skidka', $request->input('skidka'));
        }
        
        // Получаем параметры сортировки из URL
        $sort = $request->input('sort', 'id'); // по умолчанию 'id'
        $direction = $request->input('direction', 'asc'); // по умолчанию 'asc'

        $allowedSorts = ['id', 'nmv', 'name', 'skidka', 'kol_p'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
        // Проверка направления сортировки
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $categories = $query->orderBy($sort, $direction)->paginate(10);

        return view('categories.index', compact('categories', 'sort', 'direction'));
    }

    public function create(Request $request) {
        $category = new Category(); // пустой объект
        // Получаем текущие параметры сортировки
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        
//        dd($request);

        return view('categories.create', compact('category', 'sort', 'direction'));
    }

    public function store(Request $request) {
        $request->validate([
            'nmv' => 'required|integer|unique:categories,nmv',
            'name' => 'required|string|max:255',
//            'user_id' => 'required|exists:users,id',
        ]);
        // Добавляем текущего пользователя
        $request->merge(['user_id' => auth()->id()]);

        Category::create($request->all());

        return redirect()->route('categories.index', [
                    'sort' => $request->input('sort', 'id'),
                    'direction' => $request->input('direction', 'asc')
                ])->with('success', 'Категория создана');
    }

    public function show(Request $request, Category $category) {
        // Получаем текущие параметры сортировки
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        return view('categories.show', compact('category', 'sort', 'direction'));
    }

    public function edit(Request $request, Category $category) {
        // Получаем текущие параметры сортировки
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        return view('categories.edit', compact('category', 'sort', 'direction'));
    }

    public function update(Request $request, Category $category) {
        $request->validate([
            'nmv' => [
                'required',
                'integer',
                Rule::unique('categories', 'nmv')->ignore($category->id),
            ],
            'name' => 'required|string|max:255',
//            'user_id' => 'required|exists:users,id',
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index', [
                    'sort' => $request->input('sort', 'id'),
                    'direction' => $request->input('direction', 'asc')
                ])->with('success', 'Категория обновлена');
    }

    public function destroy(Category $category) {
        $category->delete();
        return redirect()->route('categories.index', [
                    'sort' => $request->input('sort', 'id'),
                    'direction' => $request->input('direction', 'asc')
                ])->with('success', 'Категория удалена');
    }

}
