<!-- Параметры сортировки -->
@if (request()->has('sort')) 
    <input type="hidden" name="sort" value="{{ request('sort') }}">
@endif
@if (request()->has('direction')) 
    <input type="hidden" name="direction" value="{{ request('direction') }}">
@endif
<!-- Фильтрация по номеру заказа -->
@if(request()->has('filter_pz_nom'))
    <input type="hidden" name="filter_pz_nom" value="{{ request('filter_pz_nom') }}">
@endif
<!-- Фильтрация по типу заказа-->
@if (request()->has('filter_type_order')) 
    <input type="hidden" name="filter_type_order" value="{{ request('filter_type_order') }}">
@endif                
<!-- Фильтрация по статусу записей (все или только неудаленные) -->
@if (request()->has('show_deleted')) 
    <input type="hidden" name="show_deleted" value="{{ request('show_deleted') }}">
@endif
<!-- Фильтрация по статусу заказа -->
@if (request()->has('status_order_id')) 
    <input type="hidden" name="status_order_id" value="{{ request('status_order_id') }}">
@endif
<!-- Фильтрация по оператору -->
@if (request()->has('filter_user_id')) 
    <input type="hidden" name="filter_user_id" value="{{ request('filter_user_id') }}">
@endif
<!-- Фильтрация по ФИО -->
@if (request()->has('client_fio')) 
    <input type="hidden" name="client_fio" value="{{ request('client_fio') }}">
@endif
<!-- Фильтрация по датам -->
@if (request()->has('date_from')) 
    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
@endif
@if (request()->has('date_to')) 
    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
@endif 
