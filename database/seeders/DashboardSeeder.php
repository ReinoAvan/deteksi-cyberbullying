<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BehaviorLog;

class DashboardSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['date' => '2023-08-24', 'level' => 3, 'response' => 'Komentar mengejek', 'indicator' => 'Tekanan Sebaya', 'value' => 4, 'risk' => 'Tinggi'],
            ['date' => '2023-08-23', 'level' => 2, 'response' => 'Melapor ke guru', 'indicator' => 'Anonimitas', 'value' => 3, 'risk' => 'Sedang'],
            ['date' => '2023-08-23', 'level' => 1, 'response' => 'Melarang teman mengejek', 'indicator' => 'Empati', 'value' => 2, 'risk' => 'Rendah'],
        ];

        foreach ($data as $item) {
            BehaviorLog::create([
                'date' => $item['date'],
                'level' => $item['level'],
                'response' => $item['response'],
                'indicator' => $item['indicator'],
                'indicator_value' => $item['value'],
                'risk_category' => $item['risk'],
            ]);
        }
    }
}