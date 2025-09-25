<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Models\FioRip;


class FioRipController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FioRip::query();

        if ($request->filled('fio')) {
            $query->where('fio', 'like', "%{$request->input('fio')}%");
        }

        if ($request->filled('kl_id')) {
            $query->where('kl_id', 'like', "%{$request->input('kl_id')}%");
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        if ($request->filled('rip_at')) {
            $query->whereNotNull('rip_at');
        }

        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        $fioRips = $query->orderBy($sort, $direction)->paginate(50);

        return view('fio_rips.index', compact('fioRips', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('fio_rips.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kl_id' => 'nullable|string|unique:fio_rips,kl_id',
            'fio' => 'required|string|max:255',
            'data_r' => 'nullable|date',
            'sex' => 'nullable|in:М,Ж',
            'adres' => 'nullable|string',
            'rip_at' => 'nullable|date',
            'nom_zap' => 'nullable|string',
            'komment' => 'nullable|string',
        ]);
        FioRip::create($validated);
        return redirect()->route('fio_rips.index')->with('success', 'Запись успешно добавлена');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FioRip $fioRip)
    {
        return view('fio_rips.edit', compact('fioRip'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FioRip $fioRip)
    {
        $validated = $request->validate([
            'kl_id' => 'nullable|string|unique:fio_rips,kl_id,' . $fioRip->id,
            'fio' => 'required|string|max:255',
            'data_r' => 'nullable|date',
            'sex' => 'nullable|in:М,Ж',
            'adres' => 'nullable|string',
            'rip_at' => 'nullable|date',
            'nom_zap' => 'nullable|string',
            'komment' => 'nullable|string',
        ]);

        $fioRip->update($validated);
        return redirect()->route('fio_rips.index')->with('success', "Запись {$fioRip->fio} обновлена");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FioRip $fioRip)
    {
        $fioRip->delete();
        return redirect()->route('fio_rips.index')->with('success', "Запись {$fioRip->fio} удалена");
    }
}
