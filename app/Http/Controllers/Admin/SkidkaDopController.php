<?php

namespace App\Http\Controllers\Admin;
use App\Models\SkidkaDop;
use Illuminate\Http\Request;

class SkidkaDopController extends BaseController
{
    // Показать список записей
    public function index(Request $request)
    {
        $query = SkidkaDop::query();

        // Фильтрация
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->has('life')) {
            $query->where('life', $request->input('life'));
        }

        $skidkaDops = $query->paginate(10)->appends($request->all());

        return view('skidka_dops.index', compact('skidkaDops'));
    }

    // Показать форму создания записи
    public function create()
    {
        return view('skidka_dops.create');
    }

    // Сохранить новую запись
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'skidka' => 'required|integer|min:50|max:100',
            'kol_p' => 'required|integer|min:10|max:26',
            'life' => 'boolean',
            'komment' => 'nullable|string',
        ]);

        SkidkaDop::create($validated);

        return redirect()->route('skidka_dops.index')->with('success', 'Запись успешно создана.');
    }

    // Показать конкретную запись
    public function show(SkidkaDop $skidkaDop)
    {
        dd(__METHOD__, $skidkaDop);
//        return view('skidka_dops.show', compact('skidkaDop'));
    }

    // Показать форму редактирования записи
    public function edit(SkidkaDop $skidkaDop)
    {
        return view('skidka_dops.edit', compact('skidkaDop'));
    }

    // Обновить запись
    public function update(Request $request, SkidkaDop $skidkaDop)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'skidka' => 'required|integer|min:50|max:100',
            'kol_p' => 'required|integer|min:10|max:26',
            'life' => 'boolean',
            'komment' => 'nullable|string',
        ]);

        $skidkaDop->update($validated);

        return redirect()->route('skidka_dops.index')->with('success', 'Запись успешно обновлена.');
    }

    // Удалить запись
    public function destroy(SkidkaDop $skidkaDop)
    {
        $skidkaDop->delete();

        return redirect()->route('skidka_dops.index')->with('success', 'Запись успешно удалена.');
    }
}