<?php

namespace App\Http\Controllers\Operator;

use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class RoleController extends BaseController {

   public function index(Request $request)
{
    $sort = $request->get('sort', 'id');
    $direction = $request->get('direction', 'asc');
    $search = $request->get('name', '');

    $rolesQuery = Role::query();

    if ($search) {
        $rolesQuery->where('name', 'like', "%$search%");
    }

    $roles = $rolesQuery->orderBy($sort, $direction)->paginate(10);

    return view('roles.index', compact('roles', 'sort', 'direction', 'search'));
}

    public function create() {
        return view('roles.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        Role::create(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Роль успешно создана.');
    }

    public function show(Role $role) {
        return view('roles.show', compact('role'));
    }

    public function edit(Role $role) {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role) {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Роль обновлена.');
    }

    public function destroy(Role $role) {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Роль удалена.');
    }

}
