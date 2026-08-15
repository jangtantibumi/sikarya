<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Northstar OS - CEO Workspace</title>
    <style>
        /* Premium Modal CSS (iOS 26.6 Style) */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
            z-index: 1000; align-items: center; justify-content: center;
            opacity: 0; animation: fadeIn 0.3s forwards;
        }
        .modal-content {
            background: var(--panel-glass);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--panel-glass-border);
            border-radius: 24px;
            padding: 32px;
            width: 90%; max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            transform: translateY(20px);
            animation: slideUp 0.3s cubic-bezier(0.25, 0.1, 0.25, 1) forwards;
            color: var(--text-main);
        }
        @keyframes fadeIn { to { opacity: 1; } }
        @keyframes slideUp { to { transform: translateY(0); } }

        /* Toast CSS */
        .ios-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: rgba(12, 53, 39, 0.9);
            color: var(--text-accent);
            padding: 14px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            backdrop-filter: blur(10px);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
        }
        .ios-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Custom File Upload Component CSS */
        .ios-file-upload {
            position: relative;
            border: 2px dashed rgba(12, 53, 39, 0.3);
            border-radius: 18px;
            background: rgba(217, 239, 233, 0.2);
            padding: 32px 16px;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 120px;
        }
        .ios-file-upload:hover, .ios-file-upload.drag-over {
            background: rgba(217, 239, 233, 0.5);
            border-color: var(--text-accent);
        }
        .ios-file-upload input[type="file"] {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer;
        }
        .ios-file-upload .upload-icon {
            font-size: 28px;
            color: var(--text-accent);
            margin-bottom: 12px;
        }
        .ios-file-upload .upload-text {
            font-size: 14px;
            color: var(--text-accent);
            font-weight: 600;
            margin: 0 0 4px 0;
        }
        .ios-file-upload .upload-subtext {
            font-size: 11px;
            color: var(--text-muted);
            margin: 0;
        }
        .ios-file-upload.has-file .upload-icon,
        .ios-file-upload.has-file .upload-text,
        .ios-file-upload.has-file .upload-subtext {
            display: none;
        }
        .ios-file-upload .file-preview {
            display: none;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            padding: 12px 16px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            z-index: 2; /* above the invisible file input */
            position: relative; /* relative to container */
        }
        .ios-file-upload.has-file .file-preview {
            display: flex;
        }
        .file-preview-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            overflow: hidden;
        }
        .file-preview-info i {
            color: #EF4444; /* PDF color typical */
            font-size: 24px;
        }
        .file-name-size {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            overflow: hidden;
            flex: 1;
        }
        .file-name {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .file-size {
            font-size: 11px;
            color: var(--text-muted);
        }
        .file-actions {
            display: flex;
            gap: 8px;
        }
        .file-action-btn {
            background: #f1f5f9;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }
        .file-action-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .file-action-btn.remove:hover {
            background: #fee2e2;
            color: #ef4444;
        }
        
        :root {
            --bg-main: #F8FAFC;
            --panel: #FFFFFF;
            --panel-secondary: #F1F5F9;
            --panel-border: #E2E8F0;
            --panel-glass: rgba(255, 255, 255, 0.85);
            --panel-glass-border: rgba(255, 255, 255, 0.6);
            --text-heading: #111827;
            --text-main: #374151;
            --text-muted: #6b7280;
            --accent: #0C3527;
            --accent-hover: #124836;
            --accent-active: #08261C;
            --secondary-surface: #D9EFE9;
            --disabled: #CBD5E1;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --accent-rgb: 12, 53, 39;
            --text-accent: #0C3527;
            --hover-bg: rgba(0, 0, 0, 0.04);
        }

        [data-theme="dark"] {
            --bg-main: #111111;
            --panel: #1a1a1a;
            --panel-secondary: #262626;
            --panel-border: rgba(255, 255, 255, 0.1);
            --panel-glass: rgba(26, 26, 26, 0.85);
            --panel-glass-border: rgba(255, 255, 255, 0.1);
            --text-heading: #f9fafb;
            --text-main: #e5e7eb;
            --text-muted: #9ca3af;
            --disabled: #374151;
            --text-accent: #D9EFE9;
            --hover-bg: rgba(255, 255, 255, 0.05);
        }

        [data-theme="dark"] .brand-logo { background-color: #ffffff !important; }

        .ios-btn, .btn {
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            letter-spacing: -0.2px;
            font-family: inherit;
        }
        .ios-btn:active, .btn:active { transform: scale(0.96); }
        .ios-btn-primary, .btn-primary, .btn { background: var(--accent); color: white; box-shadow: 0 4px 12px rgba(var(--accent-rgb), 0.2); }
        .ios-btn-primary:hover, .btn-primary:hover, .btn:hover { background: var(--accent-hover); box-shadow: 0 6px 16px rgba(var(--accent-rgb), 0.3); transform: translateY(-2px); color: white; }
        .ios-btn-secondary, .btn-secondary { background: var(--secondary-surface); color: var(--accent); box-shadow: none; }
        .ios-btn-secondary:hover, .btn-secondary:hover { background: #c2ded7; transform: translateY(-2px); color: var(--accent); }
        .ios-btn-danger, .btn-danger { background: var(--danger); color: white; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); }
        .ios-btn-danger:hover, .btn-danger:hover { background: #dc2626; transform: translateY(-2px); color: white; }
        
        .ios-input, .form-control {
            width: 100%; padding: 12px 16px;
            background: var(--panel-secondary);
            border: 1px solid var(--panel-border);
            border-radius: 12px; color: var(--text-heading);
            font-size: 14px; outline: none; transition: all 0.3s;
        }
        .ios-input:focus, .form-control:focus { box-shadow: 0 0 0 3px rgba(var(--accent-rgb), 0.15); border-color: var(--accent); }
        select option { background: var(--panel-secondary); color: var(--text-heading); }
        
        /* Modern Table CSS iOS 26.6 */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .table th {
            padding: 16px;
            color: var(--accent);
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--secondary-surface);
            text-align: left;
        }
        .table td {
            padding: 16px;
            background: var(--panel-secondary);
            border-top: 1px solid var(--panel-border);
            border-bottom: 1px solid var(--panel-border);
            transition: background 0.3s ease;
        }
        .table tr td:first-child {
            border-left: 1px solid var(--panel-border);
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
        }
        .table tr td:last-child {
            border-right: 1px solid var(--panel-border);
            border-top-right-radius: 16px;
            border-bottom-right-radius: 16px;
        }
        .table tr:hover td {
            background: rgba(var(--accent-rgb), 0.05);
        }
        
        .user-pill {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.25, 0.1, 0.25, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .user-pill:active { transform: scale(0.96); }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background-color: var(--bg-main);
            background-image: radial-gradient(circle at top right, rgba(var(--accent-rgb), 0.05), transparent 400px);
            color: var(--text-main);
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            overflow: hidden;
        }

        .layout {
            display: grid;
            grid-template-columns: 260px 1fr;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: var(--panel-secondary);
            border-right: 1px solid var(--panel-border);
            padding: 24px 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .brand {
            padding: 0 24px;
            margin-bottom: 32px;
        }
        .brand h1 {
            font-size: 20px;
            font-weight: 900;
            margin: 0;
            background: linear-gradient(to right, #D9EFE9, #0C3527);
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: 1px;
        }
        .brand small {
            color: var(--text-muted);
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            display: block;
            margin-top: 4px;
        }

        .nav-section {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin: 20px 24px 8px;
            font-weight: 700;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 10px 24px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .nav-item:hover {
            color: var(--text-main);
            background: var(--hover-bg);
        }
        .nav-item.active {
            color: var(--accent-active);
            background: rgba(12, 53, 39, 0.15);
            border-right: 3px solid var(--accent);
        }
        .nav-item i { margin-right: 12px; width: 20px; text-align: center; }

        /* Main Content */
        .main-view {
            padding: 32px 48px;
            overflow-y: auto;
            position: relative;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        .greeting h2 { margin: 0 0 4px 0; font-size: 28px; font-weight: 800; color: var(--text-heading); }
        .greeting p { margin: 0; color: var(--text-muted); }
        .user-pill {
            background: var(--panel-secondary);
            border: 1px solid var(--panel-border);
            padding: 8px 16px;
            border-radius: 99px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-heading);
        }
        .status-dot {
            width: 8px; height: 8px; border-radius: 50%; background: var(--success);
            box-shadow: 0 0 8px var(--success);
        }

        /* View Sections */
        .view-section { display: none; animation: fadeIn 0.3s ease; }
        .view-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* Dashboard Grid */
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        .grid-2 { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-bottom: 24px; }

        .card {
            background: var(--panel-glass);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--panel-glass-border);
            border-radius: 24px;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-color: rgba(var(--accent-rgb), 0.3);
        }
        .card h3 { margin: 0 0 16px 0; font-size: 14px; color: var(--text-heading); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;}
        .card .value { font-size: 32px; font-weight: 800; margin-bottom: 8px; color: var(--text-heading); }
        .card .trend { font-size: 13px; color: var(--success); display: flex; align-items: center; gap: 4px; }
        
        .card.interactive { border-left: 4px solid var(--accent); }

        /* Lists */
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid var(--panel-border);
        }
        .list-item:last-child { border-bottom: none; padding-bottom: 0; }
        .list-item .title { font-weight: 700; font-size: 15px; margin-bottom: 4px; color: var(--text-heading); }
        .list-item .desc { color: var(--text-muted); font-size: 13px; }

        /* Toggles */
        .toggle {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #374151; transition: .3s; border-radius: 34px;
        }
        .slider:before {
            position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
            background-color: white; transition: .3s; border-radius: 50%;
        }
        input:checked + .slider { background-color: var(--accent); }
        input:checked + .slider:before { transform: translateX(20px); }

        .loader {
            display: inline-block;
            width: 20px; height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Responsive CSS */
        @media (max-width: 1024px) {
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
            .grid-2 { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 768px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar {
                display: none; /* In a full implementation, we'd add a toggle button */
            }
            .sidebar.mobile-open {
                display: flex;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                z-index: 100;
            }
            .mobile-close { display: block !important; }
            .mobile-toggle { display: block !important; }
            .main-view { padding: 20px; }
            .top-bar { flex-direction: column; align-items: flex-start; gap: 16px; }
            .grid-4 { grid-template-columns: 1fr; }
        }
        .org-division-container {
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            background: rgba(18, 18, 18, 0.4);
            border: 1px solid rgba(255,255,255,0.05);
            position: relative;
        }
        .org-division-container:hover {
            box-shadow: 0 0 20px rgba(12, 53, 39,0.1);
            border-color: rgba(12, 53, 39,0.2);
        }
        .org-card {
            background: rgba(30, 30, 30, 0.7);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.08);
            position: relative;
            margin-bottom: 16px;
            transition: all 0.3s;
        }
        .org-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }
        .org-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin: 0 auto 12px auto;
        }
        .org-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin: 12px 0;
        }
        .org-badge {
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .badge-division { color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-role { color: #9ca3af; background: rgba(156, 163, 175, 0.1); border: 1px solid rgba(156, 163, 175, 0.3); }
        .badge-type { color: #9ca3af; background: rgba(156, 163, 175, 0.1); border: 1px solid rgba(156, 163, 175, 0.3); }
        .badge-task { color: var(--text-accent); background: rgba(12, 53, 39, 0.1); border: 1px solid rgba(12, 53, 39, 0.3); cursor: pointer;}
        .badge-task-active { color: var(--text-accent); background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); cursor: pointer;}
        
        .org-actions {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 16px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 12px;
        }
        .org-actions button {
            flex: 1;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-muted);
            padding: 6px;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .org-actions button:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .org-actions .btn-delete:hover {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }
        
        /* Organization Tree CSS */
        .org-tree {
            display: flex; justify-content: center; overflow-x: auto; padding-bottom: 20px;
        }
        .org-tree ul {
            padding-top: 20px; position: relative;
            transition: all 0.5s;
            display: flex; justify-content: center;
            padding-left: 0; list-style-type: none;
        }
        .org-tree li {
            float: left; text-align: center;
            list-style-type: none; position: relative;
            padding: 20px 8px 0 8px; transition: all 0.5s;
        }
        .org-tree li::before, .org-tree li::after{
            content: ''; position: absolute; top: 0; right: 50%;
            border-top: 2px solid rgba(12, 53, 39,0.4);
            width: 50%; height: 20px;
        }
        .org-tree li::after{
            right: auto; left: 50%;
            border-left: 2px solid rgba(12, 53, 39,0.4);
        }
        .org-tree li:only-child::after, .org-tree li:only-child::before { display: none; }
        .org-tree li:only-child{ padding-top: 0;}
        .org-tree li:first-child::before, .org-tree li:last-child::after{ border: 0 none; }
        .org-tree li:last-child::before{
            border-right: 2px solid rgba(12, 53, 39,0.4);
            border-radius: 0 5px 0 0;
        }
        .org-tree li:first-child::after{ border-radius: 5px 0 0 0; }
        .org-tree ul ul::before{
            content: ''; position: absolute; top: 0; left: 50%;
            border-left: 2px solid rgba(12, 53, 39,0.4);
            width: 0; height: 20px;
        }
        .org-tree .org-card { margin: 0 auto; width: 260px; }
            /* Chat UI matching Staff Account */
        .chat-layout { display: grid; grid-template-columns: 250px 1fr; gap: 20px; height: calc(100vh - 180px); }
        .chat-layout.has-announcements { grid-template-columns: 250px 1fr 350px; }
        .chat-channels { background: var(--panel-bg); border: 1px solid var(--panel-border); border-radius: 12px; overflow-y: auto; }
        .channel-item { padding: 12px 16px; border-bottom: 1px solid var(--panel-border); cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 600; color: var(--text-main); }
        .channel-item:hover, .channel-item.active { background: rgba(12, 53, 39,0.1); color: var(--primary); border-right: 3px solid var(--primary); }
        
        .chat-window { background: var(--panel-bg); border: 1px solid var(--panel-border); border-radius: 12px; display: flex; flex-direction: column; }
        .chat-header { padding: 16px; border-bottom: 1px solid var(--panel-border); font-weight: bold; display: flex; justify-content: space-between; color: var(--text-main); }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; }
        .message { display: flex; gap: 12px; max-width: 80%; }
        .message.mine { align-self: flex-end; flex-direction: row-reverse; }
        .msg-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--bg-main); border: 1px solid var(--panel-border); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; color: var(--primary); }
        .msg-bubble { background: var(--bg-main); border: 1px solid var(--panel-border); padding: 12px 16px; border-radius: 0 12px 12px 12px; color: var(--text-main); }
        .message.mine .msg-bubble { background: var(--primary); color: white; border: none; border-radius: 12px 0 12px 12px; }
        .msg-meta { font-size: 11px; color: var(--text-muted); margin-bottom: 4px; }
        
        .chat-input { padding: 16px; border-top: 1px solid var(--panel-border); display: flex; gap: 10px; background: var(--panel-bg); border-radius: 0 0 12px 12px; align-items: center; }
        .chat-input input[type="text"] { flex: 1; background: var(--bg-main); border: 1px solid var(--panel-border); border-radius: 20px; padding: 10px 16px; color: var(--text-main); outline: none; }
        .chat-input input[type="text"]:focus { border-color: var(--primary); }
        .chat-input button { background: var(--primary); color: var(--text-accent); border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
        .chat-input button:hover { opacity: 0.9; }
</style>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="<?php echo e(asset('css/finance.css')); ?>">
    
    <!-- Theme Initializer (Prevents FOUC) -->
    <script>
        (function() {
            var theme = localStorage.getItem('sikarya_theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
            var color = localStorage.getItem('sikarya_color');
            if(color) {
                document.documentElement.style.setProperty('--accent', color);
                document.documentElement.style.setProperty('--accent-hover', color);
                document.documentElement.style.setProperty('--accent-active', color);
            }
        })();
    </script>
</head>
<body>

<div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <button class="mobile-close" onclick="document.querySelector('.sidebar').classList.remove('mobile-open')" style="display:none; position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 24px; color: var(--text-muted); cursor: pointer;">&times;</button>
        <div class="brand">
            <div class="brand-logo" style="width: 170px; height: 45px; margin: 0 auto 10px auto; background-color: var(--accent); -webkit-mask: url('<?php echo e(asset('images/sikarya-logo.png')); ?>') no-repeat center; mask: url('<?php echo e(asset('images/sikarya-logo.png')); ?>') no-repeat center; -webkit-mask-size: contain; mask-size: contain;"></div>
            <small style="display: block; text-align: center; color: var(--text-muted);">Executive Workspace</small>
        </div>

                <div class="nav-section">Company Context</div>
        <div style="padding: 0 24px 16px; color: var(--text-heading); font-weight: 600;">
            <i class="fa-solid fa-building" style="color: var(--accent); margin-right: 8px;"></i>
            <?php echo e($company->name); ?>

        </div>

        <div class="nav-section">Dashboards</div>
        <a class="nav-item <?php echo e(request()->is('master-demo/app/overview') || request()->is('master-demo/app') ? 'active' : ''); ?>" href="<?php echo e(url('/master-demo/app/overview')); ?>" wire:navigate>
            <i class="fa-solid fa-chart-line"></i> Command Center
        </a>
        <a class="nav-item <?php echo e(request()->is('master-demo/app/organization') ? 'active' : ''); ?>" href="<?php echo e(url('/master-demo/app/organization')); ?>" wire:navigate>
            <i class="fa-solid fa-sitemap"></i> Organization Chart
        </a>
        <a class="nav-item" onclick="switchView('division-settings')">
            <i class="fa-solid fa-layer-group"></i> Pengaturan Divisi
        </a>
        
        <?php
            $iconMap = [
                'core_security' => 'fa-shield-halved',
                'core_workflow' => 'fa-network-wired',
                'people' => 'fa-users',
                'crm' => 'fa-users-viewfinder',
                'documents' => 'fa-file-signature',
                'project_costing' => 'fa-sack-dollar',
                'payroll' => 'fa-money-check-dollar',
                'alumni_network' => 'fa-user-graduate',
                'inventory' => 'fa-boxes-stacked',
                'purchasing' => 'fa-cart-flatbed',
                'production' => 'fa-industry',
                'pos' => 'fa-cash-register',
                'accounting' => 'fa-chart-bar',
                'client_portal' => 'fa-globe',
                'intelligence' => 'fa-brain',
                'report_builder' => 'fa-file-invoice',
                'auto_cogs' => 'fa-calculator',
                'material_request' => 'fa-boxes-packing',
                'purchase_request' => 'fa-file-circle-plus',
                'location_tracking' => 'fa-map-location-dot',
                'warning_letters' => 'fa-envelope-open-text',
                
                // New Roadmap Modules
                'hr_legal' => 'fa-scale-balanced',
                'hr_overtime' => 'fa-business-time',
                'hr_attendance_adv' => 'fa-user-clock',
                'performance_analytics' => 'fa-ranking-star',
                'task_routines' => 'fa-list-check',
                'task_approvals' => 'fa-check-double',
                'prod_automation' => 'fa-robot',
                'ga_asset_management' => 'fa-building-shield',
                'inventory_alerts' => 'fa-bell',
                'purchasing_hierarchy' => 'fa-sitemap',
                'cashier_reports' => 'fa-file-invoice-dollar',
                'chat_internal' => 'fa-comments',
                'announcements' => 'fa-bullhorn',
                'doc_archives' => 'fa-box-archive',
                'dashboard_manager' => 'fa-chart-pie',
                'master_data_center' => 'fa-database',
                'automation_engine' => 'fa-microchip',
            ];
            $activeFeatures = collect($features)->filter(fn($f) => $f['state'] !== 'off')->groupBy('group');
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activeFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $groupFeatures): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                // Get the division ID from the first feature in this group if available
                $firstFeature = $groupFeatures->first();
                $groupId = $firstFeature['division_id'] ?? 'uncategorized';
            ?>
            <div class="nav-section" id="sidebar-nav-section-<?php echo e($groupId); ?>"><?php echo e($groupName); ?></div>
            <div id="sidebar-nav-group-<?php echo e($groupId); ?>">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $groupFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php 
                    $isComingSoon = $f['state'] === 'coming_soon';
                    $style = $isComingSoon ? 'opacity: 0.5; cursor: not-allowed;' : '';
                    $onClick = $isComingSoon ? '' : "onclick=\"switchView('{$f['key']}')\"";
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($f['key'] === 'inventory' && !$isComingSoon): ?>
                    <a class="nav-item" data-target="inventory_umkm" id="sidebar-nav-item-<?php echo e($f['key']); ?>" onclick="switchView('inventory_umkm')">
                        <i class="fa-solid <?php echo e($iconMap[$f['key']] ?? 'fa-cube'); ?>"></i> <?php echo e($f['label']); ?>

                    </a>
                <?php elseif($f['key'] === 'master_data_center' && !$isComingSoon): ?>
                    <a class="nav-item" id="sidebar-nav-item-<?php echo e($f['key']); ?>" href="<?php echo e(route('masterdata.index')); ?>">
                        <i class="fa-solid <?php echo e($iconMap[$f['key']] ?? 'fa-cube'); ?>"></i> <?php echo e($f['label']); ?>

                    </a>
                <?php else: ?>
                    <a class="nav-item" id="sidebar-nav-item-<?php echo e($f['key']); ?>" <?php echo $onClick; ?> style="<?php echo e($style); ?>">
                        <i class="fa-solid <?php echo e($iconMap[$f['key']] ?? 'fa-cube'); ?>"></i> <?php echo e($f['label']); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isComingSoon): ?>
                            <span style="font-size: 9px; background: var(--panel-border); padding: 2px 4px; border-radius: 4px; margin-left: auto;">Dev</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div style="padding: 16px 24px; margin-top: auto;">
            <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'danger','icon' => 'fa-arrow-right-from-bracket','onclick' => 'confirmLogout()','style' => 'width: 100%; border-radius: 8px; padding: 10px 16px; font-size: 14px;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','icon' => 'fa-arrow-right-from-bracket','onclick' => 'confirmLogout()','style' => 'width: 100%; border-radius: 8px; padding: 10px 16px; font-size: 14px;']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Logout <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
        </div>
    </aside>

    <!-- Main View -->
    <main class="main-view">
    <main class="main-view">
        <?php echo e($slot); ?>

    </main>
</div>

<!-- Modal Edit User Profile -->
<div id="modal-edit-user" class="modal-overlay">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h4 style="margin: 0;">Edit Profil & Akses</h4>
            <button onclick="document.getElementById('modal-edit-user').style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 16px;">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('master-demo.hris.updateUser') ?? '#'); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="user_id" id="edit-user-id">
            <div class="form-group">
                <label>Nama Karyawan</label>
                <input type="text" id="edit-user-name" class="form-control" disabled>
            </div>
            <div style="display: flex; gap: 16px;">
                <div class="form-group" style="flex: 1;">
                    <label>Jabatan (Role)</label>
                    <input type="text" name="job_title" id="edit-user-job" class="form-control">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Tipe Pekerjaan</label>
                    <select name="employment_type" id="edit-user-type" class="form-control">
                        <option value="Full-Time">Full-Time</option>
                        <option value="Part-Time">Part-Time</option>
                        <option value="Contract">Contract</option>
                        <option value="Paid Internship">Paid Internship</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Target Jam Kerja per Bulan</label>
                <input type="number" step="1" name="target_hours_per_month" id="edit-user-target" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Reset Password (Opsional)</label>
                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin diubah">
            </div>
            <button type="submit" class="btn" style="width: 100%; justify-content: center; background: var(--accent);">Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- Modal Assign Role -->
