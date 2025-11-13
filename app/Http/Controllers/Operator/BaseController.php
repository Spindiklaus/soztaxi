<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;

//use Illuminate\Http\Request;

abstract class BaseController extends Controller {

    //
    public function __construct() {
//        $this->middleware('auth');
    }

    protected function getOperatorRouteData() {
        $fromOperatorPage = session('from_operator_page');
        $operatorCurrentType = session('operator_current_type');
        $routeMap = [
            1 => 'operator.social-taxi.index',
            2 => 'operator.car.index',
            3 => 'operator.gazelle.index'
        ];
        $operatorRoute = null;

        if ($fromOperatorPage && $operatorCurrentType && isset($routeMap[$operatorCurrentType])) {
            $operatorRoute = $routeMap[$operatorCurrentType];
        }

        return compact('operatorRoute', 'operatorCurrentType');
    }

}
