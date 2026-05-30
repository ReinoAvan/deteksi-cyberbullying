<div
    class="space-y-6"
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
    <div
        x-show="toast"
        x-transition
        class="fixed right-4 top-4 z-50 rounded-lg border border-emerald-200 bg-white px-4 py-3 text-sm font-medium text-emerald-700 shadow-lg"
        x-text="toast?.message"
    ></div>

<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Student Management</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">Manage Student Data</h1>
        <p class="mt-1 text-sm text-slate-500">
            Add, update, import, export, and review student records from one clean workspace.
        </p>
    </div>

    <div class="flex flex-wrap gap-2">
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/>
            </svg>
            Add Student
        </button>

        <form wire:submit.prevent="importExcel">
            <label class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0-12 4 4m-4-4-4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>
                </svg>

                <span>Import</span>

                <input
                    wire:model="importFile"
                    type="file"
                    accept=".csv,.txt,.xls,.xlsx"
                    class="hidden"
                    onchange="this.form.requestSubmit()"
                >
            </label>
        </form>

        <div class="relative" x-on:click.outside="exportOpen = false">
            <button
                type="button"
                x-on:click="exportOpen = ! exportOpen"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 lg:w-auto"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 19h16"/>
                </svg>
                Export
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                </svg>
            </button>

            <div
                x-show="exportOpen"
                x-transition
                class="absolute right-0 z-20 mt-2 w-48 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg"
            >
                <button
                    wire:click="exportPdf"
                    x-on:click="exportOpen = false"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                >
                    Export to PDF
                </button>

                <button
                    wire:click="exportExcel"
                    x-on:click="exportOpen = false"
                    class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                >
                    Export to Excel
                </button>
            </div>
        </div>
    </div>
