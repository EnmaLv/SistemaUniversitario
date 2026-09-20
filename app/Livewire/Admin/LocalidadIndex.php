<?php

namespace App\Livewire\Admin;

use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Localidad;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class LocalidadIndex extends Component
{
    use WithPagination;

    public $filtroEstado = true;
    public $nombre_localidad;
    public $estado_id;
    public $municipio_id;
    public $localidad_id;
    public $updateMode = false;
    public $search = '';
    public $from;
    public $municipios = [];

    protected $rules = [
        'nombre_localidad' => 'required|string|max:255',
        'estado_id' => 'required|integer|exists:estados,id',
        'municipio_id' => 'required|integer|exists:municipios,id',
    ];

    public function mount()
    {
        $this->from = request('from');
    }

    // 🔥 Resetea la página automáticamente al escribir en el buscador
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $estados = Estado::where('status', true)->get();

        $localidades = Localidad::where('status', $this->filtroEstado)
            ->when($this->search, function ($query) {
                // Encapsulamos las búsquedas relacionales para que no rompan el filtro de status superior
                $query->where(function ($q) {
                    $q->where('nombre_localidad', 'like', '%' . $this->search . '%')
                      ->orWhereHas('municipio', function ($subQ) {
                          $subQ->where('nombre_municipio', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('municipio.estado', function ($subQ) {
                          $subQ->where('nombre_estado', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('nombre_localidad', 'asc')
            ->paginate(10);

        return view('livewire.admin.localidad-index', compact('estados', 'localidades'));
    }

    public function buscar()
    {
        $this->resetPage();
    }

    public function updatedEstadoId($estado_id)
    {
        $this->municipios = Municipio::where('estado_id', $estado_id)
            ->where('status', true)
            ->orderBy('nombre_municipio', 'asc')
            ->get();

        $this->municipio_id = null;
    }

    public function resetInputFields()
    {
        $this->nombre_localidad = '';
        $this->estado_id = null;
        $this->municipio_id = null;
        $this->localidad_id = null;
        $this->municipios = [];
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        if (Localidad::where('nombre_localidad', $this->nombre_localidad)->where('status', true)->exists()) {
            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Error',
                text: 'Ya existe una localidad con ese nombre.'
            );
            return;
        }

        $localidad = Localidad::create([
            'nombre_localidad' => $this->nombre_localidad,
            'municipio_id' => $this->municipio_id,
            'status' => true,
        ]);

        if ($this->from) {
            return redirect()->to(
                $this->from . '?localidad_id=' . $localidad->id
            );
        }

        $this->resetInputFields();

        $this->dispatch(
            'swal',
            icon: 'success',
            title: '¡Éxito!',
            text: 'Localidad registrada Exitosamente.'
        );
    }

    public function edit($id)
    {
        $localidad = Localidad::with('municipio.estado')->findOrFail($id);

        $this->localidad_id = $localidad->id;
        $this->nombre_localidad = $localidad->nombre_localidad;
        $this->estado_id = $localidad->municipio->estado->id ?? null;
        $this->municipio_id = $localidad->municipio_id;

        if ($this->estado_id) {
            $this->municipios = Municipio::where('estado_id', $this->estado_id)
                ->where('status', true)
                ->orderBy('nombre_municipio', 'asc')
                ->get();
        }

        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        $localidad = Localidad::findOrFail($this->localidad_id);
        $localidad->update([
            'nombre_localidad' => $this->nombre_localidad,
            'municipio_id' => $this->municipio_id,
        ]);

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

    #[On('destroy-localidad')]
    public function destroy($id)
    {
        Localidad::findOrFail($id)->update([
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