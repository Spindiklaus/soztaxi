<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends BaseController {

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {

        $query = User::with('roles');

        // Фильтрация
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        // Фильтрация по статусу "life" только тогда, когда он передан в запросе
        if ($request->filled('life')) { //  параметр life существует в запросе и его значение не является null
            $query->where('life', $request->input('life'));
        }
        // Сортировка
        $sort = $request->input('sort', 'id'); // По умолчанию сортируем по ID
        $direction = $request->input('direction', 'asc'); // По умолчанию ASC
        
        $allowedSorts = ['id', 'name', 'email', 'life']; // Разрешенные поля для сортировки
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
       
        // Проверка направления сортировки
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }
        
        // Применяем сортировку
        $users = $query->orderBy($sort, $direction)->paginate(10);

        return view('users.index', compact('users', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        // Загружаем все роли для выбора
        $roles = Role::all()->pluck('name', 'id');

        return view('users.create', compact('roles'));
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        // Валидация данных
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed', // Пароль обязателен при создании
            'litera' => 'string|max:255',
            'life' => 'required|boolean',
            'role' => 'nullable|exists:roles,id',
        ]);

        // Создаем пользователя
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'litera' => $validated['litera'],
            'life' => $validated['life'],
        ]);

        // Назначаем роль, если она выбрана
        if ($request->has('role') && $validated['role']) {
            $roleName = Role::findOrFail($validated['role'])->name;
            $user->assignRole($roleName);
        }

        return redirect()->route('users.index')->with('success', 'Пользователь успешно создан.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user) {

        // Загружаем все роли
        $roles = Role::all()->pluck('name', 'id');

        // Загружаем роли, назначенные пользователю
//        $userRoles = $user->roles->pluck('id')->toArray();
        $userRole = $user->roles->first()?->id; // Берем первую роль пользователя (если есть)

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed', // Пароль не обязателен, но если указан, должен быть минимум 6 символов
            'litera' => 'string|max:3|min:1', // Литера (обязательное поле)
            'life' => 'required|boolean', // Действующий (true/false)
            'role' => 'nullable|exists:roles,id', // Роль может быть пустой
        ]);

        // Обновляем основные данные пользователя
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'litera' => $validated['litera'],
            'life' => $validated['life'],
        ];

        // Если пароль указан, хэшируем его
        if ($request->filled('password')) {
            $data['password'] = bcrypt($validated['password']);
        }
        $user->update($data);

        // Обновление роли
        if ($request->has('role') && $validated['role']) {
            $user->roles()->sync([$validated['role']]); // Синхронизируем роли по ID
        } else {
            $user->roles()->sync([]); // Удаляем все роли
        }


//        return redirect()->route('users.index')->with('success', 'Пользователь успешно обновлен.');
//         return redirect()->route('users.edit', $user)->with('success', 'Пользователь успешно обновлен.');
//        return redirect(url()->previous())->with('success', 'Пользователь успешно обновлен.');
        return redirect()->route('users.index', $request->query())->with('success', 'Пользователь успешно обновлен');
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }

    public function assignRole(Request $request, User $user) {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $role = Role::findByName($request->role);
        $user->syncRoles([$role]);

        return response()->json(['success' => true]);
    }

}
