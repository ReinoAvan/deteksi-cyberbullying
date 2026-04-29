<div class="space-y-6">

    <!-- HEADER -->
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Siswa</h1>
        <p class="text-sm text-gray-500">Kelola data siswa dan pantau tingkat risiko perilaku mereka.</p>
    </div>

    <!-- FILTER & ACTION -->
    <div class="flex flex-wrap gap-3 items-center justify-between">

        <div class="flex gap-2 flex-wrap">
            <input type="text" placeholder="Cari nama atau NIS siswa..."
                   class="border rounded-lg px-4 py-2 w-64 text-sm bg-white shadow-sm">

            <select class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option>Semua Kelas</option>
            </select>

            <select class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option>Semua Risiko</option>
            </select>

            <select class="border rounded-lg px-3 py-2 text-sm bg-white">
                <option>Semua Status</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">+ Tambah Siswa</button>
            <button class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm">Import Data</button>
            <button class="bg-green-500 text-white px-4 py-2 rounded-lg text-sm">Export Data</button>
        </div>
    </div>

    <!-- CARD STATS -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

        <div class="bg-white rounded-xl p-4 shadow">
            <p class="text-sm text-gray-500">Total Siswa</p>
            <h2 class="text-2xl font-bold">40</h2>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
            <p class="text-sm text-green-600">Risiko Rendah</p>
            <h2 class="text-2xl font-bold text-green-700">22</h2>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
            <p class="text-sm text-yellow-600">Risiko Sedang</p>
            <h2 class="text-2xl font-bold text-yellow-700">12</h2>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <p class="text-sm text-red-600">Risiko Tinggi</p>
            <h2 class="text-2xl font-bold text-red-700">6</h2>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm text-blue-600">Perlu Perhatian</p>
            <h2 class="text-2xl font-bold text-blue-700">8</h2>
        </div>

    </div>

    <!-- MAIN CONTENT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT -->
        <div class="lg:col-span-2 space-y-6">

            <!-- CHART -->
            <div class="bg-white rounded-xl p-4 shadow">
                <h3 class="font-semibold mb-4">Distribusi Siswa Berdasarkan Risiko</h3>
                <div class="h-48 flex items-center justify-center text-gray-400">
                    (Chart disini)
                </div>
            </div>

            <!-- TABLE -->
            <div class="bg-white rounded-xl shadow p-4">
                <h3 class="font-semibold mb-4">Daftar Siswa</h3>

                <table class="w-full text-sm">
                    <thead class="text-gray-500 border-b">
                        <tr>
                            <th class="py-2 text-left">Nama</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Risiko</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($students as $s)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 font-medium">{{ $s->name }}</td>
                            <td>{{ $s->nis }}</td>
                            <td>{{ $s->class }}</td>

                            <td>
                                <span class="px-2 py-1 rounded text-xs 
                                    {{ $s->status == 'Aktif' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $s->status }}
                                </span>
                            </td>

                            <td>
                                <span class="px-2 py-1 rounded text-xs
                                    {{ $s->risk_level == 'Tinggi' ? 'bg-red-100 text-red-600' : 
                                       ($s->risk_level == 'Sedang' ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') }}">
                                    {{ $s->risk_level }}
                                </span>
                            </td>

                            <td class="flex gap-2">
                                <button wire:click="edit({{ $s->id }})" class="text-blue-500">✏️</button>
                                <button wire:click="delete({{ $s->id }})" class="text-red-500">🗑️</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="bg-white rounded-xl p-4 shadow">

            <h3 class="font-semibold mb-4">Detail Siswa</h3>

            <div class="text-center mb-4">
                <div class="w-20 h-20 bg-gray-200 rounded-full mx-auto mb-2"></div>
                <h4 class="font-bold">Budi Santoso</h4>
                <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded">Risiko Tinggi</span>
            </div>

            <div class="space-y-2 text-sm text-gray-600">
                <p><b>NIS:</b> 1234567892</p>
                <p><b>Kelas:</b> 5A</p>
                <p><b>Status:</b> Aktif</p>
            </div>

            <div class="mt-4">
                <p class="text-sm font-semibold mb-2">Skor Risiko</p>
                <div class="bg-gray-200 h-2 rounded-full">
                    <div class="bg-red-500 h-2 rounded-full w-[78%]"></div>
                </div>
            </div>

        </div>

    </div>

</div>