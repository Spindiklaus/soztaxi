<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends BaseController
{
    public function index()
    {
        $categories = Category::with('user')->latest()->paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $category = new Category(); // пустой объект
        return view('categories.create', compact('category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nmv' => 'required|integer',
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        Category::create($request->all());

        return redirect()->route('categories.index')->with('success', 'Категория создана');
    }

    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'nmv' => 'required|integer',
            'name' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index')->with('success', 'Категория обновлена');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Категория удалена');
    }
}
