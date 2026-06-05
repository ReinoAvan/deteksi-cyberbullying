<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use ZipArchive;

class StudentManagement extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $classFilter = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public string $perPage = '10';

    public bool $showFormModal = false;
    public bool $showDetailModal = false;
    public ?int $editId = null;
    public ?Student $selectedStudent = null;

    public $profilePhoto;
    public $importFile;
    public ?string $existingProfilePhoto = null;
    public string $nis = '';
    public string $name = '';
    public string $class = '';
    public string $gender = 'Male';
    public string $newClassName = '';
    public ?string $editingClassOriginal = null;
    public string $editingClassName = '';

    protected array $queryString = [
        'search' => ['except' => ''],
        'classFilter' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => '10'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingClassFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['name', 'nis', 'class', 'gender'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $student = Student::findOrFail($id);

        $this->editId = $student->id;
        $this->nis = $student->nis;
        $this->name = $student->name;
        $this->class = $student->class;
        $this->gender = $this->normalizeGender($student->gender);
        $this->existingProfilePhoto = $student->profile_photo;
        $this->profilePhoto = null;
        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function showDetail(int $id): void
    {
        $this->selectedStudent = Student::findOrFail($id);
        $this->showDetailModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'profilePhoto' => [
                // ($this->editId && $this->existingProfilePhoto) ? 'nullable' : 'required',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'nis' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'nis')->ignore($this->editId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'class' => ['required', 'string', 'max:50', Rule::in($this->classOptions->all())],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
        ], [
            'profilePhoto.required' => 'Profile photo is required.',
            'profilePhoto.image' => 'Profile photo must be an image file.',
            'profilePhoto.mimes' => 'Profile photo must be a JPG, JPEG, PNG, or WEBP file.',
            'nis.unique' => 'Student ID (NIS) already exists.',
        ]);

        $data = [
            'nis' => trim($validated['nis']),
            'name' => trim($validated['name']),
            'class' => trim($validated['class']),
            'gender' => $validated['gender'],
        ];

        if ($this->profilePhoto) {
            $data['profile_photo'] = $this->storeProfilePhoto();
        }

        if ($this->editId) {
            Student::findOrFail($this->editId)->update($data);
        } else {
            Student::create($data + [
                'status' => 'Aktif',
                'risk_level' => 'Rendah',
            ]);
        }

        $this->showFormModal = false;
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Student data saved successfully.');
    }

    public function delete(int $id): void
    {
        $student = Student::findOrFail($id);
        $student->delete();

        $this->dispatch('notify', type: 'success', message: 'Student data deleted successfully.');
    }

    public function importExcel(): void
    {
        try {

            $this->validate([
                'importFile' => [
                    'required',
                    'file',
                    'mimes:csv,txt,xls,xlsx',
                    'max:5120',
                ],
            ], [
                'importFile.required' => 'File import wajib dipilih.',
                'importFile.mimes' => 'Format file harus CSV atau Excel.',
                'importFile.max' => 'Ukuran file maksimal 5MB.',
            ]);

            $rows = $this->readImportRows(
                $this->importFile->getRealPath(),
                $this->importFile->getClientOriginalExtension()
            );

            $created = 0;
            $updated = 0;
            $skipped = 0;

            $emptyNis = 0;
            $emptyName = 0;
            $emptyClass = 0;
            $invalidGender = 0;

            foreach ($rows as $row) {

                $nis = trim(
                    $row['student_id_(nis)']
                    ?? $row['student_id_nis']
                    ?? ''
                );

                $name = trim($row['name'] ?? '');

                $class = trim($row['class'] ?? '');

                $gender = $this->normalizeGender(
                    trim($row['gender'] ?? '')
                );

                $profilePhoto = trim(
                    $row['profile_photo']
                    ?? ''
                );

                $profilePhoto = $profilePhoto !== ''
                    ? $profilePhoto
                    : null;

                if ($nis === '') {
                    $emptyNis++;
                    $skipped++;
                    continue;
                }

                if ($name === '') {
                    $emptyName++;
                    $skipped++;
                    continue;
                }

                if ($class === '') {
                    $emptyClass++;
                    $skipped++;
                    continue;
                }

                if (! in_array($gender, ['Male', 'Female'], true)) {
                    $invalidGender++;
                    $skipped++;
                    continue;
                }

                StudentClass::firstOrCreate([
                    'name' => $class,
                ]);

                $payload = [
                    // 'profile_photo' => $profilePhoto,
                    'nis' => $nis,
                    'name' => $name,
                    'class' => $class,
                    'gender' => $gender,
                ];

                if ($profilePhoto !== null) {
                    $payload['profile_photo'] = $profilePhoto;
                }

                $student = Student::where('nis', $nis)->first();

                if ($student) {

                    $student->update($payload);

                    $updated++;

                } else {

                    Student::create($payload);

                    $created++;
                }
            }

            $this->importFile = null;

            $this->resetPage();

            $message = "Import berhasil. {$created} ditambahkan, {$updated} diperbarui, {$skipped} dilewati.";

            if ($emptyNis > 0) {
                $message .= " {$emptyNis} data NIS kosong.";
            }

            if ($emptyName > 0) {
                $message .= " {$emptyName} data nama kosong.";
            }

            if ($emptyClass > 0) {
                $message .= " {$emptyClass} data kelas kosong.";
            }

            if ($invalidGender > 0) {
                $message .= " {$invalidGender} data gender tidak valid.";
            }

            $this->dispatch(
                'notify',
                type: 'success',
                message: $message
            );

        } catch (\Throwable $e) {

            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Import gagal: ' . $e->getMessage()
            );
        }
    }

    public function exportExcel()
    {
        $students = $this->filteredQuery()->orderBy($this->sortField, $this->sortDirection)->get();
        $filename = 'students-' . now()->format('Y-m-d-His') . '.xls';

        return response()->streamDownload(function () use ($students) {
            echo '<table border="1">';
            echo '<tr><th>Profile Photo</th><th>Student ID (NIS)</th><th>Name</th><th>Class</th><th>Gender</th></tr>';

            foreach ($students as $student) {
                echo '<tr>';
                echo '<td>' . e($student->profile_photo ?? '') . '</td>';
                echo '<td>' . e($student->nis) . '</td>';
                echo '<td>' . e($student->name) . '</td>';
                echo '<td>' . e($student->class) . '</td>';
                echo '<td>' . e($this->normalizeGender($student->gender)) . '</td>';
                echo '</tr>';
            }

            echo '</table>';
        }, $filename, ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function exportPdf()
    {
        $students = $this->filteredQuery()->orderBy($this->sortField, $this->sortDirection)->get();
        $filename = 'students-' . now()->format('Y-m-d-His') . '.pdf';

        return response()->streamDownload(function () use ($students) {
            echo $this->buildSimplePdf($students);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function resetForm(): void
    {
        $this->reset(['editId', 'profilePhoto', 'existingProfilePhoto', 'nis', 'name', 'class']);
        $this->gender = 'Male';
        $this->resetValidation();
    }

    public function addClass(): void
    {
        $validated = $this->validate([
            'newClassName' => ['required', 'string', 'max:50', Rule::unique('student_classes', 'name')],
        ], [
            'newClassName.unique' => 'This class already exists.',
        ]);

        StudentClass::create(['name' => trim($validated['newClassName'])]);
        $this->newClassName = '';
        $this->dispatch('notify', type: 'success', message: 'Class added successfully.');
    }

    public function deleteClass(string $className): void
    {
        DB::transaction(function () use ($className) {
            Student::where('class', $className)->delete();
            StudentClass::where('name', $className)->delete();
        });

        $this->dispatch('notify', type: 'success', message: 'Class deleted successfully.');
    }

    public function startEditClass(string $className): void
    {
        $this->editingClassOriginal = $className;
        $this->editingClassName = $className;
        $this->resetValidation(['editingClassName']);
    }

    public function cancelEditClass(): void
    {
        $this->editingClassOriginal = null;
        $this->editingClassName = '';
        $this->resetValidation(['editingClassName']);
    }

    public function updateClass(): void
    {
        if (! $this->editingClassOriginal) {
            return;
        }

        $validated = $this->validate([
            'editingClassName' => [
                'required',
                'string',
                'max:50',
                Rule::unique('student_classes', 'name')->ignore(
                    StudentClass::where('name', $this->editingClassOriginal)->value('id')
                ),
            ],
        ]);

        $oldName = $this->editingClassOriginal;
        $newName = trim($validated['editingClassName']);

        DB::transaction(function () use ($oldName, $newName) {
            StudentClass::updateOrCreate(['name' => $oldName], ['name' => $newName]);
            Student::where('class', $oldName)->update(['class' => $newName]);
        });

        $this->cancelEditClass();
        $this->dispatch('notify', type: 'success', message: 'Class updated successfully.');
    }

    public function getClassOptionsProperty()
    {
        $managedClasses = StudentClass::query()
            ->orderBy('name')
            ->pluck('name');

        $studentClasses = Student::query()
            ->select('class')
            ->whereNotNull('class')
            ->distinct()
            ->orderBy('class')
            ->pluck('class');

        return $managedClasses
            ->merge($studentClasses)
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    public function getClassStatsProperty()
    {
        $counts = Student::query()
            ->select('class', DB::raw('count(*) as total'))
            ->groupBy('class')
            ->pluck('total', 'class');

        return $this->classOptions->map(fn ($class) => [
            'name' => $class,
            'total' => (int) ($counts[$class] ?? 0),
        ]);
    }

    public function getTotalStudentsProperty(): int
    {
        return Student::count();
    }

    public function getTotalMaleProperty(): int
    {
        return Student::where('gender', 'Male')->count();
    }

    public function getTotalFemaleProperty(): int
    {
        return Student::where('gender', 'Female')->count();
    }

    public function getClassDistributionChartProperty(): array
    {
        $rows = Student::query()
            ->select('class', 'gender', DB::raw('count(*) as total'))
            ->groupBy('class', 'gender')
            ->get();

        $classes = $this->classOptions->all();

        return [
            'labels' => $classes,
            'male' => collect($classes)->map(fn ($class) => (int) optional($rows->first(fn ($row) => $row->class === $class && $this->normalizeGender($row->gender) === 'Male'))->total)->values(),
            'female' => collect($classes)->map(fn ($class) => (int) optional($rows->first(fn ($row) => $row->class === $class && $this->normalizeGender($row->gender) === 'Female'))->total)->values(),
        ];
    }

    public function render()
    {
        $query = $this->filteredQuery()->orderBy($this->sortField, $this->sortDirection);
        $students = $this->perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $this->perPage);

        return view('livewire.student-management', [
            'students' => $students,
            'classOptions' => $this->classOptions,
            'classStats' => $this->classStats,
            'totalStudents' => $this->totalStudents,
            'totalMale' => $this->totalMale,
            'totalFemale' => $this->totalFemale,
            'classDistributionChart' => $this->classDistributionChart,
        ])->layout('layouts.app');
    }

    private function filteredQuery()
    {
        return Student::query()
            ->when($this->search !== '', function ($query) {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('nis', 'like', $search)
                        ->orWhere('name', 'like', $search);
                });
            })
            ->when($this->classFilter !== '', fn ($query) => $query->where('class', $this->classFilter));
    }

    private function storeProfilePhoto(): string
    {
        $directory = public_path('student-photos');
        File::ensureDirectoryExists($directory);

        $filename = Str::uuid() . '.' . $this->profilePhoto->getClientOriginalExtension();
        File::copy($this->profilePhoto->getRealPath(), $directory . DIRECTORY_SEPARATOR . $filename);

        return 'student-photos/' . $filename;
    }

    public function photoUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path);
    }

    private function readImportRows(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        if ($extension === 'xlsx') {
            return $this->readXlsxRows($path);
        }

        $rows = array_map('str_getcsv', file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        return $this->mapImportRows($rows);
    }

    private function readXlsxRows(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);
            foreach ($xml->si as $si) {
                $sharedStrings[] = (string) ($si->t ?? $si->r->t ?? '');
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $values = [];
        //     foreach ($row->c as $cell) {
        //         $type = (string) $cell['t'];
        //         $value = (string) $cell->v;
        //         $values[] = $type === 's' ? ($sharedStrings[(int) $value] ?? '') : $value;
        //     }
        //     $rows[] = $values;
        // }
            foreach ($row->c as $cell) {
                $cellRef = (string) $cell['r'];
                preg_match('/([A-Z]+)/', $cellRef, $matches);
                $column = $matches[1] ?? 'A';
                $index = 0;

                for ($i = 0; $i < strlen($column); $i++) {
                    $index = $index * 26 + (ord($column[$i]) - 64);
                }

                $index--;
                $type = (string) $cell['t'];
                $value = (string) $cell->v;
                $values[$index] = $type === 's'
                    ? ($sharedStrings[(int) $value] ?? '')
                    : $value;
                }

                ksort($values);

                $maxIndex = max(array_keys($values));
                $filled = [];
                for ($i = 0; $i <= $maxIndex; $i++) {
                    $filled[] = $values[$i] ?? '';
                }

                $rows[] = $filled;
            }
    return $this->mapImportRows($rows);
    }

    private function mapImportRows(array $rows): array
    {
        if (count($rows) < 2) {
            return [];
        }

        $headers = array_map(fn ($header) => Str::of($header)->lower()->replace([' ', '(', ')'], ['_', '', ''])->toString(), array_shift($rows));
        $mapped = [];

        foreach ($rows as $row) {
            $mapped[] = array_combine($headers, array_pad($row, count($headers), ''));
        }

        return $mapped;
    }

    private function buildSimplePdf($students): string
    {
        $lines = ['Student Management Report', 'Generated: ' . now()->format('Y-m-d H:i'), ''];
        $lines[] = 'NIS | Name | Class | Gender';

        foreach ($students as $student) {
            $lines[] = "{$student->nis} | {$student->name} | {$student->class} | {$this->normalizeGender($student->gender)}";
        }

        $content = "BT /F1 11 Tf 40 800 Td 14 TL";
        foreach ($lines as $line) {
            $content .= ' (' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], Str::limit($line, 95, '')) . ') Tj T*';
        }
        $content .= ' ET';

        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj',
            '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
            '5 0 obj << /Length ' . strlen($content) . " >> stream\n{$content}\nendstream endobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    public function normalizeGender(?string $gender): string
    {
        return match ($gender) {
            'Female', 'Perempuan' => 'Female',
            default => 'Male',
        };
    }
}