<div id="modal-assign-role" class="modal-overlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 9999; flex-direction: column; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: var(--panel-bg); padding: 32px; border-radius: 24px; width: 400px; max-width: 90vw; border: 1px solid var(--panel-border);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0;">Tetapkan Akses Karyawan</h3>
            <button onclick="document.getElementById('modal-assign-role').style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 20px;">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('master-demo.security.assign')); ?>">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Karyawan</label>
                <select name="user_id" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); background: rgba(255,255,255,0.05); color: white;">
                    <option value="">Pilih Karyawan...</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $allUsers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?> (<?php echo e($u->job_title ?? 'Staff'); ?>)</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Role (Hak Akses)</label>
                <select name="role_key" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); background: rgba(255,255,255,0.05); color: white;">
                    <option value="">Pilih Role...</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($r->key); ?>"><?php echo e($r->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>
            <button type="submit" class="ios-btn" style="width: 100%;">Simpan Perubahan</button>
        </form>
    </div>
</div>

<!-- Modal Add Role -->
<div id="modal-add-role" class="modal-overlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 9999; flex-direction: column; justify-content: center; align-items: center;">
    <div class="modal-content" style="background: var(--panel-bg); padding: 32px; border-radius: 24px; width: 450px; max-width: 90vw; border: 1px solid var(--panel-border); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0;">Tambah Role Baru</h3>
            <button onclick="document.getElementById('modal-add-role').style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 20px;">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('master-demo.security.roles.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Nama Role</label>
                <input type="text" name="name" required placeholder="Misal: Manager HRD" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); background: rgba(255,255,255,0.05); color: white;">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Deskripsi Role</label>
                <input type="text" name="description" placeholder="Akses penuh ke modul HRIS..." style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); background: rgba(255,255,255,0.05); color: white;">
            </div>
            
            <h4 style="margin: 0 0 12px 0; font-size: 13px;">Otoritas Modul (Permissions)</h4>
            <div style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $features ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($f['state'] !== 'off'): ?>
                    <label style="display: flex; align-items: center; margin-bottom: 12px; cursor: pointer;">
                        <input type="checkbox" name="permissions[]" value="<?php echo e($f['key']); ?>" style="margin-right: 12px; width: 16px; height: 16px;">
                        <span style="font-size: 13px;"><?php echo e($f['label']); ?></span>
                    </label>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            
            <button type="submit" class="ios-btn" style="width: 100%;">Simpan Role Baru</button>
        </form>
    </div>
