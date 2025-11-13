<!-- operator-orders.index-components/operator-routes.blade.php -->
@php
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
@endphp