<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Estado;
use App\Models\Municipio;
use Livewire\Attributes\On;

class MunicipioIndex extends Component
{
    use WithPagination;

    public $filtroEstado = true;
    public $nombre_municipio;
    public $municipio_id;
    public $estado_id;
    public $updateMode = false;
    public $search = '';
    public $from;

    public function mount()
    {
        $this->from = request('from');
    }

    protected $rules = [
        'nombre_municipio' => 'required|string|max:255',
        'estado_id' => 'required|integer|exists:estados,id',
    ];

    // 🔥 Resetea la página automáticamente al escribir en el buscador live
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $estados = Estado::where('status', true)->get();

        $municipios = Municipio::where('status', $this->filtroEstado)
            ->when($this->search, function ($query) {
                // Encapsulamos el buscador en un sub-bloque para que el OR no rompa el filtro de status
                $query->where(function ($q) {
                    $q->where('nombre_municipio', 'like', '%' . $this->search . '%')
                      ->orWhereHas('estado', function ($subQ) {
                          $subQ->where('nombre_estado', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('nombre_municipio', 'asc')
            ->paginate(10);

        return view('livewire.admin.municipio-index', compact('municipios', 'estados'));
    }

    public function buscar()
    {
        $this->resetPage();
    }

    public function resetInputFields()
    {
        $this->nombre_municipio = '';
        $this->municipio_id = null;
        $this->estado_id = null;
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        if (Municipio::where('nombre_municipio', $this->nombre_municipio)
            ->where('estado_id', $this->estado_id)
            ->where('status', true)->exists()
        ) {
            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Error',
                text: 'Ya existe un municipio con ese nombre.'
            );
            return;
        }

        $municipio = Municipio::create([
            'nombre_municipio' => $this->nombre_municipio,
            'estado_id' => $this->estado_id,
            'status' => true,
        ]);

        if ($this->from) {
            return redirect()->to(
                $this->from . '?municipio_id=' . $municipio->id
            );
        }

        $this->resetInputFields();

        $this->dispatch(
            'swal',
            icon: 'success',
            title: '¡Éxito!',
            text: 'Municipio registrado Exitosamente.'
        );
    }

    public function edit($id)
    {
        $municipio = Municipio::findOrFail($id);
        $this->municipio_id = $id;
        $this->nombre_municipio = $municipio->nombre_municipio;
        $this->estado_id = $municipio->estado_id;
        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        $municipio = Municipio::find($this->municipio_id);
        $municipio->update(['nombre_municipio' => $this->nombre_municipio, 'estado_id' => $this->estado_id]);

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

    #[On('destroy-municipio')]
    public function destroy($id)
    {
        Municipio::findOrFail($id)->update([
            'status' => false
        ]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Eliminado',
            text: 'Estado eliminado Exitosamente.'
        );
    }
}