</div>

</body>
</html>

<!-- Global Confirm Modal -->
<?php if (isset($component)) { $__componentOriginal7762953202be6518eecd1cfbd075bf2f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7762953202be6518eecd1cfbd075bf2f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.modal','data' => ['id' => 'modal-confirm','title' => 'Konfirmasi','icon' => 'fa-triangle-exclamation','iconColor' => 'var(--danger)','formId' => 'confirm-form']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'modal-confirm','title' => 'Konfirmasi','icon' => 'fa-triangle-exclamation','iconColor' => 'var(--danger)','formId' => 'confirm-form']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <p id="confirm-msg" class="desc" style="margin-bottom: 24px;">Apakah Anda yakin?</p>
    <input type="hidden" name="_method" id="confirm-method" value="POST">
    <div style="display: flex; gap: 12px; justify-content: center;">
        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'secondary','type' => 'button','onclick' => 'document.getElementById(\'modal-confirm\').style.display=\'none\'','style' => 'flex:1;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','type' => 'button','onclick' => 'document.getElementById(\'modal-confirm\').style.display=\'none\'','style' => 'flex:1;']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Batal <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'danger','type' => 'submit','id' => 'confirm-btn']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','type' => 'submit','id' => 'confirm-btn']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Ya, Lanjutkan <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7762953202be6518eecd1cfbd075bf2f)): ?>
