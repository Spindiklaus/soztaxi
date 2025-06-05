<?php

namespace App\Http\Controllers\Operator;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends BaseController {

    /**
     * Display a listing of the resource.
     */
    public function index() {
        // Загружаем роли пользователя
        $users = User::with('roles')->get()->map(function ($user) {
            return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'roles' => $user->roles->map(function ($role) {
            return [
        'name' => $role->name,
            ];
        })->toArray(),
            ];
        });
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
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
    public function edit(string $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
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
