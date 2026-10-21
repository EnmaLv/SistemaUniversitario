<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Estado;
use Livewire\Attributes\On;

class EstadoIndex extends Component
{
    use WithPagination;

    public $filtroEstado = true;
    public $nombre_esd;
    public $esd_id;
    public $updateMode = false;
    public $search = '';
    public $from;


    protected $rules = [
        'nombre_esd' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->from = request('from');
    }

    public function render()
    {
        $estados = Estado::where('status_id', $this->filtroEstado ? 1 : 2)
            ->when($this->search, function ($query) {
                $query->where('nombre_esd', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre_esd', 'asc')
            ->paginate(10);

        return view('livewire.admin.estado-index', compact('estados'));
    }

    public function buscar()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetInputFields()
    {
        $this->nombre_esd = '';
        $this->esd_id = null;
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        if (Estado::where('nombre_esd', $this->nombre_esd)
            ->where('status_id', 1)
            ->exists()
        ) {

            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Error',
                text: 'Ya existe un estado con ese nombre.'
            );
            return;
        }

        $estado = Estado::create([
            'nombre_esd' => $this->nombre_esd,
            'status_id' => 1,
        ]);

        if ($this->from) {
            return redirect()->to(
                $this->from . '?esd_id=' . $estado->id_esd
            );
        }

        $this->resetInputFields();

        $this->dispatch(
            'swal',
            icon: 'success',
            title: '¡Éxito!',
            text: 'Estado registrado Exitosamente.'
        );
    }

    public function edit($id)
    {
        $estado = Estado::findOrFail($id);
        $this->esd_id = $id;
        $this->nombre_esd = $estado->nombre_esd;
        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        Estado::find($this->esd_id)
            ->update(['nombre_esd' => $this->nombre_esd]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Actualizado',
            text: 'Estado actualizado Exitosamente.'
        );
    }

    public function confirmDestroy($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function confirmActivate($id)
    {
        $this->dispatch('confirm-activate', id: $id);
    }

    #[On('destroy-estado')]
    public function destroy($id)
    {
        Estado::findOrFail($id)->update([
            'status_id' => 2
        ]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Eliminado',
            text: 'Estado eliminado Exitosamente.'
        );
    }

    #[On('activate-estado')]
    public function activate($id)
    {
        Estado::findOrFail($id)->update([
            'status_id' => 1
        ]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Activado',
            text: 'Estado reactivado exitosamente.'
        );
    }
}
