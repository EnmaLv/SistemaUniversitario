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
    public $nombre_mun;
    public $mun_id;
    public $esd_id;
    public $updateMode = false;
    public $search = '';
    public $from;

    public function mount()
    {
        $this->from = request('from');
    }

    protected $rules = [
        'nombre_mun' => 'required|string|max:255',
        'esd_id' => 'required|integer|exists:estado,id_esd',
    ];

    // 🔥 Resetea la página automáticamente al escribir en el buscador live
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $estados = Estado::where('status_id', 1)->get();

        $municipios = Municipio::where('status_id', $this->filtroEstado ? 1 : 2)
            ->when($this->search, function ($query) {
                // Encapsulamos el buscador en un sub-bloque para que el OR no rompa el filtro de status
                $query->where(function ($q) {
                    $q->where('nombre_mun', 'like', '%' . $this->search . '%')
                      ->orWhereHas('estado', function ($subQ) {
                          $subQ->where('nombre_esd', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy('nombre_mun', 'asc')
            ->paginate(10);

        return view('livewire.admin.municipio-index', compact('municipios', 'estados'));
    }

    public function buscar()
    {
        $this->resetPage();
    }

    public function resetInputFields()
    {
        $this->nombre_mun = '';
        $this->mun_id = null;
        $this->esd_id = null;
        $this->updateMode = false;
    }

    public function store()
    {
        $this->validate();

        if (Municipio::where('nombre_mun', $this->nombre_mun)
            ->where('esd_id', $this->esd_id)
            ->where('status_id', 1)->exists()
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
            'nombre_mun' => $this->nombre_mun,
            'esd_id' => $this->esd_id,
            'status_id' => 1,
        ]);

        if ($this->from) {
            return redirect()->to(
                $this->from . '?mun_id=' . $municipio->id_mun
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
        $this->mun_id = $id;
        $this->nombre_mun = $municipio->nombre_mun;
        $this->esd_id = $municipio->esd_id;
        $this->updateMode = true;
    }

    public function update()
    {
        $this->validate();

        $municipio = Municipio::find($this->mun_id);
        $municipio->update(['nombre_mun' => $this->nombre_mun, 'esd_id' => $this->esd_id]);

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

    #[On('destroy-municipio')]
    public function destroy($id)
    {
        Municipio::findOrFail($id)->update([
            'status_id' => 2
        ]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Eliminado',
            text: 'Estado eliminado Exitosamente.'
        );
    }

    #[On('activate-municipio')]
    public function activate($id)
    {
        Municipio::findOrFail($id)->update([
            'status_id' => 1
        ]);

        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Activado',
            text: 'Municipio reactivado exitosamente.'
        );
    }
}