<?php $attributes = $__attributesOriginal7762953202be6518eecd1cfbd075bf2f; ?>
<?php unset($__attributesOriginal7762953202be6518eecd1cfbd075bf2f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7762953202be6518eecd1cfbd075bf2f)): ?>
<?php $component = $__componentOriginal7762953202be6518eecd1cfbd075bf2f; ?>
<?php unset($__componentOriginal7762953202be6518eecd1cfbd075bf2f); ?>
<?php endif; ?>

<script>
    function showToast(message) {
        let toast = document.getElementById('hrisToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'hrisToast';
            toast.className = 'ios-toast';
            toast.innerHTML = `<i class="fa-solid fa-check-circle" style="color: var(--text-accent);"></i> <span id="hrisToastMsg"></span>`;
            document.body.appendChild(toast);
        }
        document.getElementById('hrisToastMsg').innerText = message;
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 3000);
    }

    function handleFileSelect(input, containerId) {
        const container = document.getElementById(containerId);
        if (!container || !input) return;
        
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const fileName = file.name;
            let fileSize = (file.size / 1024).toFixed(1) + ' KB';
            if (file.size > 1024 * 1024) fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
            
            container.classList.add('has-file');
            const prefix = containerId === 'docUploadArea' ? 'doc' : 'payslip';
            document.getElementById(prefix + 'FileName').innerText = fileName;
            document.getElementById(prefix + 'FileSize').innerText = fileSize;
        } else {
            clearFileUpload(input.id, containerId);
        }
    }

    function clearFileUpload(inputId, containerId) {
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        if (input) input.value = '';
        if (container) container.classList.remove('has-file');
    }

    async function submitEditEmployee(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            const res = await fetch("<?php echo e(route('master-demo.hris.updateUser') ?? '#'); ?>", {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
            
            if(res.ok) {
                const id = document.getElementById('editEmpId').value;
                document.getElementById('emp-name-' + id).innerText = document.getElementById('editEmpName').value;
                document.getElementById('emp-email-' + id).innerText = document.getElementById('editEmpEmail').value;
                
                closeEditEmployeeModal();
                showToast('Data karyawan berhasil diperbarui!');
                setTimeout(() => window.location.reload(), 800);
            } else {
                alert('Gagal menyimpan data.');
            }
        } catch(err) {
            console.error(err);
        }
    }

    async function submitDeleteEmployee(e) {
        e.preventDefault();
        const id = document.getElementById('delEmpId').value;
        const token = document.querySelector('input[name="_token"]').value;
        
        try {
            const res = await fetch("/master-demo/employee/" + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ _method: 'DELETE' })
            });
            
            if(res.ok) {
                const row = document.getElementById('emp-row-' + id);
                if(row) row.remove();
                
                closeDeleteEmployeeModal();
                showToast('Karyawan dinonaktifkan.');
            } else {
                alert('Gagal menghapus karyawan.');
            }
        } catch (err) {
            console.error(err);
        }
    }
    
    function editUser(id, name, targetHours) {
        openEditProfileModal(id, name, '', 'Full-Time', targetHours);
    }
    
    function openEditProfileModal(id, name, job, type, target) {
        document.getElementById('edit-user-id').value = id;
        document.getElementById('edit-user-name').value = name;
        document.getElementById('edit-user-job').value = job;
        document.getElementById('edit-user-type').value = type;
        document.getElementById('edit-user-target').value = target;
        document.getElementById('modal-edit-user').style.display = 'flex';
    }
    
    function confirmDeleteUser(id) {
        document.getElementById('confirm-title').innerText = "Hapus Staf";
        document.getElementById('confirm-msg').innerText = "Apakah Anda yakin ingin menonaktifkan/menghapus staf ini dari sistem?";
        document.getElementById('confirm-form').action = "/master-demo/employee/" + id + "/delete"; 
        document.getElementById('modal-confirm').style.display = 'flex';
    }
    
    function confirmLogout() {
        document.getElementById('confirm-title').innerText = "Logout";
        document.getElementById('confirm-msg').innerText = "Apakah Anda yakin ingin keluar dari sistem?";
        document.getElementById('confirm-form').action = "/master-demo/logout"; 
        document.getElementById('modal-confirm').style.display = 'flex';
    }

    const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
    const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val);

    const viewMap = {
        'overview': { title: 'Command Center', sub: 'Real-time enterprise overview across all active modules.' },
        'crm': { title: 'CRM & Sales', sub: 'Pipeline management, quotations, and revenue tracking.' },
        'purchasing': { title: 'Purchasing', sub: 'Procurement flows, supplier relations, and pending goods.' },
        'inventory_umkm': { title: 'Gudang (Inventori UMKM)', sub: 'Pemantauan jumlah stok fisik dan batas minimal' },
        'production': { title: 'Production & QA', sub: 'Manufacturing processes and quality assurance metrics.' },
        'modules': { title: 'Module Controls', sub: 'Toggle enterprise capabilities for this tenant.' },
        'hris': { title: 'HRIS & Karyawan', sub: 'Pusat kendali SDM: Setup Jam Shif, File Perusahaan, Statistik Karyawan, dan Resign.' },
        'recipes': { title: 'Master Resep Produksi', sub: 'Otoritas Bill of Materials & Auto-Backflush.' },
        'chat_internal': { title: 'Internal Chat & Grup', sub: 'Komunikasi real-time dan aman antar karyawan dan grup.' }
    };

        function switchView(viewId) {
        if (viewId === 'purchasing' && typeof window.purchasingApp !== 'undefined') {
            window.purchasingApp.init();
        }
        if (viewId === 'crm') {
            window.location.href = "<?php echo e(route('crm.dashboard')); ?>";
            return;
        }
        localStorage.setItem('subaActiveView', viewId);

        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        
        const targetView = document.getElementById('view-' + viewId);
        if (targetView) targetView.classList.add('active');
        
        if (viewId === 'chat_internal') {
            loadMainDivisions();
            if(!currentChatChannel) selectChatChannel('general', 'Grup General');
        }

        if (viewId === 'payroll') {
            if (typeof loadPayrolls === 'function') loadPayrolls();
        }
        
        if (viewId === 'purchasing') {
            if (typeof window.purchasingApp !== 'undefined') window.purchasingApp.init();
        }


        const matchingNav = Array.from(document.querySelectorAll('.nav-item')).find(n => n.getAttribute('onclick') === `switchView('${viewId}')`);
        if (matchingNav) {
            matchingNav.classList.add('active');
            
            if(document.getElementById('view-title')) document.getElementById('view-title').innerText = viewMap[viewId] ? viewMap[viewId].title : viewId;
            if(document.getElementById('view-subtitle')) document.getElementById('view-subtitle').innerText = viewMap[viewId] ? viewMap[viewId].sub : 'Master Portal Management.';
        }
    }

    // Theme Management
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('sikarya_theme', next);
    }

    function setPrimaryColor(color) {
        document.documentElement.style.setProperty('--accent', color);
        document.documentElement.style.setProperty('--accent-hover', color);
        document.documentElement.style.setProperty('--accent-active', color);
        localStorage.setItem('sikarya_color', color);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Theme Controls
        const savedColor = localStorage.getItem('sikarya_color');
        if(savedColor) {
            const picker = document.getElementById('theme-color-picker');
            if(picker) picker.value = savedColor;
        }

        // Mock load data...
        setTimeout(() => {
            if(document.getElementById('metrics-crm-value')) document.getElementById('metrics-crm-value').innerHTML = 'Rp 14.5B';
            if(document.getElementById('metrics-po-value')) document.getElementById('metrics-po-value').innerHTML = '32';
            if(document.getElementById('metrics-qa-value')) document.getElementById('metrics-qa-value').innerHTML = '1.2%';
            if(document.getElementById('metrics-inv-value')) document.getElementById('metrics-inv-value').innerHTML = 'Rp 8.2B';
        }, 800);
        
        loadAnalytics();
    });

    // Fetch Analytics Data
    async function loadAnalytics() {
        try {
            const response = await fetch('/api/analytics/overview', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            // Populate Overview Cards
            if(data.crm) {
                if(document.getElementById('metrics-crm-value')) document.getElementById('metrics-crm-value').innerText = formatCurrency(data.crm.open_pipeline_value);
                if(document.getElementById('crm-total-leads')) document.getElementById('crm-total-leads').innerText = data.crm.total_leads;
                if(document.getElementById('crm-win-rate')) document.getElementById('crm-win-rate').innerText = data.crm.conversion_rate + '%';
                if(document.getElementById('crm-won-value')) document.getElementById('crm-won-value').innerText = formatCurrency(data.crm.won_value);
            }
            if(data.purchasing) {
                if(document.getElementById('metrics-po-value')) document.getElementById('metrics-po-value').innerText = data.purchasing.pending_receipts + ' Orders';
            }
            if(data.production) {
                if(document.getElementById('metrics-qa-value')) document.getElementById('metrics-qa-value').innerText = data.production.defect_rate + '%';
            }
            if(data.inventory) {
                if(document.getElementById('metrics-inv-value')) document.getElementById('metrics-inv-value').innerText = formatCurrency(data.inventory.estimated_valuation);
            }

            // Populate Alerts
            const alertsBox = document.getElementById('alerts-container');
            if(data.alerts && data.alerts.length > 0) {
                alertsBox.innerHTML = '';
                data.alerts.forEach(alert => {
                    const color = alert.severity === 'high' ? 'var(--danger)' : 'var(--warning)';
                    alertsBox.innerHTML += `
                        <div style="background: rgba(0,0,0,0.2); border-left: 4px solid ${color}; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-bottom: 10px;">
                            <strong style="display: block; font-size: 13px; margin-bottom: 4px; color: ${color};">${alert.title}</strong>
                            <span style="font-size: 13px; color: var(--text-muted);">${alert.message}</span>
                        </div>
                    `;
                });
            } else {
                alertsBox.innerHTML = '<div class="list-item"><span class="desc">No critical alerts at this time.</span></div>';
            }

        } catch (error) {
            console.error('Failed to load analytics', error);
            document.querySelectorAll('.loader').forEach(el => el.parentElement.innerText = 'Error loading data');
        }
    }

    // Module Toggle Logic (submits silently or reloads)
    function toggleModule(featureKey, isActive) {
        const state = isActive ? 'active' : 'off';
        const form = document.getElementById('module-form');
        document.getElementById('module-state').value = state;
        
        // Target route: /master-demo/companies/{company}/features/{feature}
        form.action = `/master-demo/companies/<?php echo e($company->id); ?>/features/${featureKey}`;
        form.submit();
    }

    // Initialize
    let activeView = localStorage.getItem('subaActiveView') || 'overview';
    if (activeView === 'chat_internal') {
        activeView = 'overview';
        localStorage.setItem('subaActiveView', 'overview');
    }
    switchView(activeView);

    document.addEventListener('DOMContentLoaded', () => {
        loadAnalytics();
    });
</script>
<!-- EDIT TASK MODAL -->
<div id="task-edit-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content ios-modal" style="width: 480px; max-width: 95vw; border-radius: 18px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid #D9EFE9; box-shadow: 0 20px 40px rgba(12, 53, 39, 0.2); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-accent);"><i class="fa-solid fa-pen-to-square" style="color: var(--text-accent); margin-right: 8px;"></i> Edit Tugas / Goal</h3>
            <button type="button" class="ios-btn-close" onclick="closePopup('task-edit-modal')" style="background: none; border: none; font-size: 18px; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-edit-task" onsubmit="submitEditTask(event)">
            <input type="hidden" id="edit-task-id" name="id">
            
            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Nama Karyawan</label>
                <select id="edit-task-user" name="user_id" class="ios-input" required style="width: 100%;" onchange="updateEditTaskDivision(this)">
                    <option value="">-- Pilih Karyawan --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $company->users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($u->id); ?>" data-division="<?php echo e($u->division ?? 'Tanpa Divisi'); ?>"><?php echo e($u->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Divisi</label>
                <input type="text" id="edit-task-division" class="ios-input" readonly placeholder="Divisi..." style="width: 100%; background: var(--panel-secondary); cursor: not-allowed;">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Deadline</label>
                <input type="date" id="edit-task-deadline" name="deadline" class="ios-input" required style="width: 100%;">
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Deskripsi Tugas</label>
                <input type="text" id="edit-task-title" name="title" class="ios-input" placeholder="Deskripsi tugas" required style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div class="form-group">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Status</label>
                    <select id="edit-task-status" name="status" class="ios-input" style="width: 100%;">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 600; display: block; margin-bottom: 6px;">Priority</label>
                    <select id="edit-task-priority" name="priority" class="ios-input" style="width: 100%;">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="ios-btn ios-btn-secondary" style="background: #D9EFE9; color: var(--text-accent);" onclick="closePopup('task-edit-modal')">Cancel</button>
                <button type="submit" class="ios-btn ios-btn-primary" style="background: #0C3527; color: white;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE TASK MODAL -->
<div id="task-delete-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content ios-modal" style="width: 440px; max-width: 95vw; border-radius: 18px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid #D9EFE9; box-shadow: 0 20px 40px rgba(12, 53, 39, 0.2); padding: 28px;">
        <div style="text-align: center;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: #D9EFE9; color: var(--text-accent); display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(12, 53, 39, 0.1);">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            
            <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; color: var(--text-accent);">Konfirmasi Hapus Tugas</h3>
            
            <div style="background: rgba(217, 239, 233, 0.4); border: 1px solid #D9EFE9; border-radius: 12px; padding: 14px; margin-bottom: 18px; text-align: left;">
                <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Tugas / Goal</div>
                <div id="del-task-title" style="font-size: 14px; font-weight: 700; color: var(--text-accent); margin-bottom: 8px;">-</div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 12px; border-top: 1px solid rgba(12, 53, 39, 0.1); padding-top: 8px;">
                    <div>
                        <span style="color: var(--text-muted); display: block; font-size: 10px;">Karyawan:</span>
                        <strong id="del-task-user" style="color: var(--text-accent);">-</strong>
                    </div>
                    <div>
                        <span style="color: var(--text-muted); display: block; font-size: 10px;">Divisi:</span>
                        <strong id="del-task-division" style="color: var(--text-accent);">-</strong>
                    </div>
                    <div style="grid-column: span 2;">
                        <span style="color: var(--text-muted); display: inline-block; font-size: 10px;">Deadline:</span>
                        <strong id="del-task-deadline" style="color: var(--text-accent);">-</strong>
                    </div>
                </div>
            </div>

            <p style="margin: 0 0 24px 0; font-size: 14px; color: var(--text-main); font-weight: 500;">Apakah Anda yakin ingin menghapus tugas ini?</p>

            <div style="display: flex; gap: 12px;">
                <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1; background: #D9EFE9; color: var(--text-accent); font-weight: 600;" onclick="closePopup('task-delete-modal')">Cancel</button>
                <button type="button" class="ios-btn ios-btn-danger" style="flex: 1; background: #ef4444; color: white; font-weight: 600;" onclick="confirmDeleteTask()">Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hapus Divisi -->
<div id="division-delete-modal" class="ios-modal-overlay" style="display:none; z-index: 10001; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div class="modal-content ios-modal" style="width: 400px; max-width: 95vw; border-radius: 18px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); border: 1px solid #ef4444; box-shadow: 0 20px 40px rgba(239, 68, 68, 0.15); padding: 28px;">
        <div style="text-align: center;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
                <i class="fa-solid fa-trash"></i>
            </div>
            
            <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; color: var(--text-accent);">Hapus Divisi</h3>
            <p style="margin: 0 0 24px 0; font-size: 14px; color: var(--text-main); font-weight: 500;">Menghapus divisi <strong id="del-div-name"></strong>? Modul di dalamnya akan dipindahkan ke Uncategorized.</p>

            <div style="display: flex; gap: 12px;">
                <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1; background: #f3f4f6; color: #374151; font-weight: 600;" onclick="closePopup('division-delete-modal')">Cancel</button>
                <button type="button" class="ios-btn ios-btn-danger" style="flex: 1; background: #ef4444; color: white; font-weight: 600;" onclick="confirmDeleteDivision()">Hapus Divisi</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Divisi -->
