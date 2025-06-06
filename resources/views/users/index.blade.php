<x-app-layout>
    <div class="container" x-data="userData()">
        <h1 class="text-2xl font-bold mb-4">Пользователи</h1>

        <!-- Поиск -->
        <div class="mb-4">
            <input type="text" x-model="search" placeholder="Поиск по имени или email..." class="border px-3 py-2 w-full md:w-1/2">
        </div>

        <!-- Таблица -->
        <table class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b">ID</th>
                    <th class="py-2 px-4 border-b">Имя</th>
                    <th class="py-2 px-4 border-b">Email</th>
                    <th class="py-2 px-4 border-b">Литера</th>
                    <th class="py-2 px-4 border-b">Действующий</th>
                    <th class="py-2 px-4 border-b">Роли</th>
                    <th class="py-2 px-4 border-b">Назначить роль</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="user in filteredUsers" :key="user.id">
                    <tr>
                        <td class="py-2 px-4 border-b text-center" x-text="user.id"></td>
                        <td class="py-2 px-4 border-b" x-text="user.name"></td>
                        <td class="py-2 px-4 border-b" x-text="user.email"></td>
                        <td class="py-2 px-4 border-b" x-text="user.litera"></td>
                        <td class="py-2 px-4 border-b" x-text="user.life"></td>
                        <td class="py-2 px-4 border-b">
                            <template x-if="user.roles.length > 0">
                                <span x-text="user.roles.map(r => r.name).join(', ')"></span>
                            </template>
                            <template x-if="user.roles.length === 0">
                                Нет ролей
                            </template>
                        </td>
                        <td class="py-2 px-4 border-b">
                            <select x-model="user.selectedRole"
                                    @change="assignRole(user)"
                                    class="border rounded px-2 py-1 w-full">
                                <option value="">Выберите роль</option>
                                <template x-for="role in roles" :key="role">
                                    <option :value="role" x-text="role"></option>
                                </template>
                            </select>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <script>
        function userData() {
            return {
                search: '',
                users: @json($users),
                roles: @json(\Spatie\Permission\Models\Role::pluck('name')),
                get filteredUsers() {
                    if (!this.search) return this.users;
                    const term = this.search.toLowerCase();
                    return this.users.filter(u =>
                        u.name.toLowerCase().includes(term) ||
                        u.email.toLowerCase().includes(term)
                    );
                },
                async assignRole(user) {
                    if (!user.selectedRole) return;

                    try {
                        const res = await fetch(`/users/${user.id}/assign-role`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ role: user.selectedRole }),
                        });

                        const data = await res.json();

                        if (data.success) {
                            // Обновляем локальный список ролей у пользователя
                            user.roles = [user.selectedRole];
                            alert('Роль успешно назначена');
                        } else {
                            alert('Ошибка при назначении роли');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Произошла ошибка');
                    }
                }
            };
        }
    </script>
</x-app-layout>