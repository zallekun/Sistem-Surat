<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use App\Models\Prodi;
use App\Models\Fakultas;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'nama';
    public $sortDirection = 'asc';
    public $filterRole = '';
    public $filterProdi = '';
    public $filterFakultas = '';

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function exportUsers()
    {
        $query = User::query()
            ->when($this->search, function ($query) {
                $query->where('nama', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterRole, function ($query) {
                $query->whereHas('role', fn($q) => $q->where('id', $this->filterRole));
            })
            ->when($this->filterProdi, function ($query) {
                $query->whereHas('prodi', fn($q) => $q->where('id', $this->filterProdi));
            })
            ->when($this->filterFakultas, function ($query) {
                $query->whereHas('prodi.fakultas', fn($q) => $q->where('id', $this->filterFakultas));
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return (new \App\Exports\UsersExport($query))->download('users.xlsx');
    }

    public function render()
    {
        $users = User::with('role', 'jabatan', 'prodi')
            ->when($this->search, function ($query) {
                $query->where('nama', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterRole, function ($query) {
                $query->whereHas('role', fn($q) => $q->where('id', $this->filterRole));
            })
            ->when($this->filterProdi, function ($query) {
                $query->whereHas('prodi', fn($q) => $q->where('id', $this->filterProdi));
            })
            ->when($this->filterFakultas, function ($query) {
                $query->whereHas('prodi.fakultas', fn($q) => $q->where('id', $this->filterFakultas));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.users-table', [
            'users' => $users,
            'roles' => Role::all(),
            'prodis' => Prodi::all(),
            'fakultas' => Fakultas::all(),
        ]);
    }
}
