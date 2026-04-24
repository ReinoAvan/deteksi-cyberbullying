<div class="flex min-h-screen bg-[#F5F7FB] font-sans">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-gradient-to-b from-indigo-600 to-purple-600 text-white p-5 space-y-6">
        <div>
            <h1 class="text-xl font-bold">🛡️ SafeSpace</h1>
            <p class="text-xs opacity-70">Cyberbullying Monitor</p>
        </div>

        <nav class="space-y-2">
            <div class="bg-white/20 p-2 rounded-lg">🏠 Dashboard</div>
            <div class="opacity-80">📊 Monitoring Real-time</div>
            <div class="opacity-80">📄 Laporan & Kasus</div>
            <div class="opacity-80">📈 Analisis Perilaku</div>
            <div class="opacity-80">👥 Siswa Berisiko</div>
        </nav>

        <div class="mt-auto bg-white/10 p-4 rounded-xl text-sm">
            <p class="font-semibold">AI Protection aktif</p>
            <p class="text-xs opacity-70">Monitoring real-time</p>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="flex-1 p-6 space-y-6">

        <!-- HEADER -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Dashboard Guru: Risiko Cyberbullying</h1>
                <p class="text-sm text-gray-500">Selamat datang, Ibu Indah 👋</p>
            </div>

            <div class="flex gap-3 items-center">
                <button class="bg-white px-4 py-2 rounded-lg shadow">🏠 Beranda</button>
                <div class="relative">
                    🔔
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-1 rounded-full">3</span>
                </div>
                <div class="bg-white px-3 py-2 rounded-lg shadow flex items-center gap-2">
                    👩 Ibu Indah
                </div>
            </div>
        </div>

        <!-- FILTER -->
        <div class="flex justify-between items-center">
            <div class="flex gap-3">
                <select class="bg-white border rounded-lg px-3 py-2">
                    <option>Kelas 5A</option>
                </select>
                <select class="bg-white border rounded-lg px-3 py-2">
                    <option>Semua</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button class="bg-blue-500 text-white px-4 py-2 rounded-lg">Cetak Laporan</button>
                <button class="bg-purple-600 text-white px-4 py-2 rounded-lg">Unduh Data</button>
            </div>
        </div>

        <!-- CARD RISIKO -->
        <div class="grid grid-cols-3 gap-4">

            <div class="bg-green-100 border border-green-300 rounded-xl p-5 flex justify-between">
                <div>
                    <p class="text-green-700 font-semibold">Risiko Rendah</p>
                    <h2 class="text-3xl font-bold">{{ $stats['rendah'] }}</h2>
                </div>
                <div class="text-4xl">🙂</div>
            </div>

            <div class="bg-orange-100 border border-orange-300 rounded-xl p-5 flex justify-between">
                <div>
                    <p class="text-orange-600 font-semibold">Risiko Sedang</p>
                    <h2 class="text-3xl font-bold">{{ $stats['sedang'] }}</h2>
                </div>
                <div class="text-4xl">😐</div>
            </div>

            <div class="bg-red-100 border border-red-300 rounded-xl p-5 flex justify-between">
                <div>
                    <p class="text-red-600 font-semibold">Risiko Tinggi</p>
                    <h2 class="text-3xl font-bold">{{ $stats['tinggi'] }}</h2>
                </div>
                <div class="text-4xl">☹️</div>
            </div>

        </div>

        <!-- CHART + SISWA -->
        <div class="grid grid-cols-3 gap-4">

            <div class="col-span-2 bg-white p-4 rounded-xl shadow">
                <h3 class="font-semibold mb-3">Distribusi Risiko</h3>
                <canvas id="chart"></canvas>
            </div>

            <div class="bg-white p-4 rounded-xl shadow">
                <h3 class="font-semibold mb-3 text-red-500">Siswa Perlu Tindak Lanjut</h3>

                @foreach(['Siswa 23','Siswa 12','Siswa 35'] as $siswa)
                <div class="flex justify-between py-2 border-b">
                    <span>{{ $siswa }}</span>
                    <button class="text-red-500 text-sm">Detail</button>
                </div>
                @endforeach
            </div>

        </div>

        <!-- TABEL -->
        <div class="bg-white rounded-xl shadow p-4">
            <h3 class="font-semibold mb-3">Perilaku Umum</h3>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500 border-b">
                        <th class="p-2 text-left">Tanggal</th>
                        <th class="p-2">Level</th>
                        <th class="p-2 text-left">Respons</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr class="border-b">
                        <td class="p-2">{{ $log->date }}</td>
                        <td class="p-2 text-center">{{ $log->level }}</td>
                        <td class="p-2">{{ $log->response }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    <!-- CHART -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            new Chart(document.getElementById('chart'), {
                type: 'bar',
                data: {
                    labels: ['Rendah','Sedang','Tinggi'],
                    datasets: [{
                        data: [12,3,2],
                        backgroundColor: ['#22c55e','#f97316','#ef4444']
                    }]
                }
            });
        });
    </script>

</div>