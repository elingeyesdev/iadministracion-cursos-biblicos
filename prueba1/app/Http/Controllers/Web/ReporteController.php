<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asistencia;
use App\Models\Curso;
use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\Miembro;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        return view('reportes.index');
    }

    public function miembros(Request $request)
    {
        $estado = $request->input('estado', '');
        $desde = $request->input('desde', '');
        $hasta = $request->input('hasta', '');

        $query = Miembro::query();

        if ($estado !== '') {
            $query->where('estado', $estado);
        }
        if ($desde) {
            $query->whereDate('fecha_ingreso', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('fecha_ingreso', '<=', $hasta);
        }

        $miembros = $query->orderBy('nombre')->get();

        $totales = [
            'total' => $miembros->count(),
            'activos' => $miembros->where('estado', 'activo')->count(),
            'inactivos' => $miembros->where('estado', 'inactivo')->count(),
        ];

        return view('reportes.miembros', compact('miembros', 'estado', 'desde', 'hasta', 'totales'));
    }

    public function asistencia(Request $request)
    {
        $cursoId = $request->input('curso_id', '');
        $desde = $request->input('desde', '');
        $hasta = $request->input('hasta', '');

        $cursos = Curso::orderBy('nombre')->get();

        $query = Asistencia::with(['miembro', 'sesion.curso']);

        if ($cursoId) {
            $query->whereHas('sesion', fn ($q) => $q->where('id_curso', $cursoId));
        }
        if ($desde) {
            $query->whereHas('sesion', fn ($q) => $q->whereDate('fecha', '>=', $desde));
        }
        if ($hasta) {
            $query->whereHas('sesion', fn ($q) => $q->whereDate('fecha', '<=', $hasta));
        }

        $registros = $query->orderByDesc('sesion_id')->get();

        $resumen = [
            'presentes' => $registros->where('estado', 'Presente')->count(),
            'ausentes' => $registros->where('estado', 'Ausente')->count(),
            'tardanzas' => $registros->where('estado', 'Tardanza')->count(),
            'justificados' => $registros->where('estado', 'Justificado')->count(),
        ];

        return view('reportes.asistencia', compact('registros', 'cursos', 'cursoId', 'desde', 'hasta', 'resumen'));
    }

    public function financiero(Request $request)
    {
        $desde = $request->input('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->input('hasta', now()->endOfMonth()->toDateString());

        $ingresos = Ingreso::whereBetween('fecha', [$desde, $hasta])->get();
        $egresos = Egreso::whereBetween('fecha', [$desde, $hasta])->get();

        $totalIngresos = $ingresos->sum('monto_total');
        $totalEgresos = $egresos->sum('monto');
        $balance = $totalIngresos - $totalEgresos;

        $ingresosPorTipo = $ingresos->groupBy('tipo')->map(fn ($g) => $g->sum('monto_total'));
        $egresosPorTipo = $egresos->groupBy('tipo_egreso')->map(fn ($g) => $g->sum('monto'));

        return view('reportes.financiero', compact(
            'ingresos',
            'egresos',
            'desde',
            'hasta',
            'totalIngresos',
            'totalEgresos',
            'balance',
            'ingresosPorTipo',
            'egresosPorTipo'
        ));
    }
}