<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Presentation Studio')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(214.3 31.8% 91.4%)",
                        input: "hsl(214.3 31.8% 91.4%)",
                        ring: "hsl(222.2 84% 4.9%)",
                        background: "hsl(0 0% 100%)",
                        foreground: "hsl(222.2 84% 4.9%)",
                        primary: {
                            DEFAULT: "hsl(222.2 47.4% 11.2%)",
                            foreground: "hsl(210 40% 98%)",
                        },
                        secondary: {
                            DEFAULT: "hsl(210 40% 96.1%)",
                            foreground: "hsl(222.2 47.4% 11.2%)",
                        },
                        destructive: {
                            DEFAULT: "hsl(0 84.2% 60.2%)",
                            foreground: "hsl(210 40% 98%)",
                        },
                        muted: {
                            DEFAULT: "hsl(210 40% 96.1%)",
                            foreground: "hsl(215.4 16.3% 46.9%)",
                        },
                        accent: {
                            DEFAULT: "hsl(210 40% 96.1%)",
                            foreground: "hsl(222.2 47.4% 11.2%)",
                        },
                        card: {
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(222.2 84% 4.9%)",
                        },
                    },
                    borderRadius: {
                        lg: "0.5rem",
                        md: "0.375rem",
                        sm: "0.25rem",
                    },
                }
            }
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8f9fa;
            margin: 0;
        }
        .sidebar {
            width: 260px;
            background: #1a1a1a;
            color: white;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            transition: width 0.3s ease;
            z-index: 100;
        }
        .sidebar.collapsed { width: 60px; }
        .sidebar.collapsed .sidebar-header h3,
        .sidebar.collapsed .sidebar-nav a span,
        .sidebar.collapsed .sidebar-footer .user-info,
        .sidebar.collapsed .sidebar-footer form button span { display: none; }
        .sidebar-toggle {
            position: absolute;
            right: -15px;
            top: 20px;
            background: #1a1a1a;
            color: white;
            border: 1px solid #333;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 101;
            transition: transform 0.3s;
        }
        .sidebar-toggle.collapsed { transform: rotate(180deg); }
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid #333;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-header .app-icon {
            width: 30px;
            height: 30px;
            background: #0d6efd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .sidebar-header h3 { margin: 0; font-size: 16px; font-weight: 600; white-space: nowrap; overflow: hidden; }
        .sidebar-nav { flex: 1; padding: 10px 0; overflow-y: auto; }
        .sidebar a {
            color: #a0a0a0;
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
        }
        .sidebar a:hover, .sidebar a.active { color: white; background: #2a2a2a; }
        .sidebar a i { width: 20px; text-align: center; flex-shrink: 0; }
        .sidebar a span { white-space: nowrap; overflow: hidden; }
        .sidebar-footer { border-top: 1px solid #333; padding: 15px 20px; }
        .sidebar-footer .user-info { color: #a0a0a0; font-size: 12px; margin-bottom: 10px; white-space: nowrap; overflow: hidden; }
        .sidebar-footer form button {
            color: #a0a0a0;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            width: 100%;
        }
        .sidebar-footer form button:hover { color: white; }
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            width: calc(100% - 260px);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        .main-content.expanded {
            margin-left: 60px;
            width: calc(100% - 60px);
        }
        
        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
        }
        .toast {
            background: #1a1a1a;
            color: white;
            padding: 16px 24px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
            font-weight: 500;
        }
        .toast-success { background: #16a34a; }
        .toast-error { background: #dc2626; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .badge-admin { background: #0d6efd; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        .badge-lambda { background: #6c757d; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        
        .page-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e5e5e5;
            width: 100%;
        }
        .page-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            width: 100%;
        }
        .page-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e5e5e5;
        }
        .page-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
        }
        .page-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        .card {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 20px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: #1a1a1a;
            color: white;
        }
        .btn-primary:hover {
            background: #333;
        }
        .btn-outline {
            background: white;
            border: 1px solid #e5e5e5;
            color: #333;
        }
        .btn-outline:hover {
            background: #f5f5f5;
        }
        .btn-danger {
            background: #dc2626;
            color: white;
        }
        .btn-sm {
            padding: 4px 12px;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e5e5;
        }
        .table th {
            background: #f5f5f5;
            font-weight: 600;
        }
        .input {
            padding: 8px 12px;
            border: 1px solid #e5e5e5;
            border-radius: 6px;
            width: 100%;
            font-size: 14px;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .badge-admin {
            background: #1a1a1a;
            color: white;
        }
        .badge-lambda {
            background: #e5e5e5;
            color: #333;
        }
        
        /* Editor specific */
        .editor-container {
            display: flex;
            height: calc(100vh - 80px);
            background: #000;
        }
        .editor-panel {
            width: 350px;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            flex-shrink: 0;
        }
        .preview-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #000;
            position: relative;
        }
        .preview-main {
            flex: 1;
            overflow: hidden;
        }
        .bottom-slides-bar {
            height: 120px;
            background: #1a1a1a;
            padding: 10px;
            overflow-x: auto;
            overflow-y: hidden;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }
        .bottom-slide-thumb {
            min-width: 140px;
            height: 90px;
            margin-right: 10px;
            background: #333;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
        }
        .bottom-slide-thumb.active {
            border-color: #0d6efd;
        }
        .bottom-slide-thumb img {
            width: 100%;
            height: 60px;
            object-fit: cover;
        }
        .bottom-slide-thumb .thumb-title {
            font-size: 10px;
            color: white;
            padding: 4px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            background: rgba(0,0,0,0.7);
        }
        
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; width: 100%; }
            .page-content { padding: 15px; }
            .page-header { padding: 15px; }
            .page-header h1 { font-size: 20px; }
            .card { padding: 15px; }
            .btn { padding: 6px 12px; font-size: 13px; }
            table { font-size: 13px; }
            .table th, .table td { padding: 8px; }
        }
        @media (max-width: 480px) {
            .page-content { padding: 10px; }
            .card { padding: 10px; }
            h1 { font-size: 18px; }
            h3 { font-size: 16px; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('mainSidebar');
            const content = document.getElementById('mainContent');
            const toggle = document.querySelector('.sidebar-toggle');
            
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                if (content) content.classList.toggle('expanded');
                toggle.classList.toggle('collapsed');
            }
        }
        
        function showToast(message, type = 'success') {
            console.log('showToast called:', message);
            const container = document.querySelector('.toast-container');
            if (!container) {
                console.log('No container found');
                return;
            }
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            console.log('Toast added:', toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        function createToastContainer() {
            const div = document.createElement('div');
            div.className = 'toast-container';
            document.body.appendChild(div);
            return div;
        }
    </script>
    <div class="toast-container"></div>
    @yield('content')
    
    @yield('scripts')
</body>
</html>
