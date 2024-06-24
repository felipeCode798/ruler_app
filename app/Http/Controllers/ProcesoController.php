<?php

namespace App\Http\Controllers;

use App\Models\Proceso;
use App\Models\RegistrarProceso;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcesoController extends Controller
{

    public function descargarPDF($id)
    {
        $proceso = Proceso::with(['client', 'registrarProcesos', 'pagos'])->findOrFail($id);
        $totalPagado = $proceso->total_pagado;

        $data = [
            'proceso' => $proceso,
            'total_pagado' => $totalPagado
        ];

        $pdf = PDF::loadView('procesos.pdf', $data);

        return $pdf->download('recibo_caja_' . $proceso->id . '.pdf');
    }

    public function downloadComparendoDocument($procesoId, $comparendoId)
    {
        $proceso = Proceso::findOrFail($procesoId);
        $comparendo = RegistrarProceso::findOrFail($comparendoId);

        if ($comparendo->proceso_id != $proceso->id) {
            abort(404);
        }

        $documentoPath = $comparendo->documento_dni;

        return Storage::download($documentoPath);
    }

}
