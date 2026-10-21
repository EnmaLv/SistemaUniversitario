<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Persona;

class BuscadorEstudiante extends Component
{
    public string $search = '';
    public ?int $selectedId = null;
    public string $selectedLabel = '';

    public function mount($selectedId = null)
    {
        if ($selectedId) {
            $this->pickStudent((int) $selectedId);
        }
    }

    public function pickStudent(int $id)
    {
        $student = Persona::find($id);
        if ($student) {
            $this->selectedId = $student->per_id;
            $this->selectedLabel = $student->nombres_per . ' ' . $student->apellidos_per . ' — C.I. ' . $student->cedula_per;
            $this->search = '';
        }
    }

    public function clearStudent()
    {
        $this->selectedId = null;
        $this->selectedLabel = '';
        $this->search = '';
    }

    public function getEstudiantesProperty()
    {
        $query = Persona::where('id_perfil', 2);

        if (strlen($this->search) >= 1) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('cedula_per', 'like', "%{$s}%")
                  ->orWhere('nombres_per', 'like', "%{$s}%")
                  ->orWhere('apellidos_per', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('nombres_per')->limit(15)->get();
    }

    public function render()
    {
        return view('livewire.buscador-estudiante', [
            'estudiantes' => $this->estudiantes,
        ]);
    }
}