</div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Students</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalStudents }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Visible Rows</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $students->count() }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Classes</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $classOptions->count() }}</p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Class Management</h2>
                <p class="mt-1 text-sm text-slate-500">Add classes, remove unused classes, and monitor student totals.</p>
            </div>
            <form wire:submit.prevent="addClass" class="flex flex-col gap-2 sm:flex-row">
                <input wire:model="newClassName" type="text" placeholder="Class name" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    Add Class
                </button>
            </form>
        </div>
        @error('newClassName') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror

        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($classStats as $classStat)
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                        <p class="font-bold text-slate-900">{{ $classStat['name'] }}</p>
                        <p class="text-sm text-slate-500">{{ $classStat['total'] }} students</p>
                    </div>
                    <button wire:click="deleteClass(@js($classStat['name']))" wire:confirm="Delete this class?" title="Delete class" class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397"/></svg>
                    </button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4">
            <div class="grid gap-3 lg:grid-cols-[1fr_180px_160px]">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                    <input wire:model.live.debounce.350ms="search" type="search" placeholder="Search by NIS or name" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select wire:model.live="classFilter" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Classes</option>
                    @foreach($classOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>

                <select wire:model.live="perPage" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                    <option value="10">10 per page</option>
                    <option value="20">20 per page</option>
                    <option value="50">50 per page</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>

        <div class="relative overflow-hidden">
            <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/70 backdrop-blur-sm">
                <div class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow">Loading students...</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Profile Photo</th>
                            <th class="px-4 py-3">
                                <button wire:click="sortBy('nis')" class="inline-flex items-center gap-1 hover:text-indigo-600">
                                    Student ID (NIS)
                                    <span class="text-slate-400">
                                        @if($sortField === 'nis' && $sortDirection === 'desc')
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                        @else
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
                                        @endif
                                    </span>
                                </button>
                            </th>
                            <th class="px-4 py-3">
                                <button wire:click="sortBy('name')" class="inline-flex items-center gap-1 hover:text-indigo-600">
                                    Name
                                    <span class="text-slate-400">
                                        @if($sortField === 'name' && $sortDirection === 'desc')
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                        @else
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
                                        @endif
                                    </span>
                                </button>
                            </th>
                            <th class="px-4 py-3">
                                <button wire:click="sortBy('class')" class="inline-flex items-center gap-1 hover:text-indigo-600">
                                    Class
                                    <span class="text-slate-400">
                                        @if($sortField === 'class' && $sortDirection === 'desc')
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                        @else
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
                                        @endif
                                    </span>
                                </button>
                            </th>
                            <th class="px-4 py-3">
                                <button wire:click="sortBy('gender')" class="inline-flex items-center gap-1 hover:text-indigo-600">
                                    Gender
                                    <span class="text-slate-400">
                                        @if($sortField === 'gender' && $sortDirection === 'desc')
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                        @else
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
                                        @endif
                                    </span>
                                </button>
                            </th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($students as $student)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    @if($this->photoUrl($student->profile_photo))
                                        <img src="{{ $this->photoUrl($student->profile_photo) }}" alt="{{ $student->name }}" class="h-11 w-11 rounded-full object-cover ring-2 ring-white">
                                    @else
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $student->nis }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $student->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $student->class }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $this->normalizeGender($student->gender) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="showDetail({{ $student->id }})" title="Detail" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                                        </button>
                                        <button wire:click="openEditModal({{ $student->id }})" title="Edit" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L8.25 18.402 3.75 19.5l1.098-4.5 12.014-10.513Z"/></svg>
                                        </button>
                                        <button wire:click="delete({{ $student->id }})" wire:confirm="Delete this student?" title="Delete" class="rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916A2.25 2.25 0 0 0 13.5 2.25h-3A2.25 2.25 0 0 0 8.25 4.5v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72M13.5 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM4.5 19.5a4.5 4.5 0 0 1 9 0"/></svg>
                                        </div>
                                        <p class="mt-3 font-semibold text-slate-800">No student data found</p>
                                        <p class="mt-1 text-sm text-slate-500">Add a new student or adjust your search and filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($perPage !== 'all')
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $students->links() }}
                </div>
            @endif
        </div>
    </div>

    @if($showFormModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/50 p-4">
            <div class="w-full max-w-xl rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-bold text-slate-900">{{ $editId ? 'Edit Student' : 'Add Student' }}</h2>
                    <button wire:click="$set('showFormModal', false)" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="space-y-4 px-5 py-5">
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Profile Photo</label>
                        <input wire:model="profilePhoto" type="file" accept="image/png,image/jpeg,image/webp" class="mt-2 block w-full rounded-lg border border-slate-200 text-sm text-slate-600 file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                        @error('profilePhoto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Student ID (NIS)</label>
                            <input wire:model="nis" type="text" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            @error('nis') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Class</label>
                            <select wire:model="class" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                <option value="">Select class</option>
                                @foreach($classOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('class') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Name</label>
                            <input wire:model="name" type="text" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Gender</label>
                            <select wire:model="gender" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            @error('gender') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showFormModal', false)" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save Student</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showDetailModal && $selectedStudent)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-bold text-slate-900">Student Detail</h2>
                    <button wire:click="$set('showDetailModal', false)" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-5 py-6">
                    <div class="flex flex-col items-center text-center">
                        @if($this->photoUrl($selectedStudent->profile_photo))
                            <img src="{{ $this->photoUrl($selectedStudent->profile_photo) }}" alt="{{ $selectedStudent->name }}" class="h-24 w-24 rounded-full object-cover ring-4 ring-indigo-50">
                        @else
                            <div class="flex h-24 w-24 items-center justify-center rounded-full bg-indigo-100 text-3xl font-bold text-indigo-700">
                                {{ strtoupper(substr($selectedStudent->name, 0, 1)) }}
                            </div>
                        @endif
                        <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $selectedStudent->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $selectedStudent->nis }}</p>
                    </div>

                    <dl class="mt-6 grid gap-3">
                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Student ID (NIS)</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selectedStudent->nis }}</dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selectedStudent->name }}</dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Class</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $selectedStudent->class }}</dd>
                        </div>
                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Gender</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $this->normalizeGender($selectedStudent->gender) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    @endif
</div>
