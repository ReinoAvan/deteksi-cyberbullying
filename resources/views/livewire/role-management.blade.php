<div
    class="min-w-0 space-y-6 overflow-x-hidden"
    x-data="{
        toast: null,
        exportOpen: false,
        showToast(event) {
            this.toast = event.detail;
            setTimeout(() => this.toast = null, 3000);
        }
    }"
    x-on:notify.window="showToast($event)"
>
    <div x-show="toast" x-transition class="fixed right-4 top-4 z-50 rounded-lg border px-4 py-3 text-sm font-medium shadow-lg" x-bind:class="toast?.type === 'error' ? 'border-red-200 bg-white text-red-700' : 'border-emerald-200 bg-white text-emerald-700'" x-text="toast?.message"></div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Manajemen Role</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Role Management</h1>
            <p class="mt-1 text-sm text-slate-500">Manage user roles with import, export, and clean access records.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button wire:click="openCreateModal" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                Add Role
            </button>
            <form wire:submit.prevent="importExcel">
                <label class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 19h16"/></svg>
                    <span>Import</span>
                    <input wire:model="importFile" type="file" accept=".csv,.txt,.xls,.xlsx" class="hidden" onchange="this.form.requestSubmit()">
                </label>
            </form>
            <div class="relative" x-on:click.outside="exportOpen = false">
                <button type="button" x-on:click="exportOpen = ! exportOpen" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 lg:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15V3m0 0 4 4m-4-4-4 4M4 19h16"/></svg>
                    Export
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                </button>
                <div x-show="exportOpen" x-transition class="absolute right-0 z-20 mt-2 w-48 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button wire:click="exportPdf" x-on:click="exportOpen = false" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Export to PDF</button>
                    <button wire:click="exportExcel" x-on:click="exportOpen = false" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Export to Excel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Super Admin</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalSuperAdmin }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Admin</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalAdmin }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Wali Kelas</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalWaliKelas }}</p>
        </div>
    </div>

    <div class="min-w-0 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4">
            <div class="grid gap-3 lg:grid-cols-[1fr_160px]">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                    <input wire:model.live.debounce.350ms="search" type="search" placeholder="Search username, email, or role" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>
                <select wire:model.live="perPage" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="10">10 per page</option>
                    <option value="20">20 per page</option>
                    <option value="50">50 per page</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>

        <div class="relative min-w-0 overflow-hidden">
            <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/70 backdrop-blur-sm">
                <div class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow">Loading roles...</div>
            </div>
            <div class="min-w-0 overflow-x-auto">
                <table class="w-full min-w-full table-auto divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="w-20 px-4 py-3">ID</th>
                            @foreach(['username' => 'Username', 'email' => 'Email', 'role' => 'Role'] as $field => $label)
                                <th class="px-4 py-3">
                                    <button wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-1 hover:text-indigo-600">{{ $label }}
                                        <span class="text-slate-400">
                                            @if($sortField === $field && $sortDirection === 'desc')
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
                                            @endif
                                        </span>
                                    </button>
                                </th>
                            @endforeach
                            <th class="w-36 px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($roles as $roleUser)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $roleUser->id }}</td>
                                <td class="break-words px-4 py-3 text-slate-700">{{ $roleUser->username }}</td>
                                <td class="break-words px-4 py-3 text-slate-700">{{ $roleUser->email }}</td>
                                <td class="px-4 py-3"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $roleUser->role }}</span></td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="showDetail({{ $roleUser->id }})" title="View" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg></button>
                                        <button wire:click="openEditModal({{ $roleUser->id }})" title="Edit" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L8.25 18.402 3.75 19.5l1.098-4.5 12.014-10.513Z"/></svg></button>
                                        <button wire:click="delete({{ $roleUser->id }})" wire:confirm="Delete this role?" title="Delete" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">No role data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($perPage !== 'all')
                <div class="border-t border-slate-200 px-4 py-3">{{ $roles->links() }}</div>
            @endif
        </div>
    </div>

    @if($showFormModal)
        <div class="fixed inset-0 z-40 !m-0 flex items-center justify-center bg-slate-950/50 p-4">
            <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">{{ $editId ? 'Edit Role' : 'Add Role' }}</h2>
                    <button wire:click="$set('showFormModal', false)" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                </div>
                <form wire:submit.prevent="save" class="space-y-4 px-5 py-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Username</label>
                            <input wire:model="username" type="text" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Email</label>
                            <input wire:model="email" type="email" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Password</label>
                            <input wire:model="password" type="password" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Role</label>
                            <select wire:model="role" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                @foreach($roleOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showFormModal', false)" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save Role</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showDetailModal && $selectedRoleUser)
        <div class="fixed inset-0 z-40 !m-0 flex items-center justify-center bg-slate-950/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Role Detail</h2>
                    <button wire:click="$set('showDetailModal', false)" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                </div>
                <dl class="space-y-3 px-5 py-6">
                    <div class="rounded-lg bg-slate-50 px-4 py-3"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Username</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selectedRoleUser->username }}</dd></div>
                    <div class="rounded-lg bg-slate-50 px-4 py-3"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selectedRoleUser->email }}</dd></div>
                    <div class="rounded-lg bg-slate-50 px-4 py-3"><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Role</dt><dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selectedRoleUser->role }}</dd></div>
                </dl>
            </div>
        </div>
    @endif
</div>
