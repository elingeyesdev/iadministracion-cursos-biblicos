<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{Miembro, Ministerio, Actividad, Curso, Ingreso, Egreso,
                Contrato, Culto, User, Role};

class DashboardController extends Controller {

    public function index() {
        $user = auth()->user();
        $data = [];

        // ── Versículo del Día ───────────────────────────────
        $versiculosDestacados = [
            ['libro' => 'Filipenses', 'capitulo' => 4, 'versiculo' => 13, 'texto' => 'Todo lo puedo en Cristo que me fortalece.'],
            ['libro' => 'Salmos', 'capitulo' => 23, 'versiculo' => 1, 'texto' => 'Jehová es mi pastor; nada me faltará.'],
            ['libro' => 'Juan', 'capitulo' => 3, 'versiculo' => 16, 'texto' => 'Porque de tal manera amó Dios al mundo, que ha dado a su Hijo unigénito, para que todo aquel que en él cree, no se pierda, mas tenga vida eterna.'],
            ['libro' => 'Josué', 'capitulo' => 1, 'versiculo' => 9, 'texto' => 'Mira que te mando que te esfuerces y seas valiente; no temas ni desmayes, porque Jehová tu Dios estará contigo en dondequiera que vayas.'],
            ['libro' => 'Proverbios', 'capitulo' => 3, 'versiculo' => 5, 'texto' => 'Fíate de Jehová de todo tu corazón, y no te apoyes en tu propia prudencia.'],
            ['libro' => 'Romanos', 'capitulo' => 8, 'versiculo' => 28, 'texto' => 'Y sabemos que a los que aman a Dios, todas las cosas les ayudan a bien, esto es, a los que conforme a su propósito son llamados.'],
            ['libro' => 'Isaías', 'capitulo' => 41, 'versiculo' => 10, 'texto' => 'No temas, porque yo estoy contigo; no desmayes, porque yo soy tu Dios que te esfuerzo; siempre te ayudaré, siempre te sustentaré con la diestra de mi justicia.'],
        ];

        $verseIndex = date('z') % count($versiculosDestacados);
        $selectedDefault = $versiculosDestacados[$verseIndex];
        
        $data['versiculo_dia'] = [
            'referencia' => $selectedDefault['libro'] . ' ' . $selectedDefault['capitulo'] . ':' . $selectedDefault['versiculo'],
            'texto' => $selectedDefault['texto']
        ];

        try {
            $bibliaService = resolve(\App\Services\BibliaDigitalService::class);
            $apiVerse = $bibliaService->getVerse($selectedDefault['libro'], $selectedDefault['capitulo'], $selectedDefault['versiculo']);
            if (isset($apiVerse['data']['verse']['text'])) {
                $data['versiculo_dia']['texto'] = $apiVerse['data']['verse']['text'];
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        // ── BLOQUE TODOS LOS ROLES ──────────────────────────
        $data['total_miembros']   = Miembro::count();
        $data['miembros_activos'] = Miembro::where('estado','activo')->count();
        $data['total_cursos']     = Curso::count();
        $data['total_sesiones']   = \App\Models\Sesion::count();

        // ── BLOQUE SECRETARIO / ADMINISTRADOR ───────────────
        if ($user->tienePermiso('actividades.ver', 'ministerios.ver')) {
            $data['total_ministerios']      = Ministerio::count();
            $data['total_actividades']      = Actividad::count();
            $data['actividades_planificadas']= Actividad::where('estado','planificada')->count();
            $data['actividades_en_curso']   = Actividad::where('estado','en_curso')->count();
            $data['actividades_completadas']= Actividad::where('estado','completada')->count();
            $data['actividades_canceladas'] = Actividad::where('estado','cancelada')->count();
            $data['ultimas_actividades']    = Actividad::with('ministerio')
                                                ->latest()->take(5)->get();
            $data['miembros_recientes']     = Miembro::latest()->take(5)->get();
        }

        // ── BLOQUE TESORERO / ADMINISTRADOR ─────────────────
        if ($user->tienePermiso('ingresos.ver', 'egresos.ver')) {
            $data['total_ingresos']    = Ingreso::sum('monto_total');
            $data['total_egresos']     = Egreso::sum('monto');
            $data['balance']           = $data['total_ingresos'] - $data['total_egresos'];
            $data['contratos_activos'] = Contrato::where('estado','activo')->count();

            // Mes actual
            $data['ingresos_mes'] = Ingreso::whereMonth('fecha', now()->month)
                                        ->whereYear('fecha', now()->year)
                                        ->sum('monto_total');
            $data['egresos_mes']  = Egreso::whereMonth('fecha', now()->month)
                                        ->whereYear('fecha', now()->year)
                                        ->sum('monto');
            $data['balance_mes']  = $data['ingresos_mes'] - $data['egresos_mes'];

            // Top 3 cultos por recaudación
            $data['top_cultos'] = Culto::withSum('ingresos','monto_total')
                                      ->orderByDesc('ingresos_sum_monto_total')
                                      ->take(3)->get();

            // Últimos 5 ingresos
            $data['ultimos_ingresos'] = Ingreso::with('culto')
                                            ->latest()->take(5)->get();

            // Últimos 5 egresos
            $data['ultimos_egresos'] = Egreso::latest()->take(5)->get();
        }

        // ── BLOQUE SOLO ADMINISTRADOR ────────────────────────
        if ($user->tienePermiso('usuarios.ver', 'roles.ver')) {
            $data['total_usuarios'] = User::count();
            $data['total_roles']    = Role::count();
            $data['ultimos_usuarios']= User::with(['roles','profile'])
                                          ->latest()->take(5)->get();
        }

        return view('dashboard', compact('data'));
    }
}
