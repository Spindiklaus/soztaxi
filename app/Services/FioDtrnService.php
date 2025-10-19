<?php

namespace App\Services;

use App\Models\Order;
use App\Models\FioDtrn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class FioDtrnService {

    public function getUrlParams() {
        // Определяем, какие параметры запроса нам нужны
        $params = request()->only([
            'sort',
            'direction',
            'filter_fio',
            'filter_kl_id',
            'filter_sex',
            'rip',
            'visit_date_from',
            'visit_date_to',
            'page', 
            // Добавьте другие параметры, если они используются
        ]);

        // \Log::info('FioDtrnService result', ['params' => $params]);
        return $params;
    }

}
