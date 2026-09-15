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
            $this->selectedId = $student->id_persona;
            $this->selectedLabel = $student->nombre_persona . ' ' . $student->apellido_persona . ' — C.I. ' . $student->cedula_persona;
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
                $q->where('cedula_persona', 'like', "%{$s}%")
                  ->orWhere('nombre_persona', 'like', "%{$s}%")
                  ->orWhere('apellido_persona', 'like', "%{$s}%");
            });
        }

        return $query->orderBy('nombre_persona')->limit(15)->get();
    }

    public function render()
    {
        return view('livewire.buscador-estudiante', [
            'estudiantes' => $this->estudiantes,
        ]);
    }
}