<div id="division-edit-modal" class="ios-modal-overlay" style="display:none; z-index: 10001; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div class="modal-content ios-modal" style="width: 400px; max-width: 95vw; border-radius: 18px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); padding: 28px;">
        <div style="text-align: left;">
            <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700; color: var(--text-accent);"><i class="fa-solid fa-pen" style="color: var(--accent); margin-right: 8px;"></i> Ubah Nama Divisi</h3>
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Nama Baru</label>
                <input type="text" id="edit-div-name-input" class="form-control" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); font-size: 15px;">
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1; background: #f3f4f6; color: #374151; font-weight: 600;" onclick="closePopup('division-edit-modal')">Cancel</button>
                <button type="button" class="ios-btn ios-btn-primary" style="flex: 1; font-weight: 600;" onclick="confirmRenameDivision()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Divisi -->
<div id="division-add-modal" class="ios-modal-overlay" style="display:none; z-index: 10001; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
    <div class="modal-content ios-modal" style="width: 400px; max-width: 95vw; border-radius: 18px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); padding: 28px;">
        <div style="text-align: left;">
            <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700; color: var(--text-accent);"><i class="fa-solid fa-users" style="color: var(--accent); margin-right: 8px;"></i> Tambah Divisi Baru</h3>
            <form onsubmit="submitNewDivision(event)">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Nama Divisi</label>
                    <input type="text" id="add-div-name-input" class="form-control" placeholder="Contoh: Marketing" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); font-size: 15px;" required>
                </div>
                <div class="form-group" style="margin-bottom: 24px;">
                    <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Kode Divisi (Opsional)</label>
                    <input type="text" id="add-div-code-input" class="form-control" placeholder="Contoh: MKT" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); font-size: 15px;">
                </div>
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1; background: #f3f4f6; color: #374151; font-weight: 600;" onclick="closePopup('division-add-modal')">Batal</button>
                    <button type="submit" class="ios-btn ios-btn-primary" style="flex: 1; font-weight: 600;">Simpan Divisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
        let currentDeleteTaskId = null;

    function openPopup(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'flex';
        }
    }

    function closePopup(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
        }
    }

    function showToast(message) {
        let toast = document.getElementById('global-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'global-toast';
            toast.className = 'ios-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #D9EFE9; font-size: 18px;"></i><span>${message}</span>`;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    function updateAssignTaskDivision(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const divisionInput = document.getElementById('assign-task-division');
        if (!selectElem.value) {
            divisionInput.value = '';
            return;
        }
        const division = selectedOption.getAttribute('data-division') || 'Tanpa Divisi';
        divisionInput.value = division;
    }

    function updateEditTaskDivision(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const divisionInput = document.getElementById('edit-task-division');
        if (!selectElem.value) {
            divisionInput.value = '';
            return;
        }
        const division = selectedOption.getAttribute('data-division') || 'Tanpa Divisi';
        divisionInput.value = division;
    }

    function handleStoreTask(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Tugas berhasil disimpan.');
                closePopup('task-add-modal');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Gagal menyimpan tugas.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan/server.');
        });
    }

    function closeEditTaskModal() {
        document.getElementById('task-edit-modal').style.display = 'none';
    }
    
    function closeDeleteTaskModal() {
        document.getElementById('task-delete-modal').style.display = 'none';
    }

    function openEditTaskModal(id, title, userId, deadline, status, priority) {
        document.getElementById('editTaskId').value = id;
        document.getElementById('editTaskTitle').value = title;
        document.getElementById('editTaskUser').value = userId;
        document.getElementById('editTaskDeadline').value = deadline;
        document.getElementById('editTaskStatus').value = status;
        document.getElementById('editTaskPriority').value = priority;
        document.getElementById('task-edit-modal').style.display = 'flex';
    }

    function submitEditTask(e) {
        e.preventDefault();
        const taskId = document.getElementById('editTaskId').value;
        const formData = new FormData(e.target);
        
        fetch(`/master-demo/tasks/${taskId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast('Tugas berhasil diperbarui.');
                closeEditTaskModal();
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert('Gagal mengupdate tugas.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        });
    }

    function openDeleteTaskModal(taskId, title, assigneeName, assigneeDiv, deadline) {
        currentDeleteTaskId = taskId;
        document.getElementById('delTaskTitle').innerText = title;
        document.getElementById('delTaskAssignee').innerText = 'Ditugaskan ke: ' + assigneeName + ' (' + assigneeDiv + ') • DL: ' + deadline;
        document.getElementById('task-delete-modal').style.display = 'flex';
    }

    function confirmDeleteTask() {
        if (!currentDeleteTaskId) return;

        fetch(`/master-demo/tasks/${currentDeleteTaskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Tugas berhasil dihapus.');
                closeDeleteTaskModal();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Gagal menghapus tugas.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan/server.');
        });
    }

    document.addEventListener('dragleave', function(ev) {
        const box = ev.target.closest('.division-box');
        if(box) {
            box.style.borderColor = box.getAttribute('data-id') ? 'transparent' : 'var(--danger)'; 
        }
    });

    function openNewDivisionModal() {
        const input = document.getElementById('add-div-name-input');
        if(input) input.value = '';
        const codeInput = document.getElementById('add-div-code-input');
        if(codeInput) codeInput.value = '';
        
        const modal = document.getElementById('division-add-modal');
        if(modal) {
            modal.style.display = 'flex';
        } else {
            console.error('Modal division-add-modal tidak ditemukan');
        }
    };

    async function submitNewDivision(e) {
        if(e) e.preventDefault();
        const name = document.getElementById('add-div-name-input').value;
        if(!name || name.trim() === '') return;
        const code = document.getElementById('add-div-code-input') ? document.getElementById('add-div-code-input').value : '';

        try {
            const response = await fetch('/api/divisions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name, code: code })
            });
            
            const result = await response.json();
            if(result.success) {
                closePopup('division-add-modal');
                showToast('Divisi berhasil ditambahkan.');
                setTimeout(() => window.location.reload(), 500); 
            } else {
                alert('Gagal membuat divisi: ' + (result.message || 'Unknown error'));
            }
        } catch(e) { console.error(e); alert('Gagal membuat divisi. Error: ' + e.message); }
    };

    let currentDivEditId = null;
    let currentDivDeleteId = null;

    function openRenameDivisionModal(id, currentName) {
        currentDivEditId = id;
        document.getElementById('edit-div-name-input').value = currentName;
        openPopup('division-edit-modal');
    }

    async function confirmRenameDivision() {
        const name = document.getElementById('edit-div-name-input').value;
        if(!name || name.trim() === '') return;
        const id = currentDivEditId;

        try {
            const response = await fetch(`/api/divisions/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name })
            });
            
            const result = await response.json();
            if(result.success) {
                document.getElementById('div-name-' + id).innerText = name;
                showToast('Nama divisi berhasil diubah.');
                closePopup('division-edit-modal');
                setTimeout(() => window.location.reload(), 500);
            }
        } catch(e) {
            console.error(e);
            alert('Gagal mengubah nama divisi.');
        }
    }

    function openDeleteDivisionModal(id, currentName) {
        currentDivDeleteId = id;
        document.getElementById('del-div-name').innerText = currentName;
        openPopup('division-delete-modal');
    }

    async function confirmDeleteDivision() {
        const id = currentDivDeleteId;
        try {
            const response = await fetch(`/api/divisions/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            if(result.success) {
                showToast('Divisi berhasil dihapus.');
                closePopup('division-delete-modal');
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(result.message || 'Gagal menghapus divisi karena masih ada modul di dalamnya.');
                closePopup('division-delete-modal');
            }
        } catch(e) {
            console.error(e);
            alert('Gagal menghapus divisi.');
        }
    }

    async function removeModuleFromDivision(featureKey) {
        if(!confirm('Keluarkan modul ini dari divisinya?')) return;
        
        try {
            const response = await fetch('/api/features/assign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    feature_key: featureKey,
                    division_id: null
                })
            });
            
            const result = await response.json();
            if(result.success) {
                showToast('Modul dikeluarkan dari divisi.');
                setTimeout(() => window.location.reload(), 300);
            }
        } catch(e) {
            console.error(e);
            alert('Gagal mengeluarkan modul.');
        }
    }

    function drag(ev) {
        ev.dataTransfer.setData("text/plain", ev.target.dataset.key);
        ev.dataTransfer.effectAllowed = "move";
    }

    function allowDrop(ev) {
        ev.preventDefault();
        ev.dataTransfer.dropEffect = "move";
        const box = ev.target.closest('.division-box');
        if(box) {
            box.style.borderColor = 'var(--accent)';
        }
    }

    async function drop(ev) {
        ev.preventDefault();
        const key = ev.dataTransfer.getData("text/plain");
        if (!key) return;
        
        const targetDivisionBox = ev.target.closest('.division-box');
        
        // Reset border styles
        document.querySelectorAll('.division-box').forEach(b => {
            b.style.borderColor = b.getAttribute('data-id') ? 'transparent' : 'var(--danger)'; 
        });

        if (!targetDivisionBox) return;
        
        const divisionId = targetDivisionBox.dataset.id || null;
        
        try {
            const res = await fetch('/api/features/assign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ feature_key: key, division_id: divisionId })
            });
            
            if (res.ok) {
                showToast('Modul berhasil dipindahkan');
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert('Gagal memindahkan modul');
            }
        } catch(e) { console.error(e); }
    }
