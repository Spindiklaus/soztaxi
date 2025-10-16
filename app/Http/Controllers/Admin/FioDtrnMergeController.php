<?php

namespace App\Http\Controllers\Admin;

use App\Models\FioDtrn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FioDtrnMergeController extends BaseController
{
    public function showForm()
    {
//        dd('Метод showForm вызван!');
        
        // Получаем всех клиентов без даты RIP, у которых есть дубликаты по ФИО
        $duplicateFios = FioDtrn::select('fio')
            ->whereNull('rip_at')
            ->groupBy('fio')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('fio');

        $clients = FioDtrn::whereIn('fio', $duplicateFios)
            ->whereNull('rip_at')
            ->orderBy('fio')
            ->orderBy('kl_id')
            ->get();

        return view('fiodtrns.merge', compact('clients'));
    }

    public function merge(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:fio_dtrns,id',
            'target_id' => 'required|exists:fio_dtrns,id|different:source_id',
        ]);

        $sourceId = $request->input('source_id');
        $targetId = $request->input('target_id');

        DB::beginTransaction();

        try {
            // Переносим заказы
            $sourceClient = FioDtrn::findOrFail($sourceId);
            $targetClient = FioDtrn::findOrFail($targetId);

            // Обновляем client_id в таблице orders
            $sourceClient->orders()->withTrashed()->update(['client_id' => $targetId]);

            // Удаляем старого клиента
            $sourceClient->delete();

            DB::commit();

            return redirect()->route('fiodtrns.index')->with('success', "Клиенты успешно совмещены: {$sourceClient->fio} удален, заказы перенесены на {$targetClient->fio}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Произошла ошибка при совмещении: ' . $e->getMessage()]);
        }
    }
}