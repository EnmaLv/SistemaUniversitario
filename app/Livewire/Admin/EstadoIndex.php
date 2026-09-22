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
    public $nombre_estado;
    public $estado_id;
    public $updateMode = false;
    public $search = '';
    public $from;


    protected $rules = [
        'nombre_estado' => 'required|string|max:255',
    ];

    public function mount()
    {
        $this->from = request('from');
    }

    public function render()
    {
        $estados = Estado::where('status', $this->filtroEstado)
            ->when($this->search, function ($query) {
                $query->where('nombre_estado', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre_estado', 'asc')
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
        $this->nombre_estado = '';
        $this->estado_id = null;
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        if (Estado::where('nombre_estado', $this->nombre_estado)
            ->where('status', true)
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
            'nombre_estado' => $this->nombre_estado,
            'status' => true,
        ]);

        if ($this->from) {
            return redirect()->to(
                $this->from . '?estado_id=' . $estado->id
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
        $this->estado_id = $id;
        $this->nombre_estado = $estado->nombre_estado;
        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        Estado::find($this->estado_id)
            ->update(['nombre_estado' => $this->nombre_estado]);

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
            'status' => false
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
            'status' => true
        ]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Activado',
            text: 'Estado reactivado exitosamente.'
        );
    }
}