</script>

<!-- CONFIRM REVOKE MODAL -->
<div id="modal-confirm-revoke" class="modal-overlay" style="display:none; z-index: 10000;">
    <div class="modal-content ios-modal" style="width: 400px; max-width: 90vw; border-radius: 18px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid #fee2e2; box-shadow: 0 20px 40px rgba(239, 68, 68, 0.15); padding: 32px 24px; text-align: center;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 48px; color: var(--danger); margin-bottom: 20px;"></i>
        <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 800; color: #111827;">Cabut Hak Akses?</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin: 0 0 24px 0; line-height: 1.5;">
            Anda yakin ingin mencabut seluruh hak akses dari <strong><span id="revoke-user-name"></span></strong>?<br>
            Pengguna tidak akan bisa mengakses modul sistem lagi sampai diberikan role baru.
        </p>
        <input type="hidden" id="revoke-user-id" value="">
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button class="ios-btn" style="flex: 1; background: #f1f5f9; color: #475569;" onclick="document.getElementById('modal-confirm-revoke').style.display='none'">Batal</button>
            <button class="ios-btn ios-btn-danger" style="flex: 1;" onclick="executeRevoke()">Ya, Cabut Akses</button>
        </div>
    </div>
</div>

<!-- Modal Create Custom Channel -->
<div id="modal-create-channel" class="modal-overlay" style="display:none; z-index: 10000; align-items: center; justify-content: center;">
    <div class="modal-content ios-modal" style="width: 500px; max-width: 90vw; border-radius: 18px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); box-shadow: 0 20px 40px rgba(0,0,0,0.1); padding: 32px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #000;">Buat Grup Kustom</h3>
            <button class="ios-btn ios-btn-icon" style="background: rgba(0,0,0,0.05); color: #000;" onclick="document.getElementById('modal-create-channel').style.display='none'"><i class="fa-solid fa-times"></i></button>
        </div>
        <form onsubmit="submitNewChannel(event)">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #333;">Nama Grup</label>
                <input type="text" id="new-channel-name" required placeholder="Contoh: Proyek Khusus A" style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--panel-border); background: var(--bg-main); outline: none; font-size: 14px; box-sizing: border-box;">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #333;">Deskripsi (Opsional)</label>
                <textarea id="new-channel-desc" placeholder="Tuliskan tujuan dari grup ini..." style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--panel-border); background: var(--bg-main); outline: none; font-size: 14px; box-sizing: border-box; min-height: 80px;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="ios-btn" style="background: rgba(0,0,0,0.05); color: #000; padding: 12px 24px; font-weight: 600;" onclick="document.getElementById('modal-create-channel').style.display='none'">Batal</button>
                <button type="submit" class="ios-btn ios-btn-primary" style="padding: 12px 24px; font-weight: 600;">Buat Grup</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Create Announcement -->
