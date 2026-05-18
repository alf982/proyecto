<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CEP') · Contraloría del Estado Portuguesa</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-cep.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-dark: #0a0c14;
            --bg-card: #111520;
            --bg-card-hover: #161b2e;
            --border: rgba(255, 255, 255, 0.07);
            --border-hover: rgba(99, 179, 237, 0.35);
            --accent: #4f8ef7;
            --accent-2: #7c5cfc;
            --accent-3: #22d3a6;
            --accent-warn: #f7b94f;
            --accent-danger: #f75f5f;
            --text-primary: #eef0f6;
            --text-secondary: #8a91a8;
            --sidebar-w: 260px;
            --header-h: 64px;
            --radius: 14px;
            --transition: 0.25s cubic-bezier(.4, 0, .2, 1);
        }

        html,
        body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
        }

        /* overflow-x en el contenedor principal: evita scroll de body sin bloquear tablas */
        .layout {
            display: flex;
            min-height: 100vh;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 99px;
        }

        /* Layout */
        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
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
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            overflow: hidden;
            flex-shrink: 0;
            background: #ffffff;
            padding: 3px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.35);
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-text {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }

        .logo-text span {
            color: var(--accent);
        }

        .logo-sub {
            font-size: 10px;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 16px 12px;
        }

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
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            position: relative;
            margin-bottom: 2px;
        }

        .nav-item i {
            width: 18px;
            text-align: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .nav-item:hover {
            background: rgba(79, 142, 247, 0.08);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(79, 142, 247, 0.18), rgba(124, 92, 252, 0.1));
            color: var(--accent);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            border-radius: 99px;
            background: linear-gradient(var(--accent), var(--accent-2));
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

        .user-card:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent-2), var(--accent));
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 11px;
            color: var(--text-secondary);
        }

        /* Main: min-width:0 es clave para que flex no desborde */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
        }

        .header {
            height: var(--header-h);
            background: rgba(10, 12, 20, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .header-breadcrumb {
            font-size: 12px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .header-breadcrumb .current {
            color: var(--text-primary);
            font-weight: 500;
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            display: grid;
            place-items: center;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 14px;
            transition: all var(--transition);
            text-decoration: none;
        }

        .icon-btn:hover {
            background: rgba(79, 142, 247, 0.12);
            border-color: var(--border-hover);
            color: var(--accent);
        }

        /* Page body */
        .page-body {
            padding: 28px;
            flex: 1;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* Card: overflow visible para no bloquear scroll de tablas internas */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .card-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
        }

        .card-body {
            padding: 22px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: inherit;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff;
            box-shadow: 0 4px 15px rgba(79, 142, 247, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 142, 247, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-secondary);
        }

        .btn-outline:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(79, 142, 247, 0.07);
        }

        .btn-danger {
            background: rgba(247, 95, 95, 0.15);
            border: 1px solid rgba(247, 95, 95, 0.3);
            color: var(--accent-danger);
        }

        .btn-danger:hover {
            background: rgba(247, 95, 95, 0.25);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 7px;
        }

        /* Table */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            /* iOS momentum scroll */
            overscroll-behavior-x: contain;
            /* evita scroll del padre al llegar al borde */
            position: relative;
        }

        /* Indicador sutil de que hay contenido hacia la derecha */
        .table-wrap::after {
            content: '';
            display: block;
            position: sticky;
            bottom: 0;
            right: 0;
            pointer-events: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead tr {
            border-bottom: 2px solid var(--border);
        }

        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: var(--text-secondary);
            white-space: nowrap;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .badge-active {
            background: rgba(34, 211, 166, 0.12);
            color: var(--accent-3);
        }

        .badge-warn {
            background: rgba(247, 185, 79, 0.12);
            color: var(--accent-warn);
        }

        .badge-danger {
            background: rgba(247, 95, 95, 0.12);
            color: var(--accent-danger);
        }

        .badge-blue {
            background: rgba(79, 142, 247, 0.12);
            color: var(--accent);
        }

        .badge-purple {
            background: rgba(124, 92, 252, 0.12);
            color: var(--accent-2);
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 7px;
            letter-spacing: .4px;
        }

        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 10px 14px;
            font-size: 13.5px;
            color: var(--text-primary);
            font-family: inherit;
            transition: border-color var(--transition), background var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(79, 142, 247, 0.05);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
        }

        select.form-control option {
            background: var(--bg-card);
            color: var(--text-primary);
        }

        .form-row {
            display: grid;
            gap: 16px;
        }

        .form-row-2 {
            grid-template-columns: 1fr 1fr;
        }

        .form-row-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .form-error {
            font-size: 12px;
            color: var(--accent-danger);
            margin-top: 5px;
        }

        /* Alert flash */
        .flash {
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .flash-success {
            background: rgba(34, 211, 166, 0.1);
            border: 1px solid rgba(34, 211, 166, 0.25);
            color: var(--accent-3);
        }

        .flash-error {
            background: rgba(247, 95, 95, 0.1);
            border: 1px solid rgba(247, 95, 95, 0.25);
            color: var(--accent-danger);
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 6px;
            align-items: center;
            padding: 16px 22px;
            border-top: 1px solid var(--border);
        }

        .page-link {
            padding: 6px 11px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            cursor: pointer;
            text-decoration: none;
            transition: all var(--transition);
        }

        .page-link:hover,
        .page-link.active {
            background: rgba(79, 142, 247, 0.15);
            border-color: var(--accent);
            color: var(--accent);
        }

        .page-info {
            font-size: 12px;
            color: var(--text-secondary);
            margin-left: auto;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: .4;
        }

        .empty-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-desc {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Alerts */
        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(34, 211, 166, 0.10);
            border-color: rgba(34, 211, 166, 0.30);
            color: var(--accent-3, #22d3a6);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.10);
            border-color: rgba(239, 68, 68, 0.30);
            color: #f87171;
        }

        .alert-warning {
            background: rgba(250, 189, 0, 0.10);
            border-color: rgba(250, 189, 0, 0.30);
            color: var(--accent-warn, #fabd00);
        }

        .alert-info {
            background: rgba(79, 142, 247, 0.10);
            border-color: rgba(79, 142, 247, 0.30);
            color: var(--accent, #4f8ef7);
        }

        /* Hamburger & Drawer */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 6px;
        }

        .hamburger span {
            display: block;
            width: 22px;
            height: 2px;
            background: var(--text-primary);
            border-radius: 2px;
            transition: all var(--transition);
        }

        .hamburger.open span:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }

        .hamburger.open span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.open span:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 99;
        }

        .overlay.show {
            display: block;
        }

        /* Animations */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-up {
            animation: fadeUp .4s ease both;
        }

        /* ── RESPONSIVE ──────────────────────────────────────────── */

        /* Tablet (≤1024px) */
        @media (max-width: 1024px) {
            :root {
                --sidebar-w: 220px;
            }

            .page-body {
                padding: 20px;
            }

            .form-row-3 {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Mobile (≤768px) */
        @media (max-width: 768px) {
            :root {
                --sidebar-w: 280px;
                --header-h: 56px;
            }

            /* Sidebar: drawer deslizable */
            .hamburger {
                display: flex;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform var(--transition);
                width: var(--sidebar-w);
                z-index: 200;
            }

            .sidebar.open {
                transform: translateX(0);
                box-shadow: 8px 0 32px rgba(0, 0, 0, 0.5);
            }

            .main {
                margin-left: 0;
            }

            /* Header compacto */
            .header {
                padding: 0 14px;
                gap: 10px;
            }

            .header-breadcrumb {
                font-size: 11px;
                max-width: calc(100vw - 130px);
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }

            .header-right {
                gap: 6px;
            }

            /* Page body */
            .page-body {
                padding: 14px;
                padding-bottom: 80px;
            }

            .page-title {
                font-size: 17px;
            }

            .page-subtitle {
                font-size: 12px;
            }

            .page-header {
                margin-bottom: 16px;
            }

            /* Cards */
            .card-header {
                padding: 12px 14px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .card-body {
                padding: 14px;
            }

            .card-title {
                font-size: 13px;
            }

            /* Tablas → scroll horizontal real en móvil */
            .table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-x: contain;
                max-width: calc(100vw - 28px);
                width: 100%;
                /* Indica que el elemento puede recibir gestos horizontales */
                touch-action: pan-x pan-y;
            }

            /* Las tablas mantienen su ancho natural (no colapsan) */
            .table-wrap table {
                width: max-content;
                min-width: 100%;
            }

            /* Celdas compactas en móvil */
            th {
                padding: 9px 10px;
                font-size: 10px;
                white-space: nowrap;
            }

            td {
                padding: 10px 10px;
                font-size: 12px;
            }

            /* Formularios: siempre columna única */
            .form-row-2,
            .form-row-3 {
                grid-template-columns: 1fr;
            }

            .form-control {
                font-size: 16px;
                /* evita zoom en iOS */
                padding: 11px 13px;
            }

            .form-group {
                margin-bottom: 16px;
            }

            /* Botones */
            .btn {
                padding: 9px 14px;
                font-size: 12px;
            }

            .btn-sm {
                padding: 7px 10px;
                font-size: 11px;
            }

            /* Badges */
            .badge {
                font-size: 10px;
                padding: 2px 8px;
            }

            /* Flash messages */
            .flash {
                font-size: 12px;
                padding: 10px 14px;
            }

            /* Grids inline usados en vistas (KPI cards) */
            [style*="grid-template-columns:repeat(auto-fill"] {
                grid-template-columns: 1fr 1fr !important;
            }

            /* Grids de 4 columnas → 2 en móvil */
            [style*="grid-template-columns:repeat(4"] {
                grid-template-columns: 1fr 1fr !important;
            }

            /* Grids de 3 columnas → 1 en móvil */
            [style*="grid-template-columns:repeat(3"] {
                grid-template-columns: 1fr !important;
            }

            /* Grids de 2 columnas (paneles laterales) → 1 en móvil */
            [style*="grid-template-columns:1fr 1fr"],
            [style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }

            /* Grids con columna fija tipo "1fr 320px" → 1 en móvil */
            [style*="grid-template-columns:1fr 3"],
            [style*="grid-template-columns: 1fr 3"] {
                grid-template-columns: 1fr !important;
            }

            [style*="grid-template-columns:1fr 2"],
            [style*="grid-template-columns: 1fr 2"] {
                grid-template-columns: 1fr !important;
            }

            /* Flex rows → columna */
            .page-header[style*="display:flex"] {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            /* Pagination */
            .pagination {
                flex-wrap: wrap;
                gap: 4px;
                padding: 12px 14px;
            }

            .page-info {
                width: 100%;
                text-align: center;
                margin-left: 0;
                margin-top: 4px;
            }

            /* Bottom navigation bar (móvil) */
            .mobile-bottom-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 60px;
                background: var(--bg-card);
                border-top: 1px solid var(--border);
                z-index: 150;
                justify-content: space-around;
                align-items: center;
                padding: 0 4px;
            }

            .mobile-bottom-nav a {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 3px;
                color: var(--text-secondary);
                font-size: 10px;
                font-weight: 500;
                text-decoration: none;
                flex: 1;
                padding: 8px 0;
                border-radius: 8px;
                transition: color var(--transition), background var(--transition);
            }

            .mobile-bottom-nav a i {
                font-size: 16px;
            }

            .mobile-bottom-nav a.active,
            .mobile-bottom-nav a:hover {
                color: var(--accent);
                background: rgba(79, 142, 247, 0.08);
            }
        }

        /* Pantallas muy pequeñas (≤380px) */
        @media (max-width: 380px) {
            .page-body {
                padding: 10px;
                padding-bottom: 80px;
            }

            .page-title {
                font-size: 15px;
            }

            [style*="grid-template-columns:repeat(auto-fill"] {
                grid-template-columns: 1fr !important;
            }

            [style*="grid-template-columns:repeat(4"] {
                grid-template-columns: 1fr !important;
            }

            [style*="grid-template-columns:1fr 1fr"],
            [style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Desktop: ocultar bottom nav */
        @media (min-width: 769px) {
            .mobile-bottom-nav {
                display: none;
            }
        }

        /* ── Utilidades responsive ──────────────────────────────── */
        /* Ocultar en móvil */
        @media (max-width: 768px) {
            .hide-mobile {
                display: none !important;
            }

            /* Flex → columna en móvil */
            .stack-mobile {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            /* Ancho completo en móvil */
            .full-mobile {
                width: 100% !important;
                min-width: unset !important;
            }

            /* Logo más compacto */
            .sidebar-logo .logo-sub {
                display: none;
            }

            /* Acciones en tablas: apilar verticalmente */
            .actions-wrap {
                flex-wrap: wrap !important;
                gap: 4px !important;
            }

            /* Stat cards dashboard */
            .stat-card-grid {
                grid-template-columns: 1fr 1fr !important;
                gap: 12px !important;
            }

            /* Quitar display flex en headers de página con muchos elementos */
            .card-header {
                flex-wrap: wrap !important;
            }

            /* Charts y elementos anchos: full width */
            canvas {
                max-width: 100% !important;
            }

            /* Tablas: celdas más compactas */
            .table-compact td {
                padding: 8px 10px !important;
            }

            /* Modales */
            .modal-box {
                width: 95vw !important;
                max-width: 95vw !important;
            }
        }

        /* ── Scroll suave ─────────────────────────────────────────── */
        html {
            scroll-behavior: smooth;
        }

        /* ── Safe area para notch de iPhone ──────────────────────── */
        @supports (padding: max(0px)) {
            .mobile-bottom-nav {
                padding-bottom: max(0px, env(safe-area-inset-bottom));
                height: calc(60px + max(0px, env(safe-area-inset-bottom)));
            }

            @media (max-width: 768px) {
                .page-body {
                    padding-bottom: calc(80px + max(0px, env(safe-area-inset-bottom)));
                }
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="overlay" id="sidebar-overlay" style="z-index:199;"></div>
    <div class="layout">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <img src="{{ asset('images/logo-cep.png') }}" alt="Logo CEP">
                </div>
                <div>
                    <div class="logo-text"><span>CEP</span> · Sistema</div>
                    <div class="logo-sub">Contraloría E. Portuguesa</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-label">Principal</div>
                <a href="{{ route('dashboard') }}"
                    class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>

                {{-- ── Administración ── --}}
                @canany(['usuarios.ver', 'unidades.ver', 'beneficiarios.ver'])
                    <div class="nav-label">Administración</div>
                    @can('usuarios.ver')
                        <a href="{{ route('admin.usuarios.index') }}"
                            class="nav-item {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}" id="nav-usuarios">
                            <i class="fa-solid fa-users"></i> Usuarios
                        </a>
                    @endcan
                    @can('unidades.ver')
                        <a href="{{ route('admin.unidades.index') }}"
                            class="nav-item {{ request()->routeIs('admin.unidades.*') ? 'active' : '' }}" id="nav-unidades">
                            <i class="fa-solid fa-sitemap"></i> Unidades Ejecutoras
                        </a>
                    @endcan
                    @can('beneficiarios.ver')
                        <a href="{{ route('admin.beneficiarios.index') }}"
                            class="nav-item {{ request()->routeIs('admin.beneficiarios.*') ? 'active' : '' }}"
                            id="nav-beneficiarios">
                            <i class="fa-solid fa-building"></i> Beneficiarios
                        </a>
                    @endcan
                    @can('roles.ver')
                        <div class="nav-divider"></div>
                        <a href="{{ route('admin.roles.index') }}"
                            class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" id="nav-roles">
                            <i class="fa-solid fa-shield-halved"></i> Roles y Permisos
                        </a>
                    @endcan
                    @can('roles.gestionar')
                        <a href="{{ route('admin.auditoria.index') }}"
                            class="nav-item {{ request()->routeIs('admin.auditoria.*') ? 'active' : '' }}" id="nav-auditoria">
                            <i class="fa-solid fa-clock-rotate-left"></i> Auditoría del Sistema
                        </a>
                    @endcan
                @endcanany

                {{-- ── Presupuesto ── --}}
                @canany(['ejercicios.ver', 'partidas.ver', 'proyectos.ver', 'compromisos.ver', 'causaciones.ver', 'pagos.ver', 'presupuesto.reportes'])
                    <div class="nav-label">Presupuesto</div>
                    @can('ejercicios.ver')
                        <a href="{{ route('presupuesto.ejercicios.index') }}"
                            class="nav-item {{ request()->routeIs('presupuesto.ejercicios.*') ? 'active' : '' }}"
                            id="nav-ejercicios">
                            <i class="fa-solid fa-calendar-days"></i> Ejercicios Fiscales
                        </a>
                    @endcan
                    @can('partidas.ver')
                        <a href="{{ route('presupuesto.partidas.index') }}"
                            class="nav-item {{ request()->routeIs('presupuesto.partidas.*') ? 'active' : '' }}"
                            id="nav-partidas">
                            <i class="fa-solid fa-list-ol"></i> Catálogo de Partidas
                        </a>
                        <a href="{{ route('presupuesto.movimientos-partidas.index') }}"
                            class="nav-item {{ request()->routeIs('presupuesto.movimientos-partidas.*') ? 'active' : '' }}"
                            id="nav-movimientos-partidas">
                            <i class="fa-solid fa-arrows-left-right"></i> Movimientos de Partidas
                        </a>
                    @endcan
                    @can('proyectos.ver')
                        <a href="{{ route('presupuesto.proyectos.index') }}"
                            class="nav-item {{ request()->routeIs('presupuesto.proyectos.*') ? 'active' : '' }}"
                            id="nav-proyectos">
                            <i class="fa-solid fa-diagram-project"></i> Proyectos
                        </a>
                    @endcan
                    @can('compromisos.ver')
                        <a href="{{ route('presupuesto.compromisos.index') }}"
                            class="nav-item {{ request()->routeIs('presupuesto.compromisos.*') ? 'active' : '' }}"
                            id="nav-compromisos">
                            <i class="fa-solid fa-handshake"></i> Compromisos
                        </a>
                    @endcan
                    @can('causaciones.ver')
                        <a href="{{ route('presupuesto.causaciones.index') }}"
                            class="nav-item {{ request()->routeIs('presupuesto.causaciones.*') ? 'active' : '' }}"
                            id="nav-causaciones">
                            <i class="fa-solid fa-file-invoice-dollar"></i> Causaciones
                        </a>
                    @endcan
                    @can('pagos.ver')
                        <a href="{{ route('presupuesto.pagos.index') }}"
                            class="nav-item {{ request()->routeIs('presupuesto.pagos.*') ? 'active' : '' }}" id="nav-pagos">
                            <i class="fa-solid fa-money-bill-wave"></i> Pagos
                        </a>
                    @endcan

                    @can('presupuesto.reportes')
                        <div class="nav-divider"></div>
                        <a href="{{ route('presupuesto.reportes.ejecucion') }}"
                            class="nav-item {{ request()->routeIs('presupuesto.reportes.*') ? 'active' : '' }}"
                            id="nav-reportes">
                            <i class="fa-solid fa-chart-pie"></i> Ejecución Presupuestaria
                        </a>
                    @endcan
                @endcanany

                {{-- ── Tesorería ── --}}
                @can('tesoreria.cuentas.ver')
                    <div class="nav-label" style="margin-top:10px;">Tesorería</div>
                    <a href="{{ route('tesoreria.cuentas.index') }}"
                        class="nav-item {{ request()->routeIs('tesoreria.cuentas.*') ? 'active' : '' }}" id="nav-cuentas">
                        <i class="fa-solid fa-building-columns"></i> Cuentas Bancarias
                    </a>
                @endcan


                {{-- ── Compras y Almacén ── --}}
                @canany(['compras.almacenes.ver', 'compras.articulos.ver', 'compras.solicitudes.ver', 'compras.ordenes.ver', 'compras.recepciones.ver', 'almacen.solicitudes.ver'])
                    <div class="nav-label" style="margin-top:10px;">Compras y Almacén</div>
                    @can('compras.almacenes.ver')
                        <a href="{{ route('compras.almacenes.index') }}"
                            class="nav-item {{ request()->routeIs('compras.almacenes.*') ? 'active' : '' }}" id="nav-almacenes">
                            <i class="fa-solid fa-warehouse"></i> Almacenes
                        </a>
                    @endcan
                    @can('compras.articulos.ver')
                        <a href="{{ route('compras.articulos.index') }}"
                            class="nav-item {{ request()->routeIs('compras.articulos.*') ? 'active' : '' }}" id="nav-articulos">
                            <i class="fa-solid fa-boxes-stacked"></i> Catálogo de Artículos
                        </a>
                    @endcan
                    @can('almacen.solicitudes.ver')
                        <div class="nav-divider"></div>
                        <a href="{{ route('almacen.solicitudes.index') }}"
                            class="nav-item {{ request()->routeIs('almacen.solicitudes.*') ? 'active' : '' }}"
                            id="nav-despacho">
                            <i class="fa-solid fa-dolly"></i> Solicitudes de Despacho
                        </a>
                    @endcan
                    @can('compras.solicitudes.ver')
                        <div class="nav-divider"></div>
                        <a href="{{ route('compras.solicitudes.index') }}"
                            class="nav-item {{ request()->routeIs('compras.solicitudes.*') ? 'active' : '' }}"
                            id="nav-solicitudes">
                            <i class="fa-solid fa-clipboard-list"></i> Solicitudes de Compra
                        </a>
                    @endcan
                    @can('compras.ordenes.ver')
                        <a href="{{ route('compras.ordenes.index') }}"
                            class="nav-item {{ request()->routeIs('compras.ordenes.*') ? 'active' : '' }}" id="nav-ordenes">
                            <i class="fa-solid fa-file-invoice"></i> Órdenes de Compra
                        </a>
                    @endcan
                    @can('compras.recepciones.ver')
                        <a href="{{ route('compras.recepciones.index') }}"
                            class="nav-item {{ request()->routeIs('compras.recepciones.*') ? 'active' : '' }}"
                            id="nav-recepciones">
                            <i class="fa-solid fa-truck-ramp-box"></i> Recepciones de Bienes
                        </a>
                    @endcan
                @endcanany



                {{-- ── Bienes Nacionales ── --}}
                @canany(['bienes.ver', 'bienes.categorias.ver'])
                    <div class="nav-label" style="margin-top:10px;">Bienes Nacionales</div>
                    @can('bienes.categorias.ver')
                        <a href="{{ route('bienes.categorias.index') }}"
                            class="nav-item {{ request()->routeIs('bienes.categorias.*') ? 'active' : '' }}"
                            id="nav-categorias-bien">
                            <i class="fa-solid fa-tags"></i> Categorías de Bienes
                        </a>
                    @endcan
                    @can('bienes.ver')
                        <a href="{{ route('bienes.bienes.index') }}"
                            class="nav-item {{ request()->routeIs('bienes.bienes.*') ? 'active' : '' }}" id="nav-bienes">
                            <i class="fa-solid fa-computer"></i> Inventario de Bienes
                        </a>
                    @endcan
                @endcanany

                {{-- ── Nómina y Personal ── --}}
                @canany(['nomina.empleados.ver', 'nomina.cargos.ver', 'nomina.conceptos.ver', 'nomina.nominas.ver'])
                    <div class="nav-label" style="margin-top:10px;">Nómina y Personal</div>
                    @can('nomina.empleados.ver')
                        <a href="{{ route('nomina.empleados.index') }}"
                            class="nav-item {{ request()->routeIs('nomina.empleados.*') ? 'active' : '' }}" id="nav-empleados">
                            <i class="fa-solid fa-id-card"></i> Empleados
                        </a>
                    @endcan
                    @can('nomina.cargos.ver')
                        <a href="{{ route('nomina.cargos.index') }}"
                            class="nav-item {{ request()->routeIs('nomina.cargos.*') ? 'active' : '' }}" id="nav-cargos">
                            <i class="fa-solid fa-briefcase"></i> Cargos
                        </a>
                    @endcan
                    @can('nomina.conceptos.ver')
                        <a href="{{ route('nomina.conceptos.index') }}"
                            class="nav-item {{ request()->routeIs('nomina.conceptos.*') ? 'active' : '' }}"
                            id="nav-conceptos-nomina">
                            <i class="fa-solid fa-sliders"></i> Conceptos de Nómina
                        </a>
                    @endcan
                    @can('nomina.nominas.ver')
                        <div class="nav-divider"></div>
                        <a href="{{ route('nomina.nominas.index') }}"
                            class="nav-item {{ request()->routeIs('nomina.nominas.*') ? 'active' : '' }}" id="nav-nominas">
                            <i class="fa-solid fa-money-check-dollar"></i> Nóminas
                        </a>
                    @endcan
                @endcanany

                {{-- ── Configuración Fiscal ── --}}
                @canany(['retenciones.ver'])
                    <div class="nav-label" style="margin-top:10px;">Configuración Fiscal</div>
                    @can('retenciones.ver')
                        <a href="{{ route('retenciones.index') }}"
                            class="nav-item {{ request()->routeIs('retenciones.*') ? 'active' : '' }}"
                            id="nav-retenciones">
                            <i class="fa-solid fa-percent"></i> Retenciones
                        </a>
                    @endcan
                @endcanany

            </nav>

            <div class="sidebar-footer">
                <div class="user-card">
                    <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->getRoleNames()->first() ?? 'Sin rol' }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0">
                        @csrf
                        <button type="submit" class="icon-btn" title="Cerrar sesión" style="border:none;">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- MAIN -->
        <div class="main">
            <header class="header">
                <button class="hamburger" id="sidebar-toggle" aria-label="Menú" style="margin-right:8px;">
                    <span></span><span></span><span></span>
                </button>
                <div class="header-breadcrumb">
                    <i class="fa-solid fa-house" style="font-size:11px"></i>
                    <i class="fa-solid fa-chevron-right" style="font-size:9px;opacity:.5"></i>
                    @yield('breadcrumb')
                </div>
                <div class="header-right" style="display:flex;align-items:center;gap:.75rem">
                    {{-- Selector de Ejercicio Fiscal --}}
                    @php $ejercicios = \App\Models\EjercicioFiscal::orderByDesc('anio')->get(); @endphp
                    @if($ejercicios->count() > 0)
                        <div style="position:relative" id="ej-picker">
                            <button type="button"
                                onclick="document.getElementById('ej-dropdown').classList.toggle('ej-show')"
                                style="display:flex;align-items:center;gap:.5rem;background:rgba(79,142,247,.12);border:1px solid rgba(79,142,247,.25);color:var(--accent);border-radius:8px;padding:.35rem .75rem;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s"
                                title="Cambiar ejercicio fiscal">
                                <i class="fa-solid fa-calendar-days" style="font-size:.7rem"></i>
                                {{ $ejercicioActual ? $ejercicioActual->anio : 'Sin ejercicio' }}
                                <i class="fa-solid fa-chevron-down" style="font-size:.6rem;opacity:.7"></i>
                            </button>
                            <div id="ej-dropdown"
                                style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:var(--bg-card);border:1px solid var(--border);border-radius:10px;min-width:180px;box-shadow:0 8px 24px rgba(0,0,0,.4);z-index:999;padding:.4rem 0;overflow:hidden">
                                <div
                                    style="padding:.4rem .8rem .3rem;font-size:.68rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.8px">
                                    Ejercicio Fiscal</div>
                                @foreach($ejercicios as $ej)
                                    <form method="POST" action="{{ route('ejercicio.seleccionar') }}">
                                        @csrf
                                        <input type="hidden" name="ejercicio_id" value="{{ $ej->id }}">
                                        <button type="submit"
                                            style="width:100%;text-align:left;background:{{ ($ejercicioActual && $ejercicioActual->id === $ej->id) ? 'rgba(79,142,247,.12)' : 'transparent' }};border:none;padding:.5rem .8rem;font-size:.82rem;color:{{ ($ejercicioActual && $ejercicioActual->id === $ej->id) ? 'var(--accent)' : 'var(--text-primary)' }};cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:.5rem;transition:background .15s"
                                            onmouseover="this.style.background='rgba(79,142,247,.08)'"
                                            onmouseout="this.style.background='{{ ($ejercicioActual && $ejercicioActual->id === $ej->id) ? 'rgba(79,142,247,.12)' : 'transparent' }}'">
                                            <span>{{ $ej->anio }}</span>
                                            <span
                                                style="font-size:.68rem;padding:.1rem .4rem;border-radius:4px;background:{{ $ej->estado === 'activo' ? 'rgba(34,211,166,.15)' : ($ej->estado === 'cerrado' ? 'rgba(247,95,95,.15)' : 'rgba(255,255,255,.07)') }};color:{{ $ej->estado === 'activo' ? 'var(--accent-3)' : ($ej->estado === 'cerrado' ? 'var(--accent-danger)' : 'var(--text-secondary)') }}">{{ ucfirst($ej->estado) }}</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                        <script>
                            document.addEventListener('click', function (e) {
                                if (!document.getElementById('ej-picker')?.contains(e.target))
                                    document.getElementById('ej-dropdown')?.classList.remove('ej-show');
                            });
                            document.getElementById('ej-dropdown') && document.getElementById('ej-dropdown').addEventListener('click', e => e.stopPropagation());
                            document.querySelector('#ej-dropdown') && (document.querySelector('.ej-show') ? null : null);
                            // toggle via class
                            var ejBtn = document.querySelector('#ej-picker > button');
                            if (ejBtn) { ejBtn.onclick = function () { var d = document.getElementById('ej-dropdown'); d.style.display = d.style.display === 'none' ? 'block' : 'none'; }; }
                        </script>
                    @endif
                    <a href="{{ route('dashboard') }}" class="icon-btn" title="Dashboard">
                        <i class="fa-solid fa-gauge-high"></i>
                    </a>
                </div>
            </header>

            <div class="page-body">
                @if(session('success'))
                    <div class="flash flash-success fade-up">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="flash flash-error fade-up">
                        <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

    </div>

    {{-- Barra inferior móvil --}}
    <nav class="mobile-bottom-nav" id="mobile-bottom-nav">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Inicio</span>
        </a>
        @canany(['presupuesto.reportes', 'partidas.ver'])
            <a href="{{ route('presupuesto.reportes.ejecucion') }}"
                class="{{ request()->is('presupuesto/*') ? 'active' : '' }}">
                <i class="fa-solid fa-coins"></i>
                <span>Presupuesto</span>
            </a>
        @endcanany
        @canany(['compras.solicitudes.ver', 'compras.ordenes.ver', 'inventario.ver'])
            <a href="{{ route('compras.solicitudes.index') }}"
                class="{{ request()->is('compras/*') || request()->is('inventario/*') ? 'active' : '' }}">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Compras</span>
            </a>
        @endcanany
        @canany(['nomina.nominas.ver', 'nomina.empleados.ver'])
            <a href="{{ route('nomina.nominas.index') }}" class="{{ request()->is('nomina/*') ? 'active' : '' }}">
                <i class="fa-solid fa-money-check-dollar"></i>
                <span>Nómina</span>
            </a>
        @endcanany
        <a href="#" id="mobile-menu-btn" onclick="document.getElementById('sidebar-toggle').click();return false;">
            <i class="fa-solid fa-bars"></i>
            <span>Menú</span>
        </a>
    </nav>

    @stack('scripts')
    <script>
        (function () {
            var btn = document.getElementById('sidebar-toggle');
            var sidebar = document.querySelector('.sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            var body = document.body;

            function closeSidebar() {
                sidebar.classList.remove('open');
                btn.classList.remove('open');
                overlay.classList.remove('show');
                // Restaurar scroll del body
                body.style.overflow = '';
            }

            btn.addEventListener('click', function () {
                var isOpen = sidebar.classList.toggle('open');
                btn.classList.toggle('open', isOpen);
                overlay.classList.toggle('show', isOpen);
                // Bloquear scroll del body cuando el drawer está abierto (solo móvil)
                if (window.innerWidth <= 768) {
                    body.style.overflow = isOpen ? 'hidden' : '';
                }
            });

            overlay.addEventListener('click', closeSidebar);

            // Cerrar al navegar en móvil
            document.querySelectorAll('.nav-item').forEach(function (el) {
                el.addEventListener('click', function () {
                    if (window.innerWidth <= 768) closeSidebar();
                });
            });

            // Al redimensionar a desktop: limpiar estado móvil
            window.addEventListener('resize', function () {
                if (window.innerWidth > 768) {
                    body.style.overflow = '';
                    overlay.classList.remove('show');
                }
            });
        })();
    </script>
</body>

</html>