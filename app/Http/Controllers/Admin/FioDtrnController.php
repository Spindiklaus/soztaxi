<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Models\FioDtrn;

class FioDtrnController extends BaseController {

    public function index(Request $request) {
        // Фильтрация
        $query = FioDtrn::query();

        if ($request->filled('fio')) {
            $query->where('fio', 'like', "%{$request->input('fio')}%");
        }

        if ($request->filled('kl_id')) {
            $query->where('kl_id', 'like', "%{$request->input('kl_id')}%");
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        if ($request->filled('rip')) {
            $query->whereNotNull('rip_at');
        }

        // Сортировка
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['id', 'fio', 'kl_id', 'data_r', 'sex'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $fiodtrns = $query->orderBy($sort, $direction)->paginate(50);

        // Подготовим данные для Alpine.js
        $fiodtrnsJs = [];
        foreach ($fiodtrns as $fiodtrn) {
            $fiodtrnsJs[] = [
                'id' => $fiodtrn->id,
                'kl_id' => $fiodtrn->kl_id,
                'fio' => $fiodtrn->fio,
                'data_r' => optional($fiodtrn->data_r)->format('d.m.Y'),
                'sex' => $fiodtrn->sex,
                'rip_at' => optional($fiodtrn->rip_at)->format('d.m.Y'),
                'operator' => optional($fiodtrn->user)->name ?? '-',
                'komment' => $fiodtrn->komment,
            ];
        }


        return view('fiodtrns.index', compact('fiodtrns', 'sort', 'direction', 'fiodtrnsJs'));
    }

    public function create(Request $request) {
        $fiodtrn = new FioDtrn();
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        return view('fiodtrns.create', compact('fiodtrn', 'sort', 'direction'));
    }

    public function store(Request $request) {
        $request->validate([
            'kl_id' => 'required|string|max:255|unique:fio_dtrns,kl_id',
            'fio' => 'required|string|max:255',
            'data_r' => 'nullable|date',
            'sex' => 'nullable|in:М,Ж',
        ]);

        // Автоматически добавляем текущего пользователя
        $request->merge(['user_id' => auth()->id()]);

        FioDtrn::create($request->all());

        return redirect()->route('fiodtrns.index', [
                    'sort' => $request->input('sort', 'id'),
                    'direction' => $request->input('direction', 'asc')
                ])->with('success', 'Клиент успешно создан');
    }

    public function show(Request $request, FioDtrn $fiodtrn) {
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        return view('fiodtrns.show', compact('fiodtrn', 'sort', 'direction'));
    }

    public function edit(Request $request, FioDtrn $fiodtrn) {
        // Получаем всех пользователей с ролью admin или operator
        $users = \App\Models\User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'operator']);
                })->get();

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        return view('fiodtrns.edit', compact('fiodtrn', 'sort', 'direction', 'users'));
    }

    public function update(Request $request, FioDtrn $fiodtrn) {

//        $data = $request->all();
//        dd($data['created_rip']);
//        // Замена T на пробел, чтобы соответствовало формату Y-m-d H:i
//        if (!empty($data['created_rip'])) {
//            $data['created_rip'] = str_replace('T', ' ', $data['created_rip']);
//        }
////        dd($data['created_rip']);
//        $request->replace($data);
        
        $request->validate([
            'kl_id' => 'required|string|max:255|unique:fio_dtrns,kl_id,' . $fiodtrn->id,
            'fio' => 'required|string|max:255',
            'data_r' => 'nullable|date',
            'sex' => 'nullable|in:М,Ж',
            'rip_at' => 'nullable|date',
            'created_rip' => 'nullable|required_with:rip_at|date_format:Y-m-d\TH:i',
            'user_rip' => 'nullable|required_with:rip_at|exists:users,id',
            'komment' => 'nullable|required_with:rip_at|string',
        ]);

        $fiodtrn->update($request->all());

        return redirect()->route('fiodtrns.index', [
                    'sort' => $request->input('sort', 'id'),
                    'direction' => $request->input('direction', 'asc')
                ])->with('success', "Клиент {$fiodtrn->fio} обновлён");
    }

    public function destroy(Request $request, FioDtrn $fiodtrn) {
        $fiodtrn->delete();

        return redirect()->route('fiodtrns.index', [
                    'sort' => $request->input('sort', 'id'),
                    'direction' => $request->input('direction', 'asc')
                ])->with('success', 'Клиент удалён');
    }

}
