<?php

namespace App\Livewire;

use App\Models\RoleUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use ZipArchive;

class RoleManagement extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $sortField = 'username';
    public string $sortDirection = 'asc';
    public string $perPage = '10';
    public bool $showFormModal = false;
    public bool $showDetailModal = false;
    public ?int $editId = null;
    public ?RoleUser $selectedRoleUser = null;
    public $importFile;
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'Admin';

    public array $roleOptions = ['Super Admin', 'Admin', 'Wali Kelas'];

    protected array $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'username'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => '10'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['username', 'email', 'role'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $roleUser = RoleUser::findOrFail($id);
        $this->editId = $roleUser->id;
        $this->username = $roleUser->username;
        $this->email = $roleUser->email;
        $this->password = '';
        $this->role = $roleUser->role;
        $this->resetValidation();
        $this->showFormModal = true;
    }

    public function showDetail(int $id): void
    {
        $this->selectedRoleUser = RoleUser::findOrFail($id);
        $this->showDetailModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('role_users', 'username')->ignore($this->editId)],
            'email' => ['required', 'email', 'max:150', Rule::unique('role_users', 'email')->ignore($this->editId)],
            'password' => [$this->editId ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', Rule::in($this->roleOptions)],
        ]);

        if ($validated['password'] !== '') {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        RoleUser::updateOrCreate(['id' => $this->editId], $validated);

        $this->showFormModal = false;
        $this->resetForm();
        $this->dispatch('notify', type: 'success', message: 'Role saved successfully.');
    }

    public function delete(int $id): void
    {
        RoleUser::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Role deleted successfully.');
    }

    public function importExcel(): void
    {
        $this->validate(['importFile' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:5120']]);

        $rows = $this->readImportRows($this->importFile->getRealPath(), $this->importFile->getClientOriginalExtension());
        $headers = array_map(fn ($header) => Str::of($header)->lower()->replace(' ', '_')->toString(), array_shift($rows) ?? []);
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $data = array_combine($headers, array_pad($row, count($headers), ''));
            $role = trim($data['role'] ?? '');

            if (! in_array($role, $this->roleOptions, true) || trim($data['username'] ?? '') === '' || trim($data['email'] ?? '') === '' || trim($data['password'] ?? '') === '') {
                $skipped++;
                continue;
            }

            RoleUser::updateOrCreate(
                ['username' => trim($data['username'])],
                [
                    'email' => trim($data['email']),
                    'password' => Hash::make(trim($data['password'])),
                    'role' => $role,
                ]
            );
            $imported++;
        }

        $this->importFile = null;
        $this->dispatch('notify', type: 'success', message: "Import completed. {$imported} imported, {$skipped} skipped.");
    }

    public function exportExcel()
    {
        $roles = $this->filteredQuery()->get();

        return response()->streamDownload(function () use ($roles) {
            echo '<table border="1"><tr><th>Username</th><th>Email</th><th>Role</th></tr>';
            foreach ($roles as $role) {
                echo '<tr><td>' . e($role->username) . '</td><td>' . e($role->email) . '</td><td>' . e($role->role) . '</td></tr>';
            }
            echo '</table>';
        }, 'roles-' . now()->format('Y-m-d-His') . '.xls', ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function exportPdf()
    {
        $roles = $this->filteredQuery()->get();

        return response()->streamDownload(function () use ($roles) {
            echo $this->buildSimplePdf($roles);
        }, 'roles-' . now()->format('Y-m-d-His') . '.pdf', ['Content-Type' => 'application/pdf']);
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->role = 'Admin';
        $this->resetValidation();
    }

    public function filteredQuery()
    {
        return RoleUser::query()
            ->when($this->search !== '', function ($query) {
                $search = '%' . trim($this->search) . '%';
                $query->where('username', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('role', 'like', $search);
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    private function readImportRows(string $path, string $extension): array
    {
        if (strtolower($extension) !== 'xlsx') {
            return array_map('str_getcsv', file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        }

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
            foreach ($row->c as $cell) {
                $type = (string) $cell['t'];
                $value = (string) $cell->v;
                $values[] = $type === 's' ? ($sharedStrings[(int) $value] ?? '') : $value;
            }
            $rows[] = $values;
        }

        return $rows;
    }

    private function buildSimplePdf($roles): string
    {
        $lines = ['Role Management Report', 'Generated: ' . now()->format('Y-m-d H:i'), '', 'Username | Email | Role'];

        foreach ($roles as $role) {
            $lines[] = "{$role->username} | {$role->email} | {$role->role}";
        }

        $content = "BT /F1 10 Tf 36 800 Td 13 TL";
        foreach ($lines as $line) {
            $content .= ' (' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], Str::limit($line, 100, '')) . ') Tj T*';
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

    public function render()
    {
        $query = $this->filteredQuery();
        $roles = $this->perPage === 'all'
            ? $query->get()
            : $query->paginate((int) $this->perPage);

        return view('livewire.role-management', [
            'roles' => $roles,
            'totalSuperAdmin' => RoleUser::where('role', 'Super Admin')->count(),
            'totalAdmin' => RoleUser::where('role', 'Admin')->count(),
            'totalWaliKelas' => RoleUser::where('role', 'Wali Kelas')->count(),
        ])->layout('layouts.app');
    }
}
