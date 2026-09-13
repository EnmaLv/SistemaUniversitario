<div>
    <div class="form-group" style="text-align: right;">
        <button wire:click="guardar" class="rd-btn rd-btn-success">
            <i class="fas fa-save"></i> Guardar Fechas de Vencimiento
        </button>
    </div>
    <table class="table table-bordered mt-3">
        
        <thead>
            <tr>
                <th>Producto</th>
                <th>Fecha de Vencimiento</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($compra->detalleCompras as $detalle)
                <tr>
                    <td>{{ $detalle->producto->nombre }}</td>
                    <td>
                        <input type="date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/20"
                            wire:model="fechas.{{ $detalle->id }}.fecha_vencimiento"
                            min="{{ now()->addDay()->format('Y-m-d') }}">
                        @error("fechas.$detalle->id.fecha_vencimiento")
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
