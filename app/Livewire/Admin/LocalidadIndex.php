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
    public $nombre_par;
    public $esd_id;
    public $mun_id;
    public $localidad_id;
    public $updateMode = false;
    public $search = '';
    public $from;
    public $municipios = [];

    protected $rules = [
        'nombre_par' => 'required|string|max:255',
        'esd_id' => 'required|integer|exists:estado,id_esd',
        'mun_id' => 'required|integer|exists:municipio,id_mun',
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
        $estados = Estado::where('status_id', 1)->get();

        $localidades = Localidad::where('status_id', $this->filtroEstado ? 1 : 2)
            ->when($this->search, function ($query) {
                // Encapsulamos las búsquedas relacionales para que no rompan el filtro de status superior
                $query->where(function ($q) {
                    $q->where('nombre_par', 'like', '%' . $this->search . '%')
                      ->orWhereHas('municipio', function ($subQ) {
                          $subQ->where('nombre_mun', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('municipio.estado', function ($subQ) {
                          $subQ->where('nombre_esd', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('nombre_par', 'asc')
            ->paginate(10);

        return view('livewire.admin.localidad-index', compact('estados', 'localidades'));
    }

    public function buscar()
    {
        $this->resetPage();
    }

    public function updatedEsdId($esd_id)
    {
        $this->municipios = Municipio::where('esd_id', $esd_id)
            ->where('status_id', 1)
            ->orderBy('nombre_mun', 'asc')
            ->get();

        $this->mun_id = null;
    }

    public function resetInputFields()
    {
        $this->nombre_par = '';
        $this->esd_id = null;
        $this->mun_id = null;
        $this->localidad_id = null;
        $this->municipios = [];
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        if (Localidad::where('nombre_par', $this->nombre_par)->where('status_id', 1)->exists()) {
            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Error',
                text: 'Ya existe una localidad con ese nombre.'
            );
            return;
        }

        $localidad = Localidad::create([
            'nombre_par' => $this->nombre_par,
            'mun_id' => $this->mun_id,
            'status_id' => 1,
        ]);

        if ($this->from) {
            return redirect()->to(
                $this->from . '?localidad_id=' . $localidad->id_par
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

        $this->localidad_id = $localidad->id_par;
        $this->nombre_par = $localidad->nombre_par;
        $this->esd_id = $localidad->municipio->estado->id_esd ?? null;
        $this->mun_id = $localidad->mun_id;

        if ($this->esd_id) {
            $this->municipios = Municipio::where('esd_id', $this->esd_id)
                ->where('status_id', 1)
                ->orderBy('nombre_mun', 'asc')
                ->get();
        }

        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        $localidad = Localidad::findOrFail($this->localidad_id);
        $localidad->update([
            'nombre_par' => $this->nombre_par,
            'mun_id' => $this->mun_id,
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

    public function confirmActivate($id)
    {
        $this->dispatch('confirm-activate', id: $id);
    }

    #[On('destroy-localidad')]
    public function destroy($id)
    {
        Localidad::findOrFail($id)->update([
            'status_id' => 2
        ]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Eliminado',
            text: 'Estado eliminado Exitosamente.'
        );
    }

    #[On('activate-localidad')]
    public function activate($id)
    {
        Localidad::findOrFail($id)->update([
            'status_id' => 1
        ]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Activada',
            text: 'Localidad reactivada exitosamente.'
        );
    }
}