@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- ── ENCABEZADO ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-white mb-0">Dashboard</h3>
            <p class="text-muted mb-0">
                {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            </p>
        </div>
        <div>
            <span class="fw-semibold text-white me-2">{{ auth()->user()->name }}</span>
            @foreach(auth()->user()->roles as $rol)
                @php
                    $color = match($rol->nombre) {
                        'Administrador' => 'danger',
                        'Secretario'    => 'primary',
                        'Tesorero'      => 'warning',
                        default         => 'secondary'
                    };
                @endphp
                <span class="badge bg-{{ $color }} text-white ms-1">{{ $rol->nombre }}</span>
            @endforeach
        </div>
    </div>

    {{-- ── Versículo del Día ── --}}
    @if(isset($data['versiculo_dia']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm verse-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-2 verse-card-label">
                        <i class="bi bi-book-half"></i>
                        <span class="text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.1em;">Versículo del Día</span>
                    </div>
                    <blockquote class="blockquote mb-0">
                        <p class="mb-2 fs-5">“{{ $data['versiculo_dia']['texto'] }}”</p>
                        <footer class="blockquote-footer fs-6 mt-1">{{ $data['versiculo_dia']['referencia'] }}</footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════ --}}
    {{-- BLOQUE 1: TODOS LOS ROLES                     --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Total Miembros --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-white">{{ $data['total_miembros'] }}</div>
                        <div class="text-muted small">Total Miembros</div>
                        <div class="text-success small">
                            <i class="bi bi-check-circle"></i>
                            {{ $data['miembros_activos'] }} activos
                        </div>
                    </div>
                </div>
                {{-- Barra progreso activos/total --}}
                <div class="card-footer bg-transparent pt-0 border-0">
                    @php $pct = $data['total_miembros'] > 0
                        ? round(($data['miembros_activos']/$data['total_miembros'])*100) : 0 @endphp
                    <div class="progress bg-dark" style="height:4px">
                        <div class="progress-bar bg-primary" style="width:{{ $pct }}%"></div>
                    </div>
                    <small class="text-muted">{{ $pct }}% activos</small>
                </div>
            </div>
        </div>

        {{-- Miembros Activos --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="bi bi-person-check-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-white">{{ $data['miembros_activos'] }}</div>
                        <div class="text-muted small">Miembros Activos</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Cursos --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="bi bi-book-fill text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-white">{{ $data['total_cursos'] }}</div>
                        <div class="text-muted small">Cursos</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Sesiones --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-calendar3 text-warning fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-white">{{ $data['total_sesiones'] }}</div>
                        <div class="text-muted small">Sesiones</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- BLOQUE 2: SECRETARIO / ADMINISTRADOR          --}}
    {{-- ══════════════════════════════════════════════ --}}
    @if(auth()->user()->tieneRol('Administrador') || auth()->user()->tieneRol('Secretario'))

    {{-- Cards de Actividades --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h6 class="text-muted text-uppercase fw-bold mb-2">
                <i class="bi bi-building"></i> Organización
            </h6>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center">
                    <i class="bi bi-building text-secondary fs-3"></i>
                    <div class="fs-2 fw-bold mt-1 text-white">{{ $data['total_ministerios'] }}</div>
                    <div class="text-muted small">Ministerios</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event text-primary fs-3"></i>
                    <div class="fs-2 fw-bold mt-1 text-white">{{ $data['total_actividades'] }}</div>
                    <div class="text-muted small">Actividades</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    <div class="fs-2 fw-bold mt-1 text-warning">
                        {{ $data['actividades_en_curso'] }}
                    </div>
                    <div class="text-muted small">En Curso</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center">
                    <i class="bi bi-clipboard-check text-success fs-3"></i>
                    <div class="fs-2 fw-bold mt-1 text-success">
                        {{ $data['actividades_completadas'] }}
                    </div>
                    <div class="text-muted small">Completadas</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tablas Secretario --}}
    <div class="row g-3 mb-4">

        {{-- Últimas Actividades --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header bg-dark border-0 fw-semibold text-white">
                    <i class="bi bi-calendar-event text-primary"></i>
                    Últimas Actividades
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Ministerio</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($data['ultimas_actividades'] as $act)
                            <tr>
                                <td class="text-white fw-bold">{{ $act->nombre }}</td>
                                <td>{{ $act->ministerio->nombre ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($act->fecha)->format('d/m/Y') }}</td>
                                <td>
                                    @php
                                        $badge = match($act->estado) {
                                            'planificada' => 'secondary',
                                            'en_curso'    => 'warning',
                                            'completada'  => 'success',
                                            'cancelada'   => 'danger',
                                            default       => 'light'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">
                                        {{ ucfirst($act->estado) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer border-0 bg-transparent text-end">
                    <a href="{{ route('actividades.index') }}" class="btn btn-sm btn-outline-primary">
                        Ver todas <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Miembros Recientes --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header bg-dark border-0 fw-semibold text-white">
                    <i class="bi bi-person-plus text-success"></i>
                    Miembros Recientes
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                    @foreach($data['miembros_recientes'] as $m)
                        <li class="list-group-item d-flex align-items-center gap-2 bg-transparent text-white border-secondary">
                            <div class="rounded-circle bg-primary text-white d-flex
                                align-items-center justify-content-center fw-bold"
                                style="width:36px;height:36px;font-size:14px">
                                {{ strtoupper(substr($m->nombre,0,1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold small">{{ $m->nombre }}</div>
                                <div class="text-muted" style="font-size:11px">
                                    {{ $m->email }}
                                </div>
                            </div>
                            <span class="badge ms-auto
                                {{ $m->estado === 'activo' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $m->estado }}
                            </span>
                        </li>
                    @endforeach
                    </ul>
                </div>
                <div class="card-footer border-0 bg-transparent text-end">
                    <a href="{{ route('miembros.index') }}" class="btn btn-sm btn-outline-success">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
    @endif

    {{-- ══════════════════════════════════════════════ --}}
    {{-- BLOQUE 3: TESORERO / ADMINISTRADOR            --}}
    {{-- ══════════════════════════════════════════════ --}}
    @if(auth()->user()->tieneRol('Administrador') || auth()->user()->tieneRol('Tesorero'))

    <div class="col-12 mb-2 mt-4">
        <h6 class="text-muted text-uppercase fw-bold">
            <i class="bi bi-cash-coin"></i> Finanzas
        </h6>
    </div>

    {{-- Cards financieras generales --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100" style="border: 1px solid rgba(25, 135, 84, 0.2) !important;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-up-circle-fill text-success fs-3"></i>
                    <div class="fs-4 fw-bold text-success mt-1">
                        Bs. {{ number_format($data['total_ingresos'],2) }}
                    </div>
                    <div class="text-muted small">Total Ingresos</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 h-100" style="border: 1px solid rgba(220, 53, 69, 0.2) !important;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-arrow-down-circle-fill text-danger fs-3"></i>
                    <div class="fs-4 fw-bold text-danger mt-1">
                        Bs. {{ number_format($data['total_egresos'],2) }}
                    </div>
                    <div class="text-muted small">Total Egresos</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            @php $balanceColor = $data['balance'] >= 0 ? 'success' : 'danger'; @endphp
            <div class="card border-0 shadow-sm bg-{{ $balanceColor }} bg-opacity-10 h-100" style="border: 1px solid rgba({{ $balanceColor === 'success' ? '25, 135, 84' : '220, 53, 69' }}, 0.2) !important;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-bank text-{{ $balanceColor }} fs-3"></i>
                    <div class="fs-4 fw-bold text-{{ $balanceColor }} mt-1">
                        Bs. {{ number_format($data['balance'],2) }}
                    </div>
                    <div class="text-muted small">Balance General</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100" style="border: 1px solid rgba(13, 202, 240, 0.2) !important;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-file-earmark-text text-info fs-3"></i>
                    <div class="fs-4 fw-bold text-info mt-1">
                        {{ $data['contratos_activos'] }}
                    </div>
                    <div class="text-muted small">Contratos Activos</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Cards del mes actual --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">
                        Ingresos — {{ now()->isoFormat('MMMM YYYY') }}
                    </div>
                    <div class="fs-4 fw-bold text-success">
                        Bs. {{ number_format($data['ingresos_mes'],2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">
                        Egresos — {{ now()->isoFormat('MMMM YYYY') }}
                    </div>
                    <div class="fs-4 fw-bold text-danger">
                        Bs. {{ number_format($data['egresos_mes'],2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @php $bMesColor = $data['balance_mes'] >= 0 ? 'success' : 'danger'; @endphp
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">
                        Balance — {{ now()->isoFormat('MMMM YYYY') }}
                    </div>
                    <div class="fs-4 fw-bold text-{{ $bMesColor }}">
                        Bs. {{ number_format($data['balance_mes'],2) }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Top Cultos + Últimos Ingresos y Egresos --}}
    <div class="row g-3 mb-4">

        {{-- Top 3 Cultos --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header bg-dark border-0 fw-semibold text-white">
                    <i class="bi bi-trophy-fill text-warning"></i>
                    Top Cultos por Recaudación
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                    @forelse($data['top_cultos'] as $i => $culto)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-secondary text-white">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill
                                    {{ $i===0 ? 'bg-warning text-dark' :
                                       ($i===1 ? 'bg-secondary text-white' : 'bg-danger bg-opacity-25 text-danger') }}">
                                    #{{ $i+1 }}
                                </span>
                                <div>
                                    <div class="fw-semibold small text-white">{{ $culto->nombre }}</div>
                                    <div class="text-muted" style="font-size:11px">
                                        {{ ucfirst($culto->dia_semana) }}
                                        — {{ \Carbon\Carbon::parse($culto->hora)->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                            <span class="text-success fw-bold small">
                                Bs. {{ number_format($culto->ingresos_sum_monto_total ?? 0, 2) }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted bg-transparent border-secondary py-4">
                            Sin datos aún
                        </li>
                    @endforelse
                    </ul>
                </div>
                <div class="card-footer border-0 bg-transparent text-end">
                    <a href="{{ route('cultos.index') }}" class="btn btn-sm btn-outline-warning">
                        Ver cultos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Últimos Ingresos --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header bg-dark border-0 fw-semibold text-white">
                    <i class="bi bi-cash text-success"></i>
                    Últimos Ingresos
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                    @forelse($data['ultimos_ingresos'] as $of)
                        <li class="list-group-item d-flex justify-content-between bg-transparent border-secondary text-white">
                            <div>
                                <div class="small fw-semibold text-white">
                                    {{ $of->culto->nombre ?? '-' }}
                                </div>
                                <div class="text-muted" style="font-size:11px">
                                    {{ \Carbon\Carbon::parse($of->fecha)->format('d/m/Y') }}
                                    — {{ $of->tipo ?? 'General' }}
                                </div>
                            </div>
                            <span class="text-success fw-bold small">
                                Bs. {{ number_format($of->monto_total,2) }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted bg-transparent border-secondary py-4">
                            Sin ingresos registrados
                        </li>
                    @endforelse
                    </ul>
                </div>
                <div class="card-footer border-0 bg-transparent text-end">
                    <a href="{{ route('ingresos.index') }}" class="btn btn-sm btn-outline-success">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Últimos Egresos --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header bg-dark border-0 fw-semibold text-white">
                    <i class="bi bi-arrow-down-circle text-danger"></i>
                    Últimos Egresos
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                    @forelse($data['ultimos_egresos'] as $eg)
                        <li class="list-group-item d-flex justify-content-between bg-transparent border-secondary text-white">
                            <div>
                                <div class="small fw-semibold text-white">{{ $eg->tipo_egreso }}</div>
                                <div class="text-muted" style="font-size:11px">
                                    {{ \Carbon\Carbon::parse($eg->fecha)->format('d/m/Y') }}
                                    — {{ $eg->responsable ?? '-' }}
                                </div>
                            </div>
                            <span class="text-danger fw-bold small">
                                Bs. {{ number_format($eg->monto,2) }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted bg-transparent border-secondary py-4">
                            Sin egresos registrados
                        </li>
                    @endforelse
                    </ul>
                </div>
                <div class="card-footer border-0 bg-transparent text-end">
                    <a href="{{ route('egresos.index') }}" class="btn btn-sm btn-outline-danger">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
    @endif

    {{-- ══════════════════════════════════════════════ --}}
    {{-- BLOQUE 4: SOLO ADMINISTRADOR                  --}}
    {{-- ══════════════════════════════════════════════ --}}
    @if(auth()->user()->esAdmin())

    <div class="col-12 mb-2 mt-4">
        <h6 class="text-muted text-uppercase fw-bold">
            <i class="bi bi-gear"></i> Administración
        </h6>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-person-gear text-secondary fs-3"></i>
                    <div class="fs-2 fw-bold mt-1 text-white">{{ $data['total_usuarios'] }}</div>
                    <div class="text-muted small">Usuarios del Sistema</div>
                    <a href="{{ route('usuarios.index') }}"
                       class="btn btn-sm btn-outline-secondary mt-2">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-body text-center py-4">
                    <i class="bi bi-shield-check text-danger fs-3"></i>
                    <div class="fs-2 fw-bold mt-1 text-white">{{ $data['total_roles'] }}</div>
                    <div class="text-muted small">Roles Definidos</div>
                    <a href="{{ route('roles.index') }}"
                       class="btn btn-sm btn-outline-danger mt-2">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>

        {{-- Últimos usuarios --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background: var(--church-surface, #1e1e2d); border: 1px solid var(--church-border, #2b2b40) !important;">
                <div class="card-header bg-dark border-0 fw-semibold text-white">
                    <i class="bi bi-person-plus text-secondary"></i>
                    Últimos Usuarios Registrados
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush bg-transparent">
                    @foreach($data['ultimos_usuarios'] as $usr)
                        <li class="list-group-item d-flex align-items-center gap-2 bg-transparent border-secondary text-white">
                            <div class="rounded-circle bg-secondary text-white d-flex
                                align-items-center justify-content-center fw-bold"
                                style="width:34px;height:34px;font-size:13px">
                                {{ strtoupper(substr($usr->name,0,1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold text-white">{{ $usr->name }}</div>
                                <div class="text-muted" style="font-size:11px">
                                    {{ $usr->email }}
                                </div>
                            </div>
                            <div>
                            @foreach($usr->roles as $r)
                                @php
                                    $rc = match($r->nombre) {
                                        'Administrador' => 'danger',
                                        'Secretario'    => 'primary',
                                        'Tesorero'      => 'warning',
                                        default         => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $rc }} text-white">{{ $r->nombre }}</span>
                            @endforeach
                            </div>
                        </li>
                    @endforeach
                    </ul>
                </div>
                <div class="card-footer border-0 bg-transparent text-end">
                    <a href="{{ route('usuarios.index') }}"
                       class="btn btn-sm btn-outline-secondary">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>
    @endif

</div>
@endsection