<div id="modal-create-announcement" class="modal-overlay" style="display:none; z-index: 10000; align-items: center; justify-content: center;">
    <div class="modal-content ios-modal" style="width: 500px; max-width: 90vw; border-radius: 18px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); box-shadow: 0 20px 40px rgba(0,0,0,0.1); padding: 32px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bullhorn" style="color: var(--primary);"></i> Buat Pengumuman Baru
            </h3>
            <button onclick="document.getElementById('modal-create-announcement').style.display='none'" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>
        
        <form onsubmit="submitAnnouncement(event)">
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Tujuan (Target Penerima)</label>
                <select id="announcement-target" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--panel-border); background: var(--bg-main); color: var(--text-main);">
                    <option value="all">Seluruh Karyawan</option>
                    <option value="managers">Seluruh Manager</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Judul Pengumuman</label>
                <input type="text" id="announcement-title" class="form-control" placeholder="Contoh: Libur Nasional Idul Fitri" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--panel-border); background: var(--bg-main); color: var(--text-main);">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Isi Pesan (Atau Link)</label>
                <textarea id="announcement-message" class="form-control" rows="4" placeholder="Ketik isi pengumuman secara detail di sini..." required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--panel-border); background: var(--bg-main); color: var(--text-main); resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="ios-btn" style="background: #f1f5f9; color: #475569;" onclick="document.getElementById('modal-create-announcement').style.display='none'">Batal</button>
                <button type="submit" class="ios-btn ios-btn-primary"><i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Siarkan Pengumuman</button>
            </div>
        </form>
    </div>
</div>

<script>
// --- CHAT & ANNOUNCEMENT LOGIC ---
let currentChatChannel = 'general';
let chatMessageInterval = null;
let currentChatAttachment = null;
const authUserId = <?php echo e(auth()->id() ?? 'null'); ?>;
const authUserName = '<?php echo e(auth()->user()->name ?? ""); ?>';

function selectChatChannel(channel, channelName) {
    currentChatChannel = channel;
    const headerTitle = document.getElementById('chat-header-title');
    if(headerTitle) headerTitle.innerText = channelName || channel;
    
    document.querySelectorAll('.channel-item').forEach(el => el.classList.remove('active'));
    const item = document.getElementById('chat-item-' + channel);
    if(item) item.classList.add('active');
    
    if(chatMessageInterval) clearInterval(chatMessageInterval);
    const container = document.getElementById('chat-history-container');
    if(container) container.innerHTML = '<div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 20px;">Memuat obrolan...</div>';
    
    fetchChatMessages();
    chatMessageInterval = setInterval(fetchChatMessages, 5000);
}

async function fetchChatMessages() {
    if(!currentChatChannel) return;
    try {
        const res = await fetch(`/master-demo/chat/${currentChatChannel}`);
        if(res.ok) {
            const messages = await res.json();
            renderChatMessages(messages);
        }
    } catch(e) { console.error('Error fetching chats', e); }
}

function renderChatMessages(messages) {
    const container = document.getElementById('chat-history-container');
    if(!container) return;
    
    if(!messages || messages.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 20px;">Belum ada pesan di grup ini.</div>';
        return;
    }
    
    let html = '';
    messages.forEach(m => {
        const isMe = m.sender_id === authUserId;
        const align = isMe ? 'flex-end' : 'flex-start';
        const bg = isMe ? 'var(--primary)' : 'var(--bg-main)';
        const color = isMe ? 'white' : 'var(--text-main)';
        const border = isMe ? 'none' : '1px solid var(--panel-border)';
        const radius = isMe ? '16px 16px 4px 16px' : '16px 16px 16px 4px';
        const nameDisplay = isMe ? '' : `<div style="font-size: 10px; font-weight: 600; color: var(--text-muted); margin-bottom: 4px;">${m.sender?.name || 'User'}</div>`;
        
        let attachmentHtml = '';
        if(m.type === 'file' && m.attachment_path) {
            attachmentHtml = `<div style="margin-top: 8px; font-size: 11px; padding: 6px 10px; background: rgba(0,0,0,0.1); border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;" onclick="window.open('${m.attachment_path}', '_blank')"><i class="fa-solid fa-file"></i> ${m.attachment_name || 'Attachment'}</div>`;
        }

        html += `
        <div style="display: flex; justify-content: ${align}; margin-bottom: 12px;">
            <div style="max-width: 75%;">
                ${nameDisplay}
                <div style="background: ${bg}; color: ${color}; padding: 10px 14px; border-radius: ${radius}; border: ${border}; font-size: 13px; line-height: 1.4; word-break: break-word;">
                    ${m.message || ''}
                    ${attachmentHtml}
                </div>
                <div style="font-size: 9px; color: var(--text-muted); text-align: ${isMe ? 'right' : 'left'}; margin-top: 4px;">
                    ${new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </div>
            </div>
        </div>`;
    });
    
    const isAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;
    container.innerHTML = html;
    if(isAtBottom) {
        container.scrollTop = container.scrollHeight;
    }
}

function handleMainAttachment(ev) {
    if(ev.target.files && ev.target.files[0]) {
        currentChatAttachment = ev.target.files[0];
        document.getElementById('main-attachment-name').innerText = currentChatAttachment.name;
        document.getElementById('main-attachment-preview').style.display = 'flex';
    }
}

function clearMainAttachment() {
    currentChatAttachment = null;
    document.getElementById('main-chat-attachment').value = '';
    document.getElementById('main-attachment-preview').style.display = 'none';
}

async function submitChatMessage(e) {
    e.preventDefault();
    const input = document.getElementById('chat-message-input');
    const msg = input.value.trim();
    if(!msg && !currentChatAttachment) return;
    
    const fileToSend = currentChatAttachment;
    input.value = '';
    clearMainAttachment();
    
    const formData = new FormData();
    formData.append('channel', currentChatChannel);
    if(msg) formData.append('message', msg);
    if(fileToSend) formData.append('attachment', fileToSend);
    
    try {
        const res = await fetch('/master-demo/chat', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        fetchChatMessages();
    } catch(err) {
        console.error(err);
        fetchChatMessages();
    }
}

// 2. Announcements Logic
async function submitAnnouncement(e) {
    e.preventDefault();
    const target = document.getElementById('announcement-target').value;
    const title = document.getElementById('announcement-title').value;
    const message = document.getElementById('announcement-message').value;
    
    const btn = e.target.querySelector('button[type="submit"]');
    const oriText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    btn.disabled = true;
    
    try {
        const res = await fetch('/master-demo/announcements', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ target: target, title: title, message: message })
        });
        
        if(res.ok) {
            document.getElementById('modal-create-announcement').style.display = 'none';
            // Clear form
            document.getElementById('announcement-title').value = '';
            document.getElementById('announcement-message').value = '';
            if(typeof showToast === 'function') showToast('Pengumuman berhasil disiarkan!');
            
            // Reload page to show new announcement in history without popup
            setTimeout(() => window.location.reload(), 500);
        } else {
            if(typeof showToast === 'function') showToast('Gagal membuat pengumuman.');
        }
    } catch(err) {
        console.error(err);
        if(typeof showToast === 'function') showToast('Terjadi kesalahan jaringan.');
    } finally {
        btn.innerHTML = oriText;
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Initial chat load
    fetchChannels();
    selectChatChannel('general', 'Grup General');
});

async function fetchChannels() {
    try {
        const res = await fetch('/master-demo/chat/channels/list');
        if(res.ok) {
            const data = await res.json();
            const container = document.getElementById('main-chat-divisions');
            if(!container) return;
            
            let html = '';
            if(data.divisions && data.divisions.length > 0) {
                data.divisions.forEach(d => {
                    const id = 'chat-item-' + d.name.replace(/\s+/g, '-');
                    html += `<div class="channel-item" onclick="selectChatChannel('${d.name}', 'Grup ${d.name}')" id="${id}">
                                <i class="fa-solid fa-users"></i> Grup ${d.name}
                             </div>`;
                });
            }
            
            if(data.custom && data.custom.length > 0) {
                html += `<div style="padding: 16px 16px 8px 16px; font-weight: bold; border-bottom: 1px solid var(--panel-border); color: var(--text-muted); font-size: 12px; border-top: 1px solid var(--panel-border);">Grup Kustom</div>`;
                data.custom.forEach(c => {
                    const id = 'chat-item-' + c.name.replace(/\s+/g, '-');
                    html += `<div class="channel-item" onclick="selectChatChannel('${c.name}', '${c.name}')" id="${id}">
                                <i class="fa-solid fa-hashtag"></i> ${c.name}
                             </div>`;
                });
            }
            
            container.innerHTML = html;
            
            // Re-apply active class if needed
            if(currentChatChannel) {
                document.querySelectorAll('.channel-item').forEach(el => el.classList.remove('active'));
                const activeEl = document.getElementById('chat-item-' + currentChatChannel.replace(/\s+/g, '-'));
                if(activeEl) activeEl.classList.add('active');
            }
        }
    } catch(err) { console.error('Error fetching channels', err); }
}

async function submitNewChannel(e) {
    e.preventDefault();
    const name = document.getElementById('new-channel-name').value;
    const desc = document.getElementById('new-channel-desc').value;
    
    const btn = e.target.querySelector('button[type="submit"]');
    const oriText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    btn.disabled = true;
    
    try {
        const res = await fetch('/master-demo/chat/channels', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name: name, description: desc })
        });
        if(res.ok) {
            document.getElementById('modal-create-channel').style.display = 'none';
            document.getElementById('new-channel-name').value = '';
            document.getElementById('new-channel-desc').value = '';
            fetchChannels(); 
            if(typeof showToast === 'function') showToast('Grup baru berhasil dibuat!');
        } else {
            if(typeof showToast === 'function') showToast('Gagal membuat grup');
        }
    } catch(err) {
        console.error(err);
        if(typeof showToast === 'function') showToast('Terjadi kesalahan jaringan');
    } finally {
        btn.innerHTML = oriText;
        btn.disabled = false;
    }
}
</script>

<?php if (isset($component)) { $__componentOriginal339c7fedf680433726dbafc2f156956f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal339c7fedf680433726dbafc2f156956f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.toast','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.toast'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal339c7fedf680433726dbafc2f156956f)): ?>
<?php $attributes = $__attributesOriginal339c7fedf680433726dbafc2f156956f; ?>
<?php unset($__attributesOriginal339c7fedf680433726dbafc2f156956f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal339c7fedf680433726dbafc2f156956f)): ?>
<?php $component = $__componentOriginal339c7fedf680433726dbafc2f156956f; ?>
<?php unset($__componentOriginal339c7fedf680433726dbafc2f156956f); ?>
<?php endif; ?>
<?php echo $__env->make('components.global-loading', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('components.chat-widget', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>



<?php /**PATH D:\suba-erp-master-local-latest\resources\views/components/layouts/app.blade.php ENDPATH**/ ?>