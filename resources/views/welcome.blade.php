<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema Integrado de Administración - Panel de Control Principal">
    <title>SIA · Sistema Integrado de Administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-dark:       #0a0c14;
            --bg-card:       #111520;
            --bg-card-hover: #161b2e;
            --border:        rgba(255,255,255,0.07);
            --border-hover:  rgba(99,179,237,0.35);
            --accent:        #4f8ef7;
            --accent-2:      #7c5cfc;
            --accent-3:      #22d3a6;
            --accent-warn:   #f7b94f;
            --accent-danger: #f75f5f;
            --text-primary:  #eef0f6;
            --text-secondary:#8a91a8;
            --sidebar-w:     260px;
            --header-h:      64px;
            --radius:        14px;
            --transition:    0.25s cubic-bezier(.4,0,.2,1);
        }

        html, body { height: 100%; font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-primary); overflow-x: hidden; }

        /* ─── Scrollbar ─── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 99px; }

        /* ─── Layout ─── */
        .layout { display: flex; min-height: 100vh; }

        /* ══════════════════════════════
           SIDEBAR
        ══════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: transform var(--transition);
        }

        .sidebar-logo {
            height: var(--header-h);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 22px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border-radius: 10px;
            display: grid; place-items: center;
            font-size: 16px;
            box-shadow: 0 0 18px rgba(79,142,247,0.4);
        }

        .logo-text { font-size: 15px; font-weight: 700; letter-spacing: .3px; }
        .logo-text span { color: var(--accent); }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: 16px 12px; }

        .nav-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.2px;
            color: var(--text-secondary);
            text-transform: uppercase;
            padding: 14px 10px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 13px;
            border-radius: 10px;
            cursor: pointer;
            transition: background var(--transition), color var(--transition);
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            position: relative;
            margin-bottom: 2px;
        }

        .nav-item i { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }

        .nav-item:hover { background: rgba(79,142,247,0.08); color: var(--text-primary); }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(79,142,247,0.18), rgba(124,92,252,0.1));
            color: var(--accent);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 20%; bottom: 20%;
            width: 3px;
            border-radius: 99px;
            background: linear-gradient(var(--accent), var(--accent-2));
        }

        .nav-badge {
            margin-left: auto;
            background: var(--accent-danger);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 99px;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: background var(--transition);
        }
        .user-card:hover { background: rgba(255,255,255,0.05); }

        .avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent-2), var(--accent));
            display: grid; place-items: center;
            font-weight: 700; font-size: 14px;
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 11px; color: var(--text-secondary); }

        /* ══════════════════════════════
           MAIN
        ══════════════════════════════ */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ─── Header ─── */
        .header {
            height: var(--header-h);
            background: rgba(10,12,20,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header-title { font-size: 16px; font-weight: 600; }
        .header-breadcrumb { font-size: 13px; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; }
        .header-breadcrumb span { color: var(--accent); }

        .header-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 13px;
            color: var(--text-secondary);
            transition: border-color var(--transition), background var(--transition);
        }
        .search-bar:focus-within { border-color: var(--accent); background: rgba(79,142,247,0.07); color: var(--text-primary); }
        .search-bar input { background: none; border: none; outline: none; color: inherit; font-family: inherit; font-size: 13px; width: 160px; }
        .search-bar input::placeholder { color: var(--text-secondary); }

        .icon-btn {
            width: 36px; height: 36px;
            border-radius: 9px;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            display: grid; place-items: center;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 15px;
            transition: all var(--transition);
            position: relative;
        }
        .icon-btn:hover { background: rgba(79,142,247,0.12); border-color: var(--border-hover); color: var(--accent); }

        .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent-danger);
            border: 2px solid var(--bg-dark);
        }

        /* ─── Page Body ─── */
        .page-body { padding: 28px; flex: 1; }

        /* ─── Section title ─── */
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .section-title { font-size: 18px; font-weight: 700; }
        .section-sub { font-size: 13px; color: var(--text-secondary); margin-top: 2px; }
        .btn-outline {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition);
            font-family: inherit;
        }
        .btn-outline:hover { border-color: var(--accent); color: var(--accent); background: rgba(79,142,247,0.07); }

        /* ─── KPI Cards grid ─── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            cursor: pointer;
            transition: border-color var(--transition), transform var(--transition), box-shadow var(--transition);
            position: relative;
            overflow: hidden;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity var(--transition);
        }
        .kpi-card:hover { border-color: var(--border-hover); transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,0.35); }
        .kpi-card:hover::before { opacity: 1; }

        .kpi-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; }
        .kpi-icon {
            width: 42px; height: 42px;
            border-radius: 11px;
            display: grid; place-items: center;
            font-size: 18px;
        }
        .kpi-icon.blue  { background: rgba(79,142,247,0.15); color: var(--accent); }
        .kpi-icon.purple{ background: rgba(124,92,252,0.15);  color: var(--accent-2); }
        .kpi-icon.green { background: rgba(34,211,166,0.15);  color: var(--accent-3); }
        .kpi-icon.warn  { background: rgba(247,185,79,0.15);  color: var(--accent-warn); }
        .kpi-icon.danger{ background: rgba(247,95,95,0.15);   color: var(--accent-danger); }

        .kpi-trend { font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px; }
        .kpi-trend.up   { color: var(--accent-3); }
        .kpi-trend.down { color: var(--accent-danger); }

        .kpi-value { font-size: 30px; font-weight: 800; letter-spacing: -1px; margin-bottom: 4px; }
        .kpi-label { font-size: 13px; color: var(--text-secondary); font-weight: 500; }

        .kpi-bar { height: 4px; background: rgba(255,255,255,0.07); border-radius: 99px; margin-top: 16px; overflow: hidden; }
        .kpi-bar-fill { height: 100%; border-radius: 99px; transition: width 1s ease; }

        /* ─── Two-col grid ─── */
        .two-col { display: grid; grid-template-columns: 1fr 360px; gap: 20px; margin-bottom: 28px; }

        /* ─── Card base ─── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }
        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-title { font-size: 14px; font-weight: 600; }
        .card-body { padding: 18px 22px; }

        /* ─── Activity list ─── */
        .activity-list { display: flex; flex-direction: column; gap: 0; }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
            transition: background var(--transition);
        }
        .activity-item:last-child { border-bottom: none; }

        .act-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: grid; place-items: center;
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .act-info { flex: 1; min-width: 0; }
        .act-title { font-size: 13px; font-weight: 500; margin-bottom: 3px; }
        .act-desc { font-size: 12px; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .act-time { font-size: 11px; color: var(--text-secondary); white-space: nowrap; flex-shrink: 0; }

        /* ─── Quick access ─── */
        .quick-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .quick-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all var(--transition);
            text-align: center;
        }
        .quick-item:hover { border-color: var(--border-hover); background: rgba(79,142,247,0.07); transform: translateY(-2px); }
        .quick-icon { font-size: 22px; }
        .quick-label { font-size: 12px; font-weight: 500; color: var(--text-secondary); }

        /* ─── Module cards row ─── */
        .module-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px; }

        .module-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 22px 18px;
            cursor: pointer;
            transition: all var(--transition);
            display: flex;
            flex-direction: column;
            gap: 14px;
            position: relative;
            overflow: hidden;
        }
        .module-card:hover { border-color: var(--border-hover); transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.3); }

        .module-card-glow {
            position: absolute;
            width: 80px; height: 80px;
            border-radius: 50%;
            filter: blur(30px);
            top: -20px; right: -20px;
            opacity: 0.25;
            transition: opacity var(--transition);
        }
        .module-card:hover .module-card-glow { opacity: 0.5; }

        .module-icon { font-size: 24px; }
        .module-name { font-size: 14px; font-weight: 600; }
        .module-desc { font-size: 12px; color: var(--text-secondary); line-height: 1.5; }
        .module-arrow { font-size: 12px; color: var(--text-secondary); }

        /* ─── Status badge ─── */
        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 9px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-badge.active  { background: rgba(34,211,166,0.12); color: var(--accent-3); }
        .status-badge.warn    { background: rgba(247,185,79,0.12);  color: var(--accent-warn); }
        .status-badge.danger  { background: rgba(247,95,95,0.12);   color: var(--accent-danger); }
        .status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

        /* ─── System status ─── */
        .sys-list { display: flex; flex-direction: column; gap: 12px; }
        .sys-item { display: flex; align-items: center; justify-content: space-between; }
        .sys-name { font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .sys-name i { color: var(--text-secondary); width: 16px; text-align: center; }
        .sys-bar-wrap { flex: 1; margin: 0 14px; height: 5px; background: rgba(255,255,255,0.07); border-radius: 99px; overflow: hidden; }
        .sys-bar-fill { height: 100%; border-radius: 99px; transition: width 1s ease; }
        .sys-pct { font-size: 12px; font-weight: 600; min-width: 36px; text-align: right; }

        /* ─── Animations ─── */
        @keyframes fadeUp { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
        @keyframes pulse  { 0%,100% { opacity:1; } 50% { opacity:.55; } }

        .fade-up { animation: fadeUp .5s ease both; }
        .fade-up:nth-child(1) { animation-delay:.05s; }
        .fade-up:nth-child(2) { animation-delay:.10s; }
        .fade-up:nth-child(3) { animation-delay:.15s; }
        .fade-up:nth-child(4) { animation-delay:.20s; }
        .fade-up:nth-child(5) { animation-delay:.25s; }

        .pulse { animation: pulse 2s ease infinite; }

        /* ─── Responsive ─── */
        @media (max-width: 1100px) {
            .two-col { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .module-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .kpi-grid { grid-template-columns: 1fr; }
            .page-body { padding: 16px; }
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- ═══════════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <div class="logo-icon"><i class="fa-solid fa-layer-group" style="color:#fff"></i></div>
            <div class="logo-text"><span>SIA</span> · Admin</div>
        </div>

        <nav class="sidebar-nav">

            <div class="nav-label">Principal</div>
            <a href="#" class="nav-item active" id="nav-dashboard"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>

            <div class="nav-label">Gestión</div>
            <a href="#" class="nav-item" id="nav-usuarios"><i class="fa-solid fa-users"></i> Usuarios</a>
            <a href="#" class="nav-item" id="nav-roles"><i class="fa-solid fa-shield-halved"></i> Roles y Permisos</a>
            <a href="#" class="nav-item" id="nav-inventario"><i class="fa-solid fa-boxes-stacked"></i> Inventario</a>
            <a href="#" class="nav-item" id="nav-finanzas"><i class="fa-solid fa-chart-line"></i> Finanzas</a>
            <a href="#" class="nav-item" id="nav-nomina"><i class="fa-solid fa-money-bill-wave"></i> Nómina <span class="nav-badge">3</span></a>
            <a href="#" class="nav-item" id="nav-clientes"><i class="fa-solid fa-address-card"></i> Clientes</a>

            <div class="nav-label">Operaciones</div>
            <a href="#" class="nav-item" id="nav-proyectos"><i class="fa-solid fa-diagram-project"></i> Proyectos</a>
            <a href="#" class="nav-item" id="nav-reportes"><i class="fa-solid fa-file-chart-column"></i> Reportes</a>
            <a href="#" class="nav-item" id="nav-auditoria"><i class="fa-solid fa-clock-rotate-left"></i> Auditoría</a>

            <div class="nav-label">Sistema</div>
            <a href="#" class="nav-item" id="nav-config"><i class="fa-solid fa-gear"></i> Configuración</a>
            <a href="#" class="nav-item" id="nav-soporte"><i class="fa-solid fa-circle-question"></i> Soporte</a>

        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="avatar">A</div>
                <div class="user-info">
                    <div class="user-name">Administrador</div>
                    <div class="user-role">Super Admin</div>
                </div>
                <i class="fa-solid fa-ellipsis-vertical" style="color:var(--text-secondary);font-size:14px;"></i>
            </div>
        </div>

    </aside>

    <!-- ═══════════════════════════════════════
         MAIN
    ═══════════════════════════════════════ -->
    <div class="main">

        <!-- Header -->
        <header class="header">
            <div>
                <div class="header-breadcrumb">
                    <i class="fa-solid fa-house" style="font-size:11px"></i>
                    <i class="fa-solid fa-chevron-right" style="font-size:9px"></i>
                    <span>Dashboard</span>
                </div>
            </div>

            <div class="header-right">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:12px"></i>
                    <input type="text" placeholder="Buscar en el sistema..." id="search-input">
                    <kbd style="font-size:10px;color:var(--text-secondary);font-family:inherit;border:1px solid var(--border);border-radius:4px;padding:1px 5px;">⌘K</kbd>
                </div>
                <div class="icon-btn" title="Notificaciones" id="btn-notif">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notif-dot pulse"></span>
                </div>
                <div class="icon-btn" title="Configuración" id="btn-settings">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <div class="avatar" style="cursor:pointer;width:36px;height:36px;font-size:13px;" title="Perfil" id="btn-profile">A</div>
            </div>
        </header>

        <!-- Page Body -->
        <div class="page-body">

            <!-- Welcome -->
            <div class="section-header fade-up" style="margin-bottom:24px;">
                <div>
                    <h1 class="section-title">Panel de Control</h1>
                    <div class="section-sub">Sistema Integrado de Administración &mdash; Vista general en tiempo real</div>
                </div>
                <button class="btn-outline" id="btn-refresh"><i class="fa-solid fa-arrows-rotate"></i> Actualizar</button>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid">

                <div class="kpi-card fade-up" id="kpi-usuarios">
                    <div class="kpi-top">
                        <div class="kpi-icon blue"><i class="fa-solid fa-users"></i></div>
                        <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +12%</div>
                    </div>
                    <div class="kpi-value" data-target="1284">0</div>
                    <div class="kpi-label">Usuarios Activos</div>
                    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:72%;background:linear-gradient(90deg,var(--accent),var(--accent-2))"></div></div>
                </div>

                <div class="kpi-card fade-up" id="kpi-ingresos">
                    <div class="kpi-top">
                        <div class="kpi-icon green"><i class="fa-solid fa-dollar-sign"></i></div>
                        <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +8.4%</div>
                    </div>
                    <div class="kpi-value" data-target="94750" data-prefix="$">$0</div>
                    <div class="kpi-label">Ingresos del Mes</div>
                    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:85%;background:linear-gradient(90deg,var(--accent-3),#16a87e)"></div></div>
                </div>

                <div class="kpi-card fade-up" id="kpi-proyectos">
                    <div class="kpi-top">
                        <div class="kpi-icon purple"><i class="fa-solid fa-diagram-project"></i></div>
                        <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +3</div>
                    </div>
                    <div class="kpi-value" data-target="47">0</div>
                    <div class="kpi-label">Proyectos en Curso</div>
                    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:60%;background:linear-gradient(90deg,var(--accent-2),#a07cff)"></div></div>
                </div>

                <div class="kpi-card fade-up" id="kpi-incidencias">
                    <div class="kpi-top">
                        <div class="kpi-icon warn"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="kpi-trend down"><i class="fa-solid fa-arrow-trend-down"></i> −2</div>
                    </div>
                    <div class="kpi-value" data-target="5">0</div>
                    <div class="kpi-label">Incidencias Abiertas</div>
                    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:18%;background:linear-gradient(90deg,var(--accent-warn),#f7a320)"></div></div>
                </div>

                <div class="kpi-card fade-up" id="kpi-inventario">
                    <div class="kpi-top">
                        <div class="kpi-icon danger"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div class="kpi-trend down"><i class="fa-solid fa-arrow-trend-down"></i> −5%</div>
                    </div>
                    <div class="kpi-value" data-target="328">0</div>
                    <div class="kpi-label">Productos en Stock</div>
                    <div class="kpi-bar"><div class="kpi-bar-fill" style="width:40%;background:linear-gradient(90deg,var(--accent-danger),#f74f7e)"></div></div>
                </div>

            </div>

            <!-- Módulos -->
            <div class="section-header fade-up">
                <div>
                    <div class="section-title">Módulos del Sistema</div>
                    <div class="section-sub">Acceso directo a los módulos principales</div>
                </div>
            </div>

            <div class="module-grid" style="margin-bottom:28px;">

                <div class="module-card fade-up" id="mod-rrhh">
                    <div class="module-card-glow" style="background:var(--accent);"></div>
                    <div class="module-icon">👥</div>
                    <div>
                        <div class="module-name">Recursos Humanos</div>
                        <div class="module-desc">Gestión de personal, contratos y evaluaciones</div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="status-badge active">Activo</span>
                        <i class="fa-solid fa-arrow-right module-arrow"></i>
                    </div>
                </div>

                <div class="module-card fade-up" id="mod-finanzas">
                    <div class="module-card-glow" style="background:var(--accent-3);"></div>
                    <div class="module-icon">📊</div>
                    <div>
                        <div class="module-name">Finanzas</div>
                        <div class="module-desc">Contabilidad, facturación y presupuestos</div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="status-badge active">Activo</span>
                        <i class="fa-solid fa-arrow-right module-arrow"></i>
                    </div>
                </div>

                <div class="module-card fade-up" id="mod-inventario">
                    <div class="module-card-glow" style="background:var(--accent-warn);"></div>
                    <div class="module-icon">📦</div>
                    <div>
                        <div class="module-name">Inventario</div>
                        <div class="module-desc">Control de stock, almacenes y proveedores</div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="status-badge warn">En revisión</span>
                        <i class="fa-solid fa-arrow-right module-arrow"></i>
                    </div>
                </div>

                <div class="module-card fade-up" id="mod-clientes">
                    <div class="module-card-glow" style="background:var(--accent-2);"></div>
                    <div class="module-icon">🤝</div>
                    <div>
                        <div class="module-name">CRM / Clientes</div>
                        <div class="module-desc">Seguimiento, ventas y atención al cliente</div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="status-badge active">Activo</span>
                        <i class="fa-solid fa-arrow-right module-arrow"></i>
                    </div>
                </div>

                <div class="module-card fade-up" id="mod-reportes">
                    <div class="module-card-glow" style="background:#f75f5f;"></div>
                    <div class="module-icon">📋</div>
                    <div>
                        <div class="module-name">Reportes</div>
                        <div class="module-desc">Analítica, exportaciones y dashboards</div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="status-badge active">Activo</span>
                        <i class="fa-solid fa-arrow-right module-arrow"></i>
                    </div>
                </div>

                <div class="module-card fade-up" id="mod-auditoria">
                    <div class="module-card-glow" style="background:#22d3a6;"></div>
                    <div class="module-icon">🔍</div>
                    <div>
                        <div class="module-name">Auditoría</div>
                        <div class="module-desc">Logs del sistema, accesos y trazabilidad</div>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="status-badge active">Activo</span>
                        <i class="fa-solid fa-arrow-right module-arrow"></i>
                    </div>
                </div>

            </div>

            <!-- Two columns: Activity + Quick + System -->
            <div class="two-col">

                <!-- Actividad Reciente -->
                <div class="card fade-up">
                    <div class="card-header">
                        <div class="card-title"><i class="fa-solid fa-clock-rotate-left" style="color:var(--accent);margin-right:8px;"></i>Actividad Reciente</div>
                        <button class="btn-outline" style="font-size:12px;padding:5px 10px;" id="btn-ver-todo">Ver todo</button>
                    </div>
                    <div class="card-body" style="padding:6px 22px;">
                        <div class="activity-list">

                            <div class="activity-item">
                                <div class="act-icon kpi-icon blue"><i class="fa-solid fa-user-plus"></i></div>
                                <div class="act-info">
                                    <div class="act-title">Nuevo usuario registrado</div>
                                    <div class="act-desc">María García fue añadida al módulo de RRHH</div>
                                </div>
                                <div class="act-time">hace 5 min</div>
                            </div>

                            <div class="activity-item">
                                <div class="act-icon kpi-icon green"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                                <div class="act-info">
                                    <div class="act-title">Factura generada #F-2024-089</div>
                                    <div class="act-desc">Cliente: Corporación Del Norte S.A. · $12,500.00</div>
                                </div>
                                <div class="act-time">hace 18 min</div>
                            </div>

                            <div class="activity-item">
                                <div class="act-icon kpi-icon warn"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                <div class="act-info">
                                    <div class="act-title">Alerta de stock bajo</div>
                                    <div class="act-desc">Producto "Resma A4 80g" — quedan 12 unidades</div>
                                </div>
                                <div class="act-time">hace 34 min</div>
                            </div>

                            <div class="activity-item">
                                <div class="act-icon kpi-icon purple"><i class="fa-solid fa-diagram-project"></i></div>
                                <div class="act-info">
                                    <div class="act-title">Proyecto actualizado</div>
                                    <div class="act-desc">Modernización de Infraestructura — avance: 68%</div>
                                </div>
                                <div class="act-time">hace 1 h</div>
                            </div>

                            <div class="activity-item">
                                <div class="act-icon kpi-icon danger"><i class="fa-solid fa-lock"></i></div>
                                <div class="act-info">
                                    <div class="act-title">Intento de acceso denegado</div>
                                    <div class="act-desc">IP 192.168.1.45 — módulo de Finanzas</div>
                                </div>
                                <div class="act-time">hace 2 h</div>
                            </div>

                            <div class="activity-item">
                                <div class="act-icon kpi-icon green"><i class="fa-solid fa-money-bill-wave"></i></div>
                                <div class="act-info">
                                    <div class="act-title">Nómina procesada</div>
                                    <div class="act-desc">Período Mayo 2024 — 87 empleados</div>
                                </div>
                                <div class="act-time">hace 3 h</div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Derecha: Quick + System -->
                <div style="display:flex;flex-direction:column;gap:20px;">

                    <!-- Accesos Rápidos -->
                    <div class="card fade-up">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-bolt" style="color:var(--accent-warn);margin-right:8px;"></i>Accesos Rápidos</div>
                        </div>
                        <div class="card-body">
                            <div class="quick-grid">
                                <div class="quick-item" id="q-nuevo-usuario">
                                    <div class="quick-icon">👤</div>
                                    <div class="quick-label">Nuevo Usuario</div>
                                </div>
                                <div class="quick-item" id="q-nueva-factura">
                                    <div class="quick-icon">🧾</div>
                                    <div class="quick-label">Nueva Factura</div>
                                </div>
                                <div class="quick-item" id="q-generar-reporte">
                                    <div class="quick-icon">📈</div>
                                    <div class="quick-label">Generar Reporte</div>
                                </div>
                                <div class="quick-item" id="q-nuevo-producto">
                                    <div class="quick-icon">📦</div>
                                    <div class="quick-label">Nuevo Producto</div>
                                </div>
                                <div class="quick-item" id="q-nuevo-proyecto">
                                    <div class="quick-icon">🗂️</div>
                                    <div class="quick-label">Nuevo Proyecto</div>
                                </div>
                                <div class="quick-item" id="q-ver-auditoria">
                                    <div class="quick-icon">🔍</div>
                                    <div class="quick-label">Ver Auditoría</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estado del Sistema -->
                    <div class="card fade-up">
                        <div class="card-header">
                            <div class="card-title"><i class="fa-solid fa-server" style="color:var(--accent-3);margin-right:8px;"></i>Estado del Sistema</div>
                            <span class="status-badge active">Operativo</span>
                        </div>
                        <div class="card-body">
                            <div class="sys-list">
                                <div class="sys-item">
                                    <div class="sys-name"><i class="fa-solid fa-microchip"></i>CPU</div>
                                    <div class="sys-bar-wrap"><div class="sys-bar-fill" id="bar-cpu" style="width:0%;background:linear-gradient(90deg,var(--accent),var(--accent-2))"></div></div>
                                    <div class="sys-pct" id="pct-cpu" style="color:var(--accent)">0%</div>
                                </div>
                                <div class="sys-item">
                                    <div class="sys-name"><i class="fa-solid fa-memory"></i>RAM</div>
                                    <div class="sys-bar-wrap"><div class="sys-bar-fill" id="bar-ram" style="width:0%;background:linear-gradient(90deg,var(--accent-3),#16a87e)"></div></div>
                                    <div class="sys-pct" id="pct-ram" style="color:var(--accent-3)">0%</div>
                                </div>
                                <div class="sys-item">
                                    <div class="sys-name"><i class="fa-solid fa-hard-drive"></i>Disco</div>
                                    <div class="sys-bar-wrap"><div class="sys-bar-fill" id="bar-disk" style="width:0%;background:linear-gradient(90deg,var(--accent-warn),#f7a320)"></div></div>
                                    <div class="sys-pct" id="pct-disk" style="color:var(--accent-warn)">0%</div>
                                </div>
                                <div class="sys-item">
                                    <div class="sys-name"><i class="fa-solid fa-network-wired"></i>Red</div>
                                    <div class="sys-bar-wrap"><div class="sys-bar-fill" id="bar-net" style="width:0%;background:linear-gradient(90deg,var(--accent-2),#a07cff)"></div></div>
                                    <div class="sys-pct" id="pct-net" style="color:var(--accent-2)">0%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- /page-body -->
    </div><!-- /main -->
</div><!-- /layout -->

<script>
    /* ── Counter animation ── */
    function animateCounter(el) {
        const target = parseInt(el.dataset.target);
        const prefix = el.dataset.prefix || '';
        const duration = 1200;
        const start = performance.now();
        const update = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(eased * target);
            el.textContent = prefix + (target >= 1000 ? value.toLocaleString('es-MX') : value);
            if (progress < 1) requestAnimationFrame(update);
        };
        requestAnimationFrame(update);
    }

    /* ── System bars animation ── */
    function animateBars() {
        const values = { cpu: 38, ram: 61, disk: 54, net: 22 };
        setTimeout(() => {
            Object.entries(values).forEach(([key, val]) => {
                document.getElementById('bar-' + key).style.width = val + '%';
                document.getElementById('pct-' + key).textContent = val + '%';
            });
        }, 400);
    }

    /* ── Run on load ── */
    window.addEventListener('load', () => {
        document.querySelectorAll('.kpi-value[data-target]').forEach(animateCounter);
        animateBars();
    });

    /* ── Sidebar toggle (mobile) ── */
    document.getElementById('btn-notif')?.addEventListener('click', () => {
        alert('📬 Tienes 3 notificaciones pendientes.');
    });

    document.getElementById('btn-refresh')?.addEventListener('click', () => {
        document.querySelectorAll('.kpi-value[data-target]').forEach(el => {
            el.textContent = el.dataset.prefix || '0';
            animateCounter(el);
        });
    });

    /* ── Active nav ── */
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', e => {
            e.preventDefault();
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            item.classList.add('active');
        });
    });
</script>

</body>
</html>
