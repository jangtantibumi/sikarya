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
        <a class="nav-item active" onclick="switchView('overview')">
            <i class="fa-solid fa-chart-line"></i> Command Center
        </a>
        <a class="nav-item" onclick="switchView('organization')">
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
        <div class="top-bar">
            <div class="greeting" style="display: flex; align-items: center; gap: 16px;">
                <button class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('mobile-open')" style="background: none; border: none; color: var(--text-heading); font-size: 20px; cursor: pointer; display: none;">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h2 id="view-title">Command Center</h2>
                    <p id="view-subtitle">Real-time enterprise overview across all active modules.</p>
                </div>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <div class="theme-controls" style="display: flex; gap: 8px; align-items: center; border-right: 1px solid var(--panel-border); padding-right: 12px; margin-right: 4px;">
                    <input type="color" id="theme-color-picker" title="Kustomisasi Warna Utama" onchange="setPrimaryColor(this.value)" style="width: 28px; height: 28px; border: none; border-radius: 50%; cursor: pointer; background: transparent; overflow: hidden; padding: 0;">
                    <button onclick="toggleTheme()" title="Ubah Mode Gelap/Terang" style="background: transparent; border: 1px solid var(--panel-border); color: var(--text-heading); width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                    </button>
                </div>
                <div class="user-pill">
                    <div class="status-dot"></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->profile_picture_path): ?>
                        <img src="/storage/<?php echo e(auth()->user()->profile_picture_path); ?>" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php echo e(auth()->user()->name); ?> (<?php echo e(auth()->user()->job_title); ?>)
                </div>
            </div>
        </div>

        <!-- OVERVIEW VIEW -->
        <section id="view-overview" class="view-section active">
            <?php
                $latestAnnouncement = \App\Models\Announcement::where('is_active', true)->latest()->first();
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($latestAnnouncement): ?>
                <div style="background: linear-gradient(135deg, #4f46e5, #0C3527); color: white; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 16px; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);">
                    <i class="fa-solid fa-bullhorn" style="font-size: 24px; margin-top: 4px;"></i>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: white; border: none; padding: 0;"><?php echo e($latestAnnouncement->title); ?></h3>
                        <div style="font-size: 14px; line-height: 1.5; opacity: 0.9;"><?php echo e($latestAnnouncement->content); ?></div>
                        <div style="font-size: 11px; margin-top: 12px; opacity: 0.7;">Disiarkan oleh Management &bull; <?php echo e($latestAnnouncement->created_at->diffForHumans()); ?></div>
                    </div>
                </div>

                <!-- Announcement Popup Modal -->
                <div id="modal-announcement-popup" class="modal-overlay" style="display:none; z-index: 10000; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); align-items: center; justify-content: center;">
                    <div class="modal-content ios-modal" style="width: 500px; max-width: 90vw; padding: 32px 24px;">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(79, 70, 229, 0.1); color: #4f46e5; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                            <h2 style="margin: 0; font-size: 22px; font-weight: 800; color: #0f172a;">Pengumuman Baru</h2>
                        </div>
                        <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; color: #1e293b; text-align: center;"><?php echo e($latestAnnouncement->title); ?></h3>
                        <div style="font-size: 14px; color: #475569; line-height: 1.6; margin-bottom: 24px; text-align: center; background: #f8fafc; padding: 16px; border-radius: 12px;">
                            <?php echo e($latestAnnouncement->content); ?>

                        </div>
                        <button class="ios-btn ios-btn-primary" style="width: 100%;" onclick="dismissAnnouncementPopup('<?php echo e($latestAnnouncement->id); ?>')">Saya Mengerti</button>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const announcementId = '<?php echo e($latestAnnouncement->id); ?>';
                        const dismissed = localStorage.getItem('announcement_dismissed_' + announcementId);
                        if (!dismissed) {
                            setTimeout(() => {
                                document.getElementById('modal-announcement-popup').style.display = 'flex';
                            }, 500);
                        }
                    });
                    function dismissAnnouncementPopup(id) {
                        localStorage.setItem('announcement_dismissed_' + id, 'true');
                        document.getElementById('modal-announcement-popup').style.display = 'none';
                    }
                </script>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="grid-4">
                <div class="card" style="opacity: 0.5; cursor: not-allowed;" title="Module coming soon">
                    <h3>CRM Pipeline</h3>
                    <div class="value" id="metrics-crm-value"><div class="loader"></div></div>
                    <div class="trend" style="color: var(--text-muted);"><i class="fa-solid fa-tools"></i> Module in Development</div>
                </div>
                <div class="card" style="opacity: 0.5; cursor: not-allowed;" title="Module coming soon">
                    <h3>Pending Payables</h3>
                    <div class="value" id="metrics-po-value"><div class="loader"></div></div>
                    <div class="trend" style="color: var(--text-muted);"><i class="fa-solid fa-tools"></i> Module in Development</div>
                </div>
                <div class="card" style="opacity: 0.5; cursor: not-allowed;" title="Module coming soon">
                    <h3>Production Quality</h3>
                    <div class="value" id="metrics-qa-value"><div class="loader"></div></div>
                    <div class="trend" style="color: var(--text-muted);"><i class="fa-solid fa-tools"></i> Module in Development</div>
                </div>
                <div class="card interactive" onclick="switchView('inventory_umkm')" style="cursor:pointer;">
                    <h3>Asset Valuation</h3>
                    <div class="value" id="metrics-inv-value"><div class="loader"></div></div>
                    <div class="trend"><i class="fa-solid fa-boxes-stacked"></i> Go to Inventory & Warehouse</div>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <h3>Executive Alerts</h3>
                    <div id="alerts-container">
                        <div class="loader"></div> Fetching system alerts...
                    </div>
                </div>
                <div class="card">
                    <h3>System Modules</h3>
                    <div style="margin-top: 10px;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = collect($features)->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="list-item">
                                <div>
                                    <div class="title"><?php echo e($mod['label']); ?></div>
                                    <div class="desc"><?php echo e($mod['group']); ?></div>
                                </div>
                                <div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mod['state'] === 'active'): ?>
                                        <span style="color: var(--success); font-weight: bold; font-size: 12px;">ACTIVE</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 12px;"><?php echo e(strtoupper($mod['state'])); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div style="margin-top: 16px; text-align: center;">
                            <a href="#" onclick="switchView('modules')" style="color: var(--accent); text-decoration: none; font-size: 13px; font-weight: bold;">View All Modules &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0;"><i class="fa-solid fa-chart-line" style="color: var(--accent); margin-right: 8px;"></i> Tren Kehadiran Mingguan (Agregat)</h3>
                    <button class="ios-btn ios-btn-secondary" style="font-size: 11px; padding: 6px 12px;" onclick="switchView('people')">Manajemen HR &rarr;</button>
                </div>
                <div style="height: 180px; display: flex; align-items: flex-end; gap: 16px; padding: 16px 24px 32px 24px; border-bottom: 1px solid var(--panel-border); background: var(--bg); border-radius: 8px;">
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--accent); height: 75%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(12, 53, 39, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Sen</span>
                        <span style="position: absolute; top: 15%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">75%</span>
                    </div>
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--accent); height: 85%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(12, 53, 39, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Sel</span>
                        <span style="position: absolute; top: 5%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">85%</span>
                    </div>
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--accent); height: 92%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(12, 53, 39, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Rab</span>
                        <span style="position: absolute; top: -5%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">92%</span>
                    </div>
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: #ef4444; height: 60%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(239, 68, 68, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Kam</span>
                        <span style="position: absolute; top: 30%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">60%</span>
                    </div>
                    <div style="flex: 1; background: var(--panel-secondary); border-radius: 4px 4px 0 0; position: relative; height: 100%;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: var(--accent); height: 95%; border-radius: 4px 4px 0 0; transition: height 1s; box-shadow: 0 -4px 10px rgba(12, 53, 39, 0.2);"></div>
                        <span style="position: absolute; bottom: -28px; left: 0; right: 0; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: bold;">Jum</span>
                        <span style="position: absolute; top: -8%; left: 0; right: 0; text-align: center; font-size: 11px; color: white; font-weight: bold; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">95%</span>
                    </div>
                </div>
            </div>

        </section>

        <script>
            async function deleteAnnouncement(id) {
                if(!confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) return;
                try {
                    const res = await fetch('/master-demo/announcements/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    if(res.ok) {
                        alert('Pengumuman berhasil dihapus!');
                        location.reload();
                    } else {
                        alert('Gagal menghapus pengumuman.');
                    }
                } catch(e) { console.error(e); }
            }

            async function bulkDeleteAnnouncements(period) {
                let pStr = period === 'daily' ? '1 Hari' : (period === 'weekly' ? '1 Minggu' : '1 Bulan');
                if(!confirm('Anda akan menghapus SEMUA pengumuman yang lebih lama dari ' + pStr + '. Tindakan ini tidak bisa dibatalkan. Lanjutkan?')) return;
                try {
                    const res = await fetch('/master-demo/announcements/bulk-delete', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify({ period: period })
                    });
                    if(res.ok) {
                        const data = await res.json();
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Gagal menghapus pengumuman.');
                    }
                } catch(e) { console.error(e); }
            }
        </script>

        <!-- DIVISION SETTINGS VIEW -->
        <section id="view-division-settings" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h3 style="margin: 0; margin-bottom: 4px;">Pengaturan Divisi & Modul</h3>
                    <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Ganti nama divisi atau seret-dan-lepas (Drag & Drop) modul antar divisi.</p>
                </div>
                <button onclick="openNewDivisionModal()" style="padding: 8px 16px; border-radius: 6px; background: var(--accent); color: white; border: none; font-weight: bold; cursor: pointer;"><i class="fa-solid fa-plus"></i> Tambah Divisi</button>
            </div>
            
            <div class="division-container-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $div): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="division-box card" data-id="<?php echo e($div->id); ?>" style="padding: 16px; min-height: 200px; display: flex; flex-direction: column;" ondragover="allowDrop(event)" ondrop="drop(event)">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--panel-border); padding-bottom: 12px; margin-bottom: 16px;">
                            <h4 style="margin: 0; font-size: 15px;" id="div-name-<?php echo e($div->id); ?>"><?php echo e($div->name); ?></h4>
                            <div style="display: flex; gap: 6px;">
                                <button onclick="openRenameDivisionModal(<?php echo e($div->id); ?>, '<?php echo e(addslashes($div->name)); ?>')" style="background: var(--bg); border: 1px solid var(--panel-border); color: var(--text-heading); cursor: pointer; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><i class="fa-solid fa-pen"></i> Ubah</button>
                                <button onclick="openDeleteDivisionModal(<?php echo e($div->id); ?>, '<?php echo e(addslashes($div->name)); ?>')" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; cursor: pointer; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                        
                        <div class="module-list" id="module-list-<?php echo e($div->id); ?>" style="display: flex; flex-direction: column; gap: 8px; min-height: 100px; flex: 1;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = collect($features)->where('division_id', $div->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="module-item" draggable="true" ondragstart="drag(event)" id="module-<?php echo e($f['key']); ?>" data-key="<?php echo e($f['key']); ?>" style="background: var(--bg); border: 1px solid var(--panel-border); padding: 12px; border-radius: 6px; cursor: grab; display: flex; align-items: center; gap: 12px; font-size: 14px;">
                                    <i class="fa-solid fa-grip-vertical" style="color: var(--text-muted); cursor: grab;"></i>
                                    <div style="flex: 1; display: flex; justify-content: space-between; align-items: center;">
                                        <span><i class="fa-solid <?php echo e($iconMap[$f['key']] ?? 'fa-cube'); ?>" style="color: var(--accent); width: 16px; text-align: center;"></i> <?php echo e($f['label']); ?></span>
                                        <button onclick="removeModuleFromDivision('<?php echo e($f['key']); ?>')" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 4px; font-size: 12px; opacity: 0.5;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.5" title="Keluarkan modul dari divisi ini"><i class="fa-solid fa-times"></i></button>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                
                <!-- Uncategorized Container -->
                <div class="division-box card" data-id="" style="padding: 16px; min-height: 200px; border: 1px dashed var(--danger); display: flex; flex-direction: column;" ondragover="allowDrop(event)" ondrop="drop(event)">
                    <div style="border-bottom: 1px solid var(--panel-border); padding-bottom: 12px; margin-bottom: 16px;">
                        <h4 style="margin: 0; font-size: 15px; color: var(--danger);">Uncategorized / System</h4>
                    </div>
                    
                    <div class="module-list" id="module-list-uncategorized" style="display: flex; flex-direction: column; gap: 8px; min-height: 100px; flex: 1;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = collect($features)->where('division_id', null); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="module-item" draggable="true" ondragstart="drag(event)" id="module-<?php echo e($f['key']); ?>" data-key="<?php echo e($f['key']); ?>" style="background: var(--bg); border: 1px solid var(--panel-border); padding: 12px; border-radius: 6px; cursor: grab; display: flex; align-items: center; gap: 12px; font-size: 14px;">
                                <i class="fa-solid fa-grip-vertical" style="color: var(--text-muted); cursor: grab;"></i>
                                <i class="fa-solid <?php echo e($iconMap[$f['key']] ?? 'fa-cube'); ?>" style="color: var(--danger);"></i> <?php echo e($f['label']); ?>

                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        

        <!-- SALES & CRM VIEW -->
        <section id="view-crm" class="view-section">
            <div class="grid-4">
                <?php
                    $totalSalesCount = \App\Models\PosSale::where('company_id', $company->id)->count();
                    $totalRevenue = \App\Models\PosSale::where('company_id', $company->id)->sum('total_amount');
                    $todayRevenue = \App\Models\PosSale::where('company_id', $company->id)->whereDate('created_at', today())->sum('total_amount');
                ?>
                <div class="card">
                    <h3>Total Sales (Invoices)</h3>
                    <div class="value" id="crm-total-leads"><?php echo e(number_format($totalSalesCount)); ?> <span style="font-size: 12px; color: var(--text-muted);">Trx</span></div>
                </div>
                <div class="card">
                    <h3>Today's Revenue</h3>
                    <div class="value" id="crm-win-rate" style="color: var(--warning)">Rp <?php echo e(number_format($todayRevenue, 0, ',', '.')); ?></div>
                </div>
                <div class="card" style="grid-column: span 2;">
                    <h3>Total Accumulated Revenue</h3>
                    <div class="value" id="crm-won-value" style="color: var(--success)">Rp <?php echo e(number_format($totalRevenue, 0, ',', '.')); ?></div>
                </div>
            </div>
            <div class="card">
                <h3>Sales Transactions Log</h3>
                <p class="muted">Setiap transaksi POS otomatis terekam di sini dan memotong stok gudang barang jadi.</p>
                
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 16px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--panel-border); color: var(--text-muted); text-align: left;">
                            <th style="padding: 12px 8px;">No. Struk</th>
                            <th style="padding: 12px 8px;">Waktu</th>
                            <th style="padding: 12px 8px;">Metode</th>
                            <th style="padding: 12px 8px;">Kasir</th>
                            <th style="padding: 12px 8px; text-align: right;">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\PosSale::where('company_id', $company->id)->latest()->limit(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 12px 8px; font-weight: bold; color: var(--accent);"><?php echo e($sale->receipt_number); ?></td>
                            <td style="padding: 12px 8px;"><?php echo e($sale->created_at->format('d M Y, H:i')); ?></td>
                            <td style="padding: 12px 8px;"><span style="text-transform: uppercase;"><?php echo e($sale->payment_method); ?></span></td>
                            <td style="padding: 12px 8px;"><?php echo e($sale->creator?->name); ?></td>
                            <td style="padding: 12px 8px; text-align: right; font-weight: bold; color: var(--success);">Rp <?php echo e(number_format($sale->total_amount, 0, ',', '.')); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php echo $__env->make('organization.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- MODULE CONTROLS VIEW (REPLACED WITH RBAC) -->
        <section id="view-core_security" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
                <div>
                    <h2 style="margin: 0;">Security, RBAC & Audit</h2>
                    <p style="color: var(--text-muted); margin: 5px 0 0;">Pengaturan Hak Akses (Role-Based Access Control), Log Audit, dan Kemanan Sistem.</p>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div>
                        <h3 style="margin:0;">User Roles & Permissions</h3>
                        <p class="desc" style="margin: 4px 0 0 0;">Konfigurasi grup akses spesifik per karyawan untuk membatasi visibilitas modul dan data.</p>
                    </div>
                    <div>
                        <button class="ios-btn" onclick="document.getElementById('modal-assign-role').style.display='flex'"><i class="fa-solid fa-user-plus"></i> Assign User</button>
                    </div>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="list-item" style="cursor:pointer;" onclick="const d = document.getElementById('role-users-<?php echo e($r->key); ?>'); if(d) { d.style.display = (d.style.display === 'none' || d.style.display === '') ? 'block' : 'none'; }">
                    <div>
                        <div class="title"><?php echo e($r->name); ?></div>
                        <div class="desc"><?php echo e($r->description); ?></div>
                    </div>
                    <div>
                        <span style="background: rgba(12, 53, 39,0.1); color: var(--success); padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: bold;"><?php echo e(count($r->users)); ?> Users</span>
                        <i class="fa-solid fa-chevron-down" style="font-size:12px; color:var(--text-muted); margin-left: 8px;"></i>
                    </div>
                </div>
                <!-- Spoiler Users for this Role -->
                                <div id="role-users-<?php echo e($r->key); ?>" style="display:none; background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--panel-border); padding: 12px 16px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($r->users) > 0): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $r->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php echo $__env->make('security.user-authority', ['user' => $u, 'role' => $r], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php else: ?>
                        <div style="font-size: 12px; color: var(--text-muted); text-align: center;">Tidak ada pengguna dengan peran ini.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                
                <div style="margin-top: 16px;">
                    <button class="ios-btn ios-btn-secondary" onclick="document.getElementById('modal-add-role').style.display='flex'"><i class="fa-solid fa-plus"></i> Tambah Role Baru</button>
                </div>
            </div>
            
                                                            <div class="card" style="min-height: 500px; padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h3 style="margin: 0; font-size: 18px; font-weight: 700;">System Audit Log</h3>
                        <p class="desc" style="margin: 4px 0 0 0; color: var(--text-muted); font-size: 13px;">Rekam jejak seluruh aktivitas sistem secara real-time.</p>
                    </div>
                    
                    <!-- Advanced Filters & Search (iOS Style) -->
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check() && (auth()->user()->isCEO() || auth()->user()->isPlatformAdmin())): ?>
                        <button onclick="confirmClearAuditLog()" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--danger); padding: 9px 14px; border-radius: 12px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.2)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
                            <i class="fa-solid fa-trash-can"></i> Bersihkan Log
                        </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <!-- Search Box -->
                        <div style="position: relative; width: 240px;">
                            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px;"></i>
                            <input type="text" id="audit-search" placeholder="Cari user, modul, action..." 
                                style="width: 100%; padding: 10px 14px 10px 40px; border-radius: 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); font-size: 13px; outline: none; transition: all 0.2s;"
                                onkeyup="if(event.key === 'Enter') loadAuditLogs()">
                        </div>

                          <!-- Segmented Control -->
                          <div style="background: rgba(120,120,128,0.12); padding: 3px; border-radius: 10px; display: inline-flex; font-weight: 500; font-size: 13px;">
                              <button type="button" class="audit-filter-btn active" data-range="today" onclick="setAuditFilter('today', this)" style="border: none; background: var(--primary); box-shadow: 0 3px 8px rgba(0,0,0,0.2); border-radius: 8px; padding: 6px 16px; color: white; font-weight: 700; cursor: pointer; transition: all 0.2s;">Hari Ini</button>
                              <button type="button" class="audit-filter-btn" data-range="week" onclick="setAuditFilter('week', this)" style="border: none; background: transparent; padding: 6px 16px; color: var(--text-muted); font-weight: 500; cursor: pointer; transition: all 0.2s; border-radius: 8px;">7 Hari</button>
                              <button type="button" class="audit-filter-btn" data-range="30_days" onclick="setAuditFilter('30_days', this)" style="border: none; background: transparent; padding: 6px 16px; color: var(--text-muted); font-weight: 500; cursor: pointer; transition: all 0.2s; border-radius: 8px;">30 Hari</button>
                              <button type="button" class="audit-filter-btn" data-range="month" onclick="setAuditFilter('month', this)" style="border: none; background: transparent; padding: 6px 16px; color: var(--text-muted); font-weight: 500; cursor: pointer; transition: all 0.2s; border-radius: 8px;">Bulan Ini</button>
                          </div>

                        <!-- Sort Dropdown -->
                        <select id="audit-sort" onchange="loadAuditLogs()" style="padding: 9px 14px; border-radius: 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); font-size: 13px; cursor: pointer; outline: none;">
                            <option value="desc">Newest First</option>
                            <option value="asc">Oldest First</option>
                        </select>
                        
                    </div>
                </div>

                <!-- Custom Date Range -->
                <div id="audit-custom-range" style="display: none; justify-content: flex-end; gap: 8px; align-items: center; margin-bottom: 20px;">
                    <input type="date" id="audit-date" style="border: 1px solid var(--panel-border); border-radius: 8px; background: var(--panel-bg); font-size: 12px; padding: 6px 12px; color: var(--text-main);" onchange="loadAuditLogs()">
                    <span style="color: var(--text-muted); font-size: 12px;">Pukul</span>
                    <input type="time" id="audit-time-start" style="border: 1px solid var(--panel-border); border-radius: 8px; background: var(--panel-bg); font-size: 12px; padding: 6px 12px; color: var(--text-main);" onchange="loadAuditLogs()">
                    <span style="color: var(--text-muted); font-size: 12px;">-</span>
                    <input type="time" id="audit-time-end" style="border: 1px solid var(--panel-border); border-radius: 8px; background: var(--panel-bg); font-size: 12px; padding: 6px 12px; color: var(--text-main);" onchange="loadAuditLogs()">
                </div>

                <!-- List Header -->
                <div style="display: grid; grid-template-columns: 200px 150px 150px 200px minmax(200px, 1fr) 120px; gap: 16px; padding: 12px 16px; border-bottom: 1px solid var(--panel-border); color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    <div>Waktu</div>
                    <div>Aktor (User)</div>
                    <div>Modul/Target</div>
                    <div>Aktivitas</div>
                    <div>Detail</div>
                    <div>IP Address</div>
                </div>

                <!-- Timeline / Data Container -->
                <div id="audit-log-container" style="position: relative; min-height: 200px; padding-top: 8px;">
                    <div style="text-align: center; padding: 40px 0;">
                        <div class="loader"></div> <span style="margin-top: 12px; display: block; color: var(--text-muted); font-size: 14px;">Mengambil data log...</span>
                    </div>
                </div>
                
                <!-- Pagination -->
                <div id="audit-pagination" style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--panel-border);">
                    <div style="font-size: 13px; color: var(--text-muted);" id="audit-page-info">Menampilkan 0 data</div>
                    <div style="display: flex; gap: 8px;" id="audit-page-controls">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>
            
            <script>
                let currentAuditRange = 'today';
                let currentAuditPage = 1;

                function setAuditFilter(range, btn) {
                    currentAuditRange = range;
                    document.querySelectorAll('.audit-filter-btn').forEach(el => {
                        el.style.background = 'transparent';
                        el.style.boxShadow = 'none';
                        el.style.color = 'var(--text-muted)';
                        el.style.fontWeight = '500';
                        el.classList.remove('active');
                    });
                    
                    if (btn) {
                        btn.style.background = 'var(--primary)';
                        btn.style.boxShadow = '0 3px 8px rgba(0,0,0,0.2)';
                        btn.style.color = 'white';
                        btn.style.fontWeight = '700';
                        btn.classList.add('active');
                    }
                    
                    if(range === 'custom') {
                        document.getElementById('audit-custom-range').style.display = 'flex';
                    } else {
                        document.getElementById('audit-custom-range').style.display = 'none';
                    }
                    
                    currentAuditPage = 1;
                    loadAuditLogs();
                }

                async function loadAuditLogs() {
                    const container = document.getElementById('audit-log-container');
                    container.innerHTML = '<div style="text-align: center; padding: 40px 0;"><div class="loader" style="margin: 0 auto;"></div><div style="margin-top: 12px; color: var(--text-muted); font-size: 14px;">Memuat data...</div></div>';
                    
                    const date = document.getElementById('audit-date').value;
                    const timeStart = document.getElementById('audit-time-start').value;
                    const timeEnd = document.getElementById('audit-time-end').value;
                    const keyword = document.getElementById('audit-search').value;
                    const sort = document.getElementById('audit-sort').value;
                    
                    let url = '/master-demo/security/audit-logs?range=' + currentAuditRange + '&page=' + currentAuditPage;
                    if(date) url += '&date=' + date;
                    if(timeStart) url += '&time_start=' + timeStart;
                    if(timeEnd) url += '&time_end=' + timeEnd;
                    if(keyword) url += '&keyword=' + encodeURIComponent(keyword);
                    if(sort) url += '&sort=' + sort;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').content;
                        // Avoid global loader interception by using XMLHttpRequest header explicitly
                        const response = await fetch(url, {
                            headers: { 
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) throw new Error('Network response was not ok');
                        const data = await response.json();
                        
                        renderAuditLogs(data.data);
                        renderPagination(data);
                    } catch(e) {
                        console.error('Audit Log Error:', e);
                        container.innerHTML = '<div style="text-align:center; padding:32px; color:var(--danger);"><i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:12px;"></i><br>Gagal memuat log audit.</div>';
                    }
                }

                function renderAuditLogs(logs) {
                    const container = document.getElementById('audit-log-container');
                    if(!logs || logs.length === 0) {
                        container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-clock-rotate-left" style="font-size:32px; margin-bottom:16px; opacity:0.5;"></i><br>Tidak ada riwayat aktivitas pada rentang waktu ini.</div>';
                        return;
                    }

                    let html = '<div style="display: flex; flex-direction: column;">';
                    
                    logs.forEach(log => {
                        let icon = log.type === 'rbac' ? '<i class="fa-solid fa-shield-halved" style="color:var(--primary);"></i>' : '<i class="fa-solid fa-server" style="color:var(--success);"></i>';
                        
                        let detailsStr = '';
                        if (log.details && typeof log.details === 'object') {
                            detailsStr = Object.entries(log.details).map(([k, v]) => `<span style="display:inline-block; margin-right:8px; margin-bottom:4px; padding:2px 6px; background:rgba(128,128,128,0.1); border-radius:4px;"><b>${k}:</b> ${v}</span>`).join('');
                        } else if (log.details) {
                            detailsStr = `<span style="padding:2px 6px; background:rgba(128,128,128,0.1); border-radius:4px;">${log.details}</span>`;
                        }

                        let avatar = log.actor_avatar ? `<img src="${log.actor_avatar}" style="width:24px; height:24px; border-radius:50%; object-fit:cover;">` : `<div style="width:24px; height:24px; border-radius:50%; background:var(--panel-border); display:flex; align-items:center; justify-content:center; font-size:10px;"><i class="fa-solid fa-user"></i></div>`;

                        html += `
                        <div style="display: grid; grid-template-columns: 200px 150px 150px 200px minmax(200px, 1fr) 120px; gap: 16px; padding: 16px; border-bottom: 1px solid var(--panel-border); font-size: 13px; align-items: start; transition: background 0.2s;" onmouseover="this.style.background='rgba(128,128,128,0.05)'" onmouseout="this.style.background='transparent'">
                            <div style="color: var(--text-muted);">${log.created_at}</div>
                            <div style="display: flex; gap: 8px; align-items: center; font-weight: 500;">
                                ${avatar}
                                <span>${log.actor}</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                ${icon} ${log.target}
                            </div>
                            <div>
                                <span style="display: inline-block; padding: 4px 10px; background: var(--panel-border); border-radius: 20px; font-size: 11px; font-weight: 600;">${log.action_label}</span>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); line-height: 1.6;">${detailsStr}</div>
                            <div style="font-family: monospace; font-size: 11px; color: var(--text-muted);">${log.ip_address || '-'}</div>
                        </div>`;
                    });
                    
                    html += '</div>';
                    container.innerHTML = html;
                }

                function renderPagination(data) {
                    const info = document.getElementById('audit-page-info');
                    const controls = document.getElementById('audit-page-controls');
                    
                    if(data.total === 0) {
                        info.innerHTML = '';
                        controls.innerHTML = '';
                        return;
                    }
                    
                    info.innerHTML = `Menampilkan ${data.from || 0} - ${data.to || 0} dari total ${data.total} aktivitas`;
                    
                    let btns = '';
                    if (data.prev_page_url) {
                        btns += `<button onclick="changeAuditPage(${data.current_page - 1})" style="padding: 6px 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s;"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
                    }
                    
                    btns += `<span style="padding: 6px 12px; font-size: 12px; font-weight: 600; color: var(--text-main);">Halaman ${data.current_page} / ${data.last_page}</span>`;
                    
                    if (data.next_page_url) {
                        btns += `<button onclick="changeAuditPage(${data.current_page + 1})" style="padding: 6px 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s;">Next <i class="fa-solid fa-chevron-right"></i></button>`;
                    }
                    
                    controls.innerHTML = btns;
                }

                function changeAuditPage(page) {
                    currentAuditPage = page;
                    loadAuditLogs();
                }

                // Initial Load
                setTimeout(() => {
                    if(document.getElementById('audit-log-container')) {
                        loadAuditLogs();
                    }
                }, 100);

                function confirmClearAuditLog() {
                    document.getElementById('modal-clear-audit').style.display = 'flex';
                }

                function closeClearAuditModal() {
                    document.getElementById('modal-clear-audit').style.display = 'none';
                }

                async function executeClearAuditLog() {
                    const timeframe = document.getElementById('audit-clear-timeframe').value;
                    const btn = document.getElementById('btn-execute-clear');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...';
                    btn.disabled = true;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('/master-demo/security/audit-logs/clear', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ timeframe: timeframe })
                        });
                        const result = await response.json();
                        if (result.success) {
                            closeClearAuditModal();
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Audit Log berhasil dibersihkan.', type: 'success' }}));
                            loadAuditLogs();
                        } else {
                            alert(result.message || 'Gagal membersihkan log.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan saat membersihkan log.');
                    } finally {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                }
            </script>
            
            <!-- Audit Clear Modal -->
            <div id="modal-clear-audit" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
                <div style="background:var(--panel-bg); width:400px; border-radius:16px; padding:24px; box-shadow:0 10px 25px rgba(0,0,0,0.2); border:1px solid var(--panel-border);">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                        <div style="width:40px; height:40px; border-radius:50%; background:rgba(239, 68, 68, 0.1); display:flex; align-items:center; justify-content:center; color:var(--danger); font-size:18px;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 style="margin:0; font-size:16px; color:var(--text-main);">Bersihkan Audit Log</h3>
                        </div>
                    </div>
                    <p style="font-size:13px; color:var(--text-muted); line-height:1.5; margin-bottom:20px;">
                        Pilih rentang waktu aktivitas log yang ingin Anda hapus secara permanen dari sistem.
                    </p>
                    
                    <div style="margin-bottom:24px;">
                        <label style="display:block; font-size:12px; font-weight:600; color:var(--text-main); margin-bottom:8px;">Pilih Rentang Waktu</label>
                        <select id="audit-clear-timeframe" style="width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--panel-border); background:var(--bg-main); color:var(--text-main); outline:none; font-size:13px;">
                            <option value="all">Hapus Semua Riwayat Log</option>
                            <option value="older_than_7_days">Log lebih lama dari 7 hari</option>
                            <option value="older_than_30_days" selected>Log lebih lama dari 30 hari</option>
                            <option value="older_than_90_days">Log lebih lama dari 90 hari</option>
                        </select>
                    </div>
                    
                    <div style="display:flex; justify-content:flex-end; gap:12px;">
                        <button onclick="closeClearAuditModal()" style="padding:10px 16px; border-radius:8px; border:1px solid var(--panel-border); background:transparent; color:var(--text-main); font-weight:600; cursor:pointer; font-size:13px;">Batal</button>
                        <button id="btn-execute-clear" onclick="executeClearAuditLog()" style="padding:10px 16px; border-radius:8px; border:none; background:var(--danger); color:white; font-weight:600; cursor:pointer; font-size:13px; box-shadow:0 4px 12px rgba(239, 68, 68, 0.2);"><i class="fa-solid fa-trash"></i> Ya, Bersihkan Log</button>
                    </div>
                </div>
            </div>
        </section>

<section id="view-accounting" class="view-section">
    <?php
        $accounting = app(\App\Services\AccountingService::class);
        $summary = $accounting->getFinanceSummary();
        $journals = \App\Models\JournalEntry::latest()->take(20)->get();
    ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="margin: 0; font-size: 24px;">Finance & Accounting</h2>
            <p style="margin: 4px 0 0; color: var(--text-muted);">Sistem double-entry otomatis untuk merekam semua transaksi bisnis.</p>
        </div>
        <button onclick="document.getElementById('manual-journal-modal').style.display='block'" class="user-pill" style="background: var(--accent); color: white; border: none; cursor: pointer;">
            <i class="fa-solid fa-plus"></i> Jurnal Manual
        </button>
    </div>

    <?php echo $__env->make('finance.index', ['summary' => $summary, 'journals' => $journals], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Modal Jurnal Manual -->
    <div id="manual-journal-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 500px; margin: 100px auto; background: var(--bg-main);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0;">Entri Jurnal Manual</h3>
                <button onclick="document.getElementById('manual-journal-modal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            
            <form method="POST" action="<?php echo e(route('master-demo.finance.journal')); ?>">
                <?php echo csrf_field(); ?>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Deskripsi (Memo)</label>
                    <input type="text" name="memo" class="form-control" placeholder="Contoh: Pembayaran Listrik Bulan Juli" required>
                </div>
                
                <div class="grid-2" style="margin-bottom: 12px;">
                    <div class="form-group">
                        <label>Akun Debit</label>
                        <select name="debit_account" class="form-control" required>
                            <option value="expense">Biaya Operasional (Expense)</option>
                            <option value="inventory">Persediaan Barang (Inventory)</option>
                            <option value="cash_bank">Kas/Bank (Cash)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Akun Kredit</label>
                        <select name="credit_account" class="form-control" required>
                            <option value="cash_bank">Kas/Bank (Cash)</option>
                            <option value="accounts_payable">Hutang Dagang (Payable)</option>
                            <option value="inventory">Persediaan Barang (Inventory)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 24px;">
                    <label>Jumlah (Rp)</label>
                    <input type="number" name="amount" class="form-control" placeholder="1000000" min="1" required>
                </div>
                
                <button type="submit" class="btn" style="width: 100%; justify-content: center;">Catat Jurnal</button>
            </form>
        </div>
    </div>
</section>

        <!-- HRIS & KARYAWAN VIEW -->
        <section id="view-people" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Human Resource Information System</div>
                    <h2 style="margin: 0; font-size: 28px; font-weight: 700; color: var(--text-heading);">People & Talent</h2>
                    <p style="margin: 8px 0 0; color: var(--text-muted); font-size: 14px; max-width: 600px; line-height: 1.5;">Pusat kendali SDM: Rekrutmen, Setup Shift, Dokumen Perusahaan, Goal/Tugas, dan Slip Gaji.</p>
                </div>
            </div>
            
            <div class="grid-2">
                
                <!-- KOLOM KIRI -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <!-- 1. Perekrutan & Tambah Karyawan -->
                    <div class="card" style="padding: 24px; border: 1px solid var(--panel-border);">
                        <h4 style="margin: 0 0 16px 0; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-user-plus" style="color: var(--accent); margin-right: 8px;"></i> Perekrutan & Tambah Karyawan</h4>
                        <form method="POST" action="<?php echo e(route('master-demo.employee.hire')); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <input type="text" name="name" class="ios-input" placeholder="Nama Lengkap Karyawan" required style="width: 100%;">
                            </div>
                            <div class="form-group" style="margin-bottom: 12px;">
                                <input type="email" name="email" class="ios-input" placeholder="Alamat Email Karyawan" required style="width: 100%;">
                            </div>
                            <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                                <select name="role" class="ios-input" style="flex: 1;" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="manager">Manager</option>
                                    <option value="staff">Staff</option>
                                </select>
                                <input type="text" name="job_title" class="ios-input" placeholder="Jabatan Spesifik" style="flex: 1;" required>
                            </div>
                            <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                                <select name="reports_to_id" class="ios-input" style="flex: 1;">
                                    <option value="">-- Lapor Ke Atasan --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\User::where('company_id', $company->id)->where('id', '!=', auth()->id())->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <input type="number" name="base_salary" class="ios-input" placeholder="Gaji Pokok (Rp)" style="flex: 1;">
                            </div>
                            <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%;"><i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Ajukan Perekrutan Baru</button>
                        </form>

                        <div style="margin-top: 24px; border-top: 1px solid var(--panel-border); padding-top: 16px;">
                            <h5 style="margin: 0 0 12px 0; font-size: 13px; color: var(--text-muted);">Menunggu Persetujuan (Approval)</h5>
                            <?php $pendingHires = \App\Models\User::where('company_id', $company->id)->where('is_approved', false)->get(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pendingHires; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hire): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--panel-secondary); padding: 12px; border-radius: 8px; margin-bottom: 8px;">
                                    <div>
                                        <div style="font-size: 13px; font-weight: 600; color: var(--text-heading);"><?php echo e($hire->name); ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?php echo e($hire->job_title); ?></div>
                                    </div>
                                    <form method="POST" action="<?php echo e(route('master-demo.employee.approve', $hire->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="ios-btn ios-btn-success" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-check"></i> Setujui</button>
                                    </form>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div style="font-size: 12px; color: var(--text-muted); text-align: center; padding: 12px; background: var(--panel-secondary); border-radius: 8px;">Tidak ada usulan perekrutan tertunda.</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!-- 2. Setup Jam Shift -->
                    <div class="card" style="padding: 24px; border: 1px solid var(--panel-border); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                        <h4 style="margin: 0 0 16px 0; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-clock" style="color: var(--accent); margin-right: 8px;"></i> Setup Jam Shift</h4>
                        <form id="form-shift" method="POST" action="<?php echo e(route('master-demo.shifts.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="_method" id="shift-method" value="POST">
                            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <input type="text" id="shift-name" name="name" class="ios-input" placeholder="Nama Shift (Cth: Pagi)" required style="flex: 2; padding: 12px; border-radius: 12px;">
                                <input type="time" id="shift-start" name="start_time" class="ios-input" required style="flex: 1; padding: 12px; border-radius: 12px;">
                                <input type="time" id="shift-end" name="end_time" class="ios-input" required style="flex: 1; padding: 12px; border-radius: 12px;">
                            </div>
                            <button type="submit" id="shift-btn" class="ios-btn ios-btn-primary" style="width: 100%; border-radius: 12px; padding: 12px; font-weight: 600;"><i class="fa-solid fa-save" style="margin-right: 8px;"></i> Simpan Shift</button>
                        </form>
                        
                        <div style="margin-top: 16px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = \App\Models\Shift::where('company_id', $company->id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--panel-secondary); padding: 12px 16px; border-radius: 12px; margin-bottom: 10px; border: 1px solid var(--panel-border);">
                                    <div>
                                        <span style="font-weight: 600; color: var(--text-heading); font-size: 14px;"><?php echo e($shift->name); ?></span>
                                        <span style="color: var(--text-muted); font-size: 12px; margin-left: 10px; background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 20px;"><?php echo e(substr($shift->start_time, 0, 5)); ?> - <?php echo e(substr($shift->end_time, 0, 5)); ?></span>
                                    </div>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" onclick="editShift('<?php echo e($shift->id); ?>', '<?php echo e($shift->name); ?>', '<?php echo e(substr($shift->start_time, 0, 5)); ?>', '<?php echo e(substr($shift->end_time, 0, 5)); ?>')" class="ios-btn" style="padding: 6px 12px; font-size: 11px; background: rgba(59,130,246,0.1); color: var(--text-accent); border-radius: 20px;"><i class="fa-solid fa-pencil" style="margin-right: 4px;"></i> Edit</button>
                                        <form method="POST" action="/master-demo/shifts/<?php echo e($shift->id); ?>" onsubmit="return confirm('Hapus shift ini?');" style="margin: 0;">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="ios-btn" style="padding: 6px 12px; font-size: 11px; background: rgba(239,68,68,0.1); color: #ef4444; border-radius: 20px;"><i class="fa-solid fa-trash" style="margin-right: 4px;"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 20px;">Belum ada shift terdaftar.</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <script>
                            function editShift(id, name, start, end) {
                                document.getElementById('form-shift').action = '/master-demo/shifts/' + id;
                                document.getElementById('shift-method').value = 'PUT';
                                document.getElementById('shift-name').value = name;
                                document.getElementById('shift-start').value = start;
                                document.getElementById('shift-end').value = end;
                                document.getElementById('shift-btn').innerHTML = '<i class="fa-solid fa-check" style="margin-right: 8px;"></i> Update Shift';
                                document.getElementById('shift-name').focus();
                            }
                        </script>
                    </div>

                    <!-- 3. Dokumen Perusahaan -->
                    <div class="card" style="padding: 24px; border: 1px solid var(--panel-border);">
                        <h4 style="margin: 0 0 16px 0; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-file-contract" style="color: var(--accent); margin-right: 8px;"></i> Dokumen Perusahaan</h4>
                        <form method="POST" action="<?php echo e(route('master-demo.documents.upload')); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <input type="text" name="title" class="ios-input" placeholder="Judul Dokumen (Misal: Aturan Cuti)" required style="width: 100%; margin-bottom: 12px;">
                            
                            <div class="ios-file-upload" id="docUploadArea" onclick="document.getElementById('docFileInput').click()" ondragover="event.preventDefault(); this.classList.add('drag-over');" ondragleave="this.classList.remove('drag-over');" ondrop="event.preventDefault(); this.classList.remove('drag-over'); document.getElementById('docFileInput').files = event.dataTransfer.files; handleFileSelect(document.getElementById('docFileInput'), 'docUploadArea');" style="margin-bottom: 16px;">
                                <input type="file" name="file" id="docFileInput" required onchange="handleFileSelect(this, 'docUploadArea')">
                                <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                                <p class="upload-text">Pilih File PDF</p>
                                <p class="upload-subtext">atau drag & drop file ke sini (Maks 5MB)</p>
                                
                                <div class="file-preview" id="docFilePreview" onclick="event.stopPropagation()">
                                    <div class="file-preview-info">
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <div class="file-name-size">
                                            <span class="file-name" id="docFileName">dokumen.pdf</span>
                                            <span class="file-size" id="docFileSize">1.2 MB</span>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <button type="button" class="file-action-btn" onclick="document.getElementById('docFileInput').click()" title="Replace"><i class="fa-solid fa-pen"></i></button>
                                        <button type="button" class="file-action-btn remove" onclick="clearFileUpload('docFileInput', 'docUploadArea')" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%;"><i class="fa-solid fa-upload" style="margin-right: 6px;"></i> Upload Dokumen</button>
                        </form>
                        
                        <div style="margin-top: 16px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = \App\Models\CompanyDocument::where('company_id', $company->id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--panel-secondary); padding: 10px 14px; border-radius: 8px; margin-bottom: 8px; border: 1px solid var(--panel-border);">
                                    <a href="<?php echo e(Storage::url($doc->file_path)); ?>" target="_blank" style="color: var(--text-heading); font-size: 13px; text-decoration: none;"><i class="fa-solid fa-file-pdf" style="color: var(--danger); margin-right: 8px;"></i> <?php echo e($doc->title); ?></a>
                                    <form method="POST" action="/master-demo/documents/<?php echo e($doc->id); ?>" onsubmit="return confirm('Hapus dokumen ini?');">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="ios-btn ios-btn-danger" style="padding: 4px 10px; font-size: 11px;"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div style="font-size: 12px; color: var(--text-muted);">Belum ada dokumen perusahaan.</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- 3.5. Kalender Libur (Holidays) -->
                    <div class="card" style="padding: 24px; border: 1px solid var(--panel-border); border-radius: 16px; margin-top: 24px;">
                        <h4 style="margin: 0 0 16px 0; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-calendar-day" style="color: var(--accent); margin-right: 8px;"></i> Manajemen Hari Libur</h4>
                        <form method="POST" action="<?php echo e(route('master-demo.holidays.store')); ?>">
                            <?php echo csrf_field(); ?>
                            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <input type="text" name="name" class="ios-input" placeholder="Nama Libur (Cth: Idul Fitri)" required style="flex: 2; padding: 12px; border-radius: 12px;">
                                <input type="date" name="start_date" class="ios-input" required style="flex: 1; padding: 12px; border-radius: 12px;">
                                <input type="date" name="end_date" class="ios-input" placeholder="Selesai (Opsional)" style="flex: 1; padding: 12px; border-radius: 12px;">
                            </div>
                            <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%; border-radius: 12px; padding: 12px; font-weight: 600;"><i class="fa-solid fa-plus" style="margin-right: 8px;"></i> Tambah Hari Libur</button>
                        </form>
                        
                        <div style="margin-top: 16px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = \App\Models\Holiday::where('company_id', $company->id)->orderBy('start_date', 'asc')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--panel-secondary); padding: 12px 16px; border-radius: 12px; margin-bottom: 10px; border: 1px solid var(--panel-border);">
                                    <div>
                                        <span style="font-weight: 600; color: var(--text-heading); font-size: 14px;"><?php echo e($holiday->name); ?></span>
                                        <span style="color: var(--text-muted); font-size: 12px; margin-left: 10px; background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 20px;">
                                            <?php echo e(\Carbon\Carbon::parse($holiday->start_date)->format('d M Y')); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($holiday->end_date && $holiday->start_date != $holiday->end_date): ?>
                                                - <?php echo e(\Carbon\Carbon::parse($holiday->end_date)->format('d M Y')); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                    </div>
                                    <div>
                                        <form method="POST" action="/master-demo/holidays/<?php echo e($holiday->id); ?>" onsubmit="return confirm('Hapus hari libur ini?');" style="margin: 0;">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="ios-btn" style="padding: 6px 12px; font-size: 11px; background: rgba(239,68,68,0.1); color: #ef4444; border-radius: 20px;"><i class="fa-solid fa-trash" style="margin-right: 4px;"></i> Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div style="font-size: 13px; color: var(--text-muted); text-align: center; padding: 20px;">Belum ada hari libur terdaftar.</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    
                    <!-- 4. Setup Goal / Tugas Karyawan -->
                    <div class="card" style="padding: 24px; border: 1px solid var(--panel-border);">
                        <h4 style="margin: 0 0 16px 0; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-bullseye" style="color: var(--accent); margin-right: 8px;"></i> Assign Tugas / Goal</h4>
                        <form id="form-assign-task" method="POST" action="<?php echo e(route('master-demo.tasks.store')); ?>" onsubmit="handleStoreTask(event)">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="type" value="goal">
                            
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Pilih Karyawan</label>
                                <select id="assign-task-user" name="user_id" class="ios-input" required style="width: 100%;" onchange="updateAssignTaskDivision(this)">
                                    <option value="" data-division="">-- Pilih Karyawan --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $company->users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($u->id); ?>" data-division="<?php echo e($u->division ?? 'Tanpa Divisi'); ?>"><?php echo e($u->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 12px;">
                                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Divisi</label>
                                <input type="text" id="assign-task-division" name="division" class="ios-input" readonly placeholder="Divisi otomatis terisi..." style="width: 100%; background: var(--panel-secondary); cursor: not-allowed;" value="">
                            </div>

                            <div class="form-group" style="margin-bottom: 12px;">
                                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Deadline</label>
                                <input type="date" id="assign-task-deadline" name="deadline" class="ios-input" required style="width: 100%;">
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Deskripsi Tugas</label>
                                <input type="text" id="assign-task-title" name="title" class="ios-input" placeholder="Contoh: Susun laporan bulanan" required style="width: 100%;">
                            </div>

                            <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%;"><i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Assign Tugas</button>
                        </form>

                        <div style="margin-top: 24px; border-top: 1px solid var(--panel-border); padding-top: 16px;">
                            <h5 style="margin: 0 0 12px 0; font-size: 13px; color: var(--text-muted);">Goal / Tugas Aktif</h5>
                            <div id="active-tasks-list">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = \App\Models\Task::with('user')->where('company_id', $company->id)->latest()->take(10)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php
                                        $uName = $task->user->name ?? $task->assignee->name ?? 'Unknown';
                                        $uDiv = $task->user->division ?? 'Tanpa Divisi';
                                        $dlFormatted = $task->deadline ? $task->deadline->format('d M Y') : 'N/A';
                                        $dlVal = $task->deadline ? $task->deadline->format('Y-m-d') : '';
                                    ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; background: var(--panel-secondary); padding: 10px 14px; border-radius: 8px; margin-bottom: 8px; border: 1px solid var(--panel-border);">
                                        <div>
                                            <div style="font-size: 13px; font-weight: 600; color: var(--text-heading);"><?php echo e($task->title); ?></div>
                                            <div style="font-size: 11px; color: var(--text-muted);">
                                                Assigned to: <?php echo e($uName); ?> &bull; Divisi: <?php echo e($uDiv); ?> &bull; DL: <?php echo e($dlFormatted); ?>

                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 6px; align-items: center;">
                                            <button type="button" class="ios-btn ios-btn-secondary" style="padding: 6px 14px; font-size: 11px; border-radius: 20px; font-weight: 600; display: flex; align-items: center; gap: 6px;" onclick="openEditTaskModal(<?php echo e($task->id); ?>, '<?php echo e(addslashes($task->title)); ?>', <?php echo e($task->user_id ?? 0); ?>, '<?php echo e($dlVal); ?>', '<?php echo e($task->status ?? 'pending'); ?>', '<?php echo e($task->priority ?? 'medium'); ?>')"><i class="fa-solid fa-pen" style="font-size: 10px;"></i> Edit</button>
                                            <button type="button" class="ios-btn ios-btn-danger" style="padding: 6px 14px; font-size: 11px; border-radius: 20px; font-weight: 600; display: flex; align-items: center; gap: 6px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);" onclick="openDeleteTaskModal(<?php echo e($task->id); ?>, '<?php echo e(addslashes($task->title)); ?>', '<?php echo e(addslashes($uName)); ?>', '<?php echo e(addslashes($uDiv)); ?>', '<?php echo e($dlFormatted); ?>')"><i class="fa-solid fa-trash" style="font-size: 10px;"></i> Hapus</button>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div style="font-size: 12px; color: var(--text-muted); text-align: center; padding: 12px; background: var(--panel-secondary); border-radius: 8px;">Belum ada goal / tugas.</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Slip Gaji -->
                    <div class="card" style="padding: 24px; border: 1px solid var(--panel-border);">
                        <h4 style="margin: 0 0 16px 0; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-file-invoice-dollar" style="color: var(--accent); margin-right: 8px;"></i> Upload Slip Gaji (Manual PDF)</h4>
                        <form method="POST" action="<?php echo e(route('master-demo.payslip.upload')); ?>" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                                <select name="user_id" class="ios-input" required style="flex: 1; min-width: 150px;">
                                    <option value="">-- Pilih Karyawan --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $company->users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <input type="month" name="month_year" class="ios-input" required style="flex: 1; min-width: 150px;">
                            </div>
                            
                            <input type="number" name="net_salary" class="ios-input" placeholder="Gaji Bersih (Rp)" required style="width: 100%; margin-bottom: 12px;">

                            <div class="ios-file-upload" id="payslipUploadArea" onclick="document.getElementById('payslipFileInput').click()" ondragover="event.preventDefault(); this.classList.add('drag-over');" ondragleave="this.classList.remove('drag-over');" ondrop="event.preventDefault(); this.classList.remove('drag-over'); document.getElementById('payslipFileInput').files = event.dataTransfer.files; handleFileSelect(document.getElementById('payslipFileInput'), 'payslipUploadArea');" style="margin-bottom: 16px;">
                                <input type="file" name="file" accept=".pdf" id="payslipFileInput" required onchange="handleFileSelect(this, 'payslipUploadArea')">
                                <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                                <p class="upload-text">Pilih File Slip Gaji (PDF)</p>
                                <p class="upload-subtext">atau drag & drop file ke sini</p>
                                
                                <div class="file-preview" id="payslipFilePreview" onclick="event.stopPropagation()">
                                    <div class="file-preview-info">
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <div class="file-name-size">
                                            <span class="file-name" id="payslipFileName">slip_gaji.pdf</span>
                                            <span class="file-size" id="payslipFileSize">1.2 MB</span>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <button type="button" class="file-action-btn" onclick="document.getElementById('payslipFileInput').click()" title="Replace"><i class="fa-solid fa-pen"></i></button>
                                        <button type="button" class="file-action-btn remove" onclick="clearFileUpload('payslipFileInput', 'payslipUploadArea')" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%;"><i class="fa-solid fa-upload" style="margin-right: 6px;"></i> Upload Slip Gaji</button>
                        </form>

                        <div style="margin-top: 16px;">
                            <h5 style="margin: 0 0 12px 0; font-size: 13px; color: var(--text-muted);">Slip Gaji Terbaru</h5>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = \App\Models\Payslip::where('company_id', $company->id)->latest()->take(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: var(--panel-secondary); padding: 10px 14px; border-radius: 8px; margin-bottom: 8px; border: 1px solid var(--panel-border);">
                                    <div style="font-size: 13px; color: var(--text-heading);">
                                        <strong><?php echo e($p->user->name ?? 'Unknown'); ?></strong> &bull; <?php echo e($p->month_year); ?>

                                        <div style="font-size: 11px; color: var(--text-muted);">Rp <?php echo e(number_format($p->net_salary ?? 0, 0, ',', '.')); ?></div>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->file_path): ?>
                                        <a href="<?php echo e(Storage::url($p->file_path)); ?>" target="_blank" class="ios-btn ios-btn-secondary" style="padding: 4px 10px; font-size: 11px; text-decoration: none;"><i class="fa-solid fa-eye"></i></a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('master-demo.payslip.delete', $p->id)); ?>" onsubmit="return confirm('Hapus slip gaji ini?');">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="ios-btn ios-btn-danger" style="padding: 4px 10px; font-size: 11px;"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div style="font-size: 12px; color: var(--text-muted);">Belum ada slip gaji diunggah.</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    
                </div>
            </div>

            <!-- 6. Tabel Master Karyawan -->
            <div class="card" style="margin-top: 24px; padding: 24px; border: 1px solid var(--panel-border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h4 style="margin: 0; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-users" style="color: var(--accent); margin-right: 8px;"></i> Daftar Karyawan Aktif</h4>
                    <input type="text" id="hris-emp-search" class="ios-input" placeholder="Cari karyawan..." onkeyup="filterHrisEmployees()" style="width: 250px;">
                </div>
                
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;" id="hris-emp-table">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--panel-border); color: var(--text-muted); text-align: left;">
                                <th style="padding: 12px 16px;">Nama & Jabatan</th>
                                <th style="padding: 12px 16px;">Divisi</th>
                                <th style="padding: 12px 16px;">Email Login</th>
                                <th style="padding: 12px 16px; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $company->users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($u->is_approved): ?>
                            <tr class="hris-emp-row" id="emp-row-<?php echo e($u->id); ?>" style="border-bottom: 1px solid var(--panel-border);" data-name="<?php echo e(strtolower($u->name)); ?>" data-role="<?php echo e(strtolower($u->job_title ?? '')); ?>">
                                <td style="padding: 16px;">
                                    <div style="font-weight: 600; color: var(--text-heading); font-size: 14px;" id="emp-name-<?php echo e($u->id); ?>"><?php echo e($u->name); ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;" id="emp-role-<?php echo e($u->id); ?>"><?php echo e($u->job_title ?? 'Staff'); ?></div>
                                </td>
                                <td style="padding: 16px;">
                                    <span style="background: rgba(var(--accent-rgb), 0.1); color: var(--accent); padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                        <?php echo e($u->division ?? $u->divisionLabel() ?? 'Perusahaan'); ?>

                                    </span>
                                </td>
                                <td style="padding: 16px; color: var(--text-muted);" id="emp-email-<?php echo e($u->id); ?>">
                                    <?php echo e($u->email); ?>

                                </td>
                                <td style="padding: 16px; text-align: right;">
                                    <button class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px; margin-right: 8px;" onclick="openEditEmployeeModal('<?php echo e($u->id); ?>', '<?php echo e(addslashes($u->name)); ?>', '<?php echo e($u->email); ?>', '<?php echo e((int)$u->base_salary); ?>', '<?php echo e(addslashes($u->job_title)); ?>', '<?php echo e($u->employment_type); ?>', '<?php echo e($u->role); ?>', '<?php echo e($u->employee_code); ?>')"><i class="fa-solid fa-pen"></i> Edit</button>
                                    <button class="ios-btn ios-btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="openDeleteEmployeeModal('<?php echo e($u->id); ?>', '<?php echo e(addslashes($u->name)); ?>', '<?php echo e(addslashes($u->job_title ?? "Staff")); ?>')"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <script>
            function filterHrisEmployees() {
                const query = document.getElementById('hris-emp-search').value.toLowerCase();
                const rows = document.querySelectorAll('.hris-emp-row');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    const role = row.getAttribute('data-role');
                    if (name.includes(query) || role.includes(query)) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            function openEditEmployeeModal(id, name, email, baseSalary, jobTitle, empType, role, empCode) {
                document.getElementById('editEmpId').value = id;
                document.getElementById('editEmpName').value = name;
                document.getElementById('editEmpEmail').value = email;
                document.getElementById('editEmpBaseSalary').value = baseSalary || 0;
                document.getElementById('editEmpJobTitle').value = jobTitle;
                document.getElementById('editEmpEmploymentType').value = empType || 'Full-Time';
                document.getElementById('editEmpRole').value = role || 'staff';
                document.getElementById('editEmpCode').value = empCode;
                document.getElementById('editEmpPassword').value = '';
                document.getElementById('editEmployeeModal').style.display = 'flex';
            }
            function closeEditEmployeeModal() {
                document.getElementById('editEmployeeModal').style.display = 'none';
            }
            function openDeleteEmployeeModal(id, name, role) {
                document.getElementById('delEmpId').value = id;
                document.getElementById('delEmpName').innerText = name;
                document.getElementById('delEmpRole').innerText = role;
                document.getElementById('deleteEmployeeModal').style.display = 'flex';
            }
            function closeDeleteEmployeeModal() {
                document.getElementById('deleteEmployeeModal').style.display = 'none';
            }
            </script>

            <!-- Edit Employee Modal -->
            <!-- Edit Employee Modal -->
            <div id="editEmployeeModal" class="modal-overlay">
                <div class="modal-content" style="background: var(--bg-main); border: 1px solid var(--panel-border); max-height: 85vh; overflow-y: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <h3 style="margin: 0; color: var(--text-heading); font-size: 18px;"><i class="fa-solid fa-pen-to-square" style="color: var(--accent); margin-right: 8px;"></i> Edit Profil Staf Tim</h3>
                        <button onclick="closeEditEmployeeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
                    </div>
                    
                    <form id="editEmployeeForm" onsubmit="submitEditEmployee(event)">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="user_id" id="editEmpId">
                        
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">NIK (Kode Pegawai)</label>
                            <input type="text" id="editEmpCode" class="ios-input" readonly style="opacity: 0.7; cursor: not-allowed; background: var(--panel-secondary); font-family: monospace;">
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Nama Lengkap</label>
                            <input type="text" name="name" id="editEmpName" class="ios-input" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Email Staf (Untuk Penerimaan OTP)</label>
                            <input type="email" name="email" id="editEmpEmail" class="ios-input" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Nama Jabatan Kustom</label>
                            <input type="text" name="job_title" id="editEmpJobTitle" class="ios-input" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Password Baru (Kosongkan jika tidak diubah)</label>
                            <div style="padding: 12px; border: 1px solid rgba(var(--accent-rgb), 0.2); background: rgba(var(--accent-rgb), 0.05); border-radius: 8px; margin-bottom: 8px;">
                                <p style="margin:0; font-size: 12px; color: var(--text-muted); line-height: 1.4;">Login akun menggunakan OTP email. Perubahan email akan menentukan alamat tujuan OTP berikutnya.</p>
                            </div>
                            <input type="password" name="password" id="editEmpPassword" class="ios-input">
                        </div>

                        <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group" style="flex: 1;">
                                <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Tipe Pekerjaan</label>
                                <select name="employment_type" id="editEmpEmploymentType" class="ios-input">
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Level/Posisi (Role)</label>
                                <select name="role" id="editEmpRole" class="ios-input">
                                    <option value="manager">🎯 Manager</option>
                                    <option value="supervisor">🎯 Supervisor</option>
                                    <option value="pic">🎯 PIC</option>
                                    <option value="staff">🎯 Staff</option>
                                    <option value="ceo">👑 CEO</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Gaji Pokok (Rp)</label>
                            <input type="number" name="base_salary" id="editEmpBaseSalary" class="ios-input" placeholder="Misal: 5000000">
                        </div>
                        
                        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--panel-border); padding-top: 16px;">
                            <button type="button" class="ios-btn ios-btn-secondary" onclick="closeEditEmployeeModal()">Batal</button>
                            <button type="submit" class="ios-btn ios-btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Employee Modal -->
            <div id="deleteEmployeeModal" class="modal-overlay">
                <div class="modal-content" style="background: var(--bg-main); border: 1px solid var(--danger);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="margin: 0; color: var(--danger); font-size: 18px;"><i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i> Konfirmasi Penonaktifan</h3>
                        <button onclick="closeDeleteEmployeeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
                    </div>
                    
                    <div style="background: rgba(239, 68, 68, 0.1); padding: 16px; border-radius: 12px; margin-bottom: 24px; border: 1px solid rgba(239, 68, 68, 0.2);">
                        <p style="margin: 0 0 12px 0; color: var(--text-main); font-size: 14px;">Apakah Anda yakin ingin menonaktifkan akun karyawan ini?</p>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <strong style="font-size: 16px; color: var(--text-heading);" id="delEmpName">Nama</strong>
                            <span style="font-size: 13px; color: var(--text-muted);" id="delEmpRole">Jabatan</span>
                        </div>
                    </div>
                    
                    <form id="deleteEmployeeForm" onsubmit="submitDeleteEmployee(event)">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <input type="hidden" id="delEmpId">
                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                            <button type="button" class="ios-btn ios-btn-secondary" onclick="closeDeleteEmployeeModal()">Batal</button>
                            <button type="submit" class="ios-btn ios-btn-danger">Nonaktifkan Karyawan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Task Edit Modal -->
            <div id="task-edit-modal" class="modal-overlay">
                <div class="modal-content" style="background: var(--bg-main); border: 1px solid var(--panel-border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <h3 style="margin: 0; color: var(--text-heading); font-size: 18px;"><i class="fa-solid fa-pen" style="color: var(--accent); margin-right: 8px;"></i> Edit Tugas</h3>
                        <button onclick="closeEditTaskModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
                    </div>
                    <form id="editTaskForm" onsubmit="submitEditTask(event)">
                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                        <input type="hidden" id="editTaskId">
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Judul Tugas</label>
                            <input type="text" name="title" id="editTaskTitle" class="ios-input" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Tugaskan Kepada</label>
                            <select name="user_id" id="editTaskUser" class="ios-input" required>
                                <option value="">-- Pilih Karyawan --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $company->users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Batas Waktu (Deadline)</label>
                            <input type="date" name="deadline" id="editTaskDeadline" class="ios-input" required>
                        </div>
                        <div style="display: flex; gap: 16px; margin-bottom: 24px;">
                            <div class="form-group" style="flex: 1;">
                                <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Status</label>
                                <select name="status" id="editTaskStatus" class="ios-input">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label style="font-size: 13px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; display: block;">Prioritas</label>
                                <select name="priority" id="editTaskPriority" class="ios-input">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--panel-border); padding-top: 16px;">
                            <button type="button" class="ios-btn ios-btn-secondary" onclick="closeEditTaskModal()">Batal</button>
                            <button type="submit" class="ios-btn ios-btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Task Delete Modal -->
            <div id="task-delete-modal" class="modal-overlay">
                <div class="modal-content" style="background: var(--bg-main); border: 1px solid var(--danger);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="margin: 0; color: var(--danger); font-size: 18px;"><i class="fa-solid fa-trash" style="margin-right: 8px;"></i> Hapus Tugas</h3>
                        <button onclick="closeDeleteTaskModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
                    </div>
                    <div style="background: rgba(239, 68, 68, 0.1); padding: 16px; border-radius: 12px; margin-bottom: 24px; border: 1px solid rgba(239, 68, 68, 0.2);">
                        <p style="margin: 0 0 12px 0; color: var(--text-main); font-size: 14px;">Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan.</p>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <strong style="font-size: 16px; color: var(--text-heading);" id="delTaskTitle">Judul Tugas</strong>
                            <span style="font-size: 13px; color: var(--text-muted);" id="delTaskAssignee">Assignee</span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="ios-btn ios-btn-secondary" onclick="closeDeleteTaskModal()">Batal</button>
                        <button type="button" class="ios-btn ios-btn-danger" onclick="confirmDeleteTask()">Hapus Tugas</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- RECIPE BOM VIEW -->
        <section id="view-recipes" class="view-section">
            <div class="card" style="margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--panel-border); padding-bottom: 16px; margin-bottom: 16px;">
                    <h3 style="margin: 0; border: none; padding: 0;">Master Resep (Bill of Materials)</h3>
                    <button class="user-pill" style="background: var(--accent); color: white; cursor: pointer; border: none;">+ Buat Resep Baru</button>
                </div>
                <p class="desc" style="margin-bottom: 24px;">Pengaturan hak paten resep (BOM). Hanya CEO & Manajer Produksi yang memiliki wewenang mengubah takaran. Seluruh takaran <strong>wajib menggunakan satuan Gram (gr)</strong> untuk akurasi HPP. Harga Modal (Cost per Gram) tersinkron otomatis dengan stok Gudang.</p>
                
                <!-- Create Recipe Form (Mockup for CEO) -->
                <div style="background: rgba(0,0,0,0.2); padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid var(--panel-border);">
                    <h4 style="margin: 0 0 12px 0; font-size: 14px;">Draft Resep Baru</h4>
                    <form method="post" action="<?php echo e(route('master-demo.recipes.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="grid-2">
                            <div>
                                <label style="font-size: 12px; color: var(--text-muted);">Pilih Produk Jadi</label>
                                <select name="product_id" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); color: white; padding: 8px 12px; border-radius: 6px; width: 100%; margin-bottom: 12px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Product::where('company_id', $company->id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <label style="font-size: 12px; color: var(--text-muted);">Nama Resep Khusus</label>
                                <input type="text" name="name" class="form-control" placeholder="Contoh: Roti Sobek Standar" style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); color: white; padding: 8px 12px; border-radius: 6px; width: 100%; margin-bottom: 12px;">
                                <label style="font-size: 12px; color: var(--text-muted);">Yield (Output Pcs)</label>
                                <input type="number" name="yield_quantity" class="form-control" placeholder="100" style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); color: white; padding: 8px 12px; border-radius: 6px; width: 100%; margin-bottom: 12px;">
                            </div>
                            <div>
                                <label style="font-size: 12px; color: var(--text-muted);">Pilih Bahan dari Gudang (Gram)</label>
                                <div id="recipe-items-container">
                                    <div class="recipe-item-row" style="display: flex; gap: 8px; margin-bottom: 8px;">
                                        <select name="materials[]" class="form-control material-select" onchange="calculateRecipeCost()" style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); color: white; padding: 8px 12px; border-radius: 6px; flex: 2;">
                                            <option value="" data-cost="0">Pilih Bahan...</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Product::where('company_id', $company->id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($material->id); ?>" data-cost="<?php echo e($material->standard_cost ?? 0); ?>"><?php echo e($material->name); ?> (Rp<?php echo e(number_format($material->standard_cost ?? 0, 0, ',', '.')); ?>/gr)</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                        <input type="number" name="quantities[]" class="form-control material-qty" oninput="calculateRecipeCost()" placeholder="Gram" style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); color: white; padding: 8px 12px; border-radius: 6px; flex: 1;">
                                    </div>
                                </div>
                                <button type="button" class="user-pill" onclick="addRecipeItemRow()" style="background: rgba(255,255,255,0.1); border: none; cursor: pointer; color: white; margin-bottom: 16px;">+ Tambah Bahan Lain</button>
                                
                                <div style="font-size: 12px; color: var(--success); font-weight: bold; margin-bottom: 16px; padding: 8px; background: rgba(12, 53, 39,0.1); border-radius: 4px;">
                                    TOTAL ESTIMASI HPP: <span id="recipe-total-cost">Rp 0</span>
                                </div>
                                
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Resep / BOM</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <script>
                function calculateRecipeCost() {
                    let total = 0;
                    document.querySelectorAll('.recipe-item-row').forEach(row => {
                        const select = row.querySelector('.material-select');
                        const qty = row.querySelector('.material-qty').value;
                        const costPerGram = select.options[select.selectedIndex].getAttribute('data-cost');
                        if(qty && costPerGram) {
                            total += (parseFloat(qty) * parseFloat(costPerGram));
                        }
                    });
                    document.getElementById('recipe-total-cost').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                }
                
                function addRecipeItemRow() {
                    const container = document.getElementById('recipe-items-container');
                    const firstRow = container.querySelector('.recipe-item-row');
                    const newRow = firstRow.cloneNode(true);
                    newRow.querySelector('.material-qty').value = '';
                    container.appendChild(newRow);
                }
                </script>

                <div style="margin-bottom: 16px;">
                    <input type="text" id="recipe-search" class="form-control" placeholder="Cari Resep..." onkeyup="searchRecipes()" style="width: 100%; max-width: 400px;">
                </div>

                <div id="recipe-list">
                <?php
                    $recipes = \App\Models\Recipe::with(['product', 'items.material'])->where('company_id', $company->id)->get();
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recipes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recipe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $estimatedCost = 0;
                        foreach($recipe->items as $item) {
                            // Hitung harga berdasar average cost material per gram
                            // Asumsi standard_cost adalah per satuan (misal per gram)
                            $estimatedCost += $item->quantity * ($item->material->standard_cost ?? 0);
                        }
                    ?>
                    <div class="list-item recipe-item" data-name="<?php echo e(strtolower($recipe->name)); ?>">
                        <div>
                            <div class="title" style="color: var(--success); font-size: 16px;">1x Resep: <?php echo e($recipe->name); ?></div>
                            <div class="desc" style="margin-top: 8px;"><strong>Output:</strong> <?php echo e($recipe->yield_quantity); ?> <?php echo e($recipe->product->unit ?? 'Pcs'); ?> | <strong>Estimasi Modal:</strong> Rp <?php echo e(number_format($estimatedCost, 0, ',', '.')); ?></div>
                        </div>
                    </div>
                    <div class="recipe-item-details" data-name="<?php echo e(strtolower($recipe->name)); ?>" style="background: rgba(0,0,0,0.2); padding: 16px; border-radius: 8px; margin-top: 10px; margin-bottom: 24px;">
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; font-weight: bold;">Bahan Baku (Raw Materials):</div>
                        <ul style="margin: 0; padding-left: 20px; color: var(--text-main); font-size: 14px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recipe->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li style="margin-bottom: 6px;"><strong><?php echo e($item->quantity); ?> gram</strong> - <?php echo e($item->material->name); ?> (Rp <?php echo e(number_format($item->quantity * ($item->material->standard_cost ?? 0), 0, ',', '.')); ?>)</li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fa-solid fa-receipt" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                        <p>Belum ada resep / BOM yang dibuat.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <script>
                function searchRecipes() {
                    const query = document.getElementById('recipe-search').value.toLowerCase();
                    const items = document.querySelectorAll('.recipe-item, .recipe-item-details');
                    items.forEach(el => {
                        const name = el.getAttribute('data-name');
                        if (name && name.includes(query)) {
                            el.style.display = 'block';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                }
                </script>
            </div>
        </section>

        <!-- PURCHASING VIEW -->
        <section id="view-purchasing" class="view-section">
            <?php echo $__env->make('purchasing.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </section>

        <!-- PRODUCTION VIEW -->
        <section id="view-production" class="view-section">
            <div class="ios-tabs" style="display: flex; gap: 16px; border-bottom: 1px solid var(--panel-border); margin-bottom: 24px; padding-bottom: 8px; overflow-x: auto;">
                <button class="ios-tab-main active" data-prodtab="dashboard" onclick="switchProdTab('dashboard')" style="background:none; border:none; color:var(--accent); font-weight:600; padding:8px 16px; border-bottom: 2px solid var(--accent); cursor:pointer; white-space: nowrap;">Production / BOM</button>
                <button class="ios-tab-main" data-prodtab="request" onclick="switchProdTab('request')" style="background:none; border:none; color:var(--text-muted); font-weight:500; padding:8px 16px; cursor:pointer; white-space: nowrap;">Request Bahan (Pabrik)</button>
                <button class="ios-tab-main" data-prodtab="automation" onclick="switchProdTab('automation')" style="background:none; border:none; color:var(--text-muted); font-weight:500; padding:8px 16px; cursor:pointer; white-space: nowrap;">Otomasi Backflush</button>
            </div>
            
            <script>
            function switchProdTab(tabName) {
                document.querySelectorAll('#view-production .ios-tab-main').forEach(function(btn) {
                    btn.style.borderBottom = 'none';
                    btn.style.color = 'var(--text-muted)';
                    btn.style.fontWeight = '500';
                });
                const activeBtn = document.querySelector('#view-production .ios-tab-main[data-prodtab="' + tabName + '"]');
                if (activeBtn) {
                    activeBtn.style.borderBottom = '2px solid var(--accent)';
                    activeBtn.style.color = 'var(--accent)';
                    activeBtn.style.fontWeight = '600';
                }
                
                document.getElementById('prod-tab-dashboard').style.display = tabName === 'dashboard' ? 'block' : 'none';
                document.getElementById('prod-tab-request').style.display = tabName === 'request' ? 'block' : 'none';
                document.getElementById('prod-tab-automation').style.display = tabName === 'automation' ? 'block' : 'none';
            }
            </script>
            
            <div id="prod-tab-dashboard">
            <!-- Production KPIs -->
            <div class="grid-4" style="margin-bottom: 24px;">
                <?php
                    $woTotal = \App\Models\ProductionOrder::where('company_id', $company->id)->count();
                    $woCompleted = \App\Models\ProductionOrder::where('company_id', $company->id)->where('status', 'completed')->count();
                    $totalWaste = \App\Models\ProductionWaste::where('company_id', $company->id)->sum('quantity');
                    
                    // Simple efficiency: (Completed / Total) if possible
                    $efficiency = $woTotal > 0 ? round(($woCompleted / $woTotal) * 100) : 100;
                ?>
                <div class="stat-card">
                    <div class="stat-title">Total Work Orders</div>
                    <div class="stat-value"><?php echo e($woTotal); ?></div>
                    <div class="stat-desc">Target produksi batch</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">WO Selesai</div>
                    <div class="stat-value" style="color: var(--success);"><?php echo e($woCompleted); ?></div>
                    <div class="stat-desc">Barang jadi ke gudang</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Total Waste & Scrap</div>
                    <div class="stat-value" style="color: var(--danger);"><?php echo e(number_format($totalWaste, 1)); ?></div>
                    <div class="stat-desc">Kerugian gramasi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Efisiensi Produksi</div>
                    <div class="stat-value" style="color: <?php echo e($efficiency >= 80 ? 'var(--success)' : 'var(--warning)'); ?>;"><?php echo e($efficiency); ?>%</div>
                    <div class="stat-desc">Tingkat penyelesaian</div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--panel-border); padding-bottom: 16px; margin-bottom: 16px;">
                    <h3 style="margin: 0; border: none; padding: 0;">Laporan Produksi Harian</h3>
                    <div style="color: var(--success); font-size: 13px; font-weight: bold;"><i class="fa-solid fa-link"></i> Terkoneksi ke Gudang Sentral</div>
                </div>
                
                <div class="grid-2">
                    <div>
                        <h4 style="margin: 0 0 12px 0; font-size: 14px;">Real-Time Stok Gudang</h4>
                        <div style="background: rgba(0,0,0,0.2); padding: 16px; border-radius: 8px; border: 1px solid var(--panel-border);">
                            <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                <thead>
                                    <tr style="border-bottom: 1px solid var(--panel-border); color: var(--text-muted); text-align: left;">
                                        <th style="padding: 8px;">Bahan Baku</th>
                                        <th style="padding: 8px;">Stok (Gram)</th>
                                        <th style="padding: 8px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\StockMovement::select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'))->where('company_id', $company->id)->groupBy('product_id')->with('product')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stock->product && $stock->product->type === 'raw_material'): ?>
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                            <td style="padding: 8px;">
                                                <?php echo e($stock->product->name); ?>

                                                <div style="font-size: 10px; color: var(--text-muted);">
                                                    Min: <?php echo e($stock->product->min_stock ?? 0); ?> | Max: <?php echo e($stock->product->max_stock ?? '-'); ?>

                                                </div>
                                            </td>
                                            <td style="padding: 8px; font-weight: bold;"><?php echo e(number_format($stock->total_qty, 0, ',', '.')); ?> <?php echo e($stock->product->unit ?? 'Gram'); ?></td>
                                            <td style="padding: 8px;">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stock->product->min_stock && $stock->total_qty < $stock->product->min_stock): ?>
                                                    <span class="pill" style="background: rgba(239,68,68,0.2); color: var(--danger);">Kritis (Bawah Min)</span>
                                                <?php elseif($stock->product->max_stock && $stock->total_qty > $stock->product->max_stock): ?>
                                                    <span class="pill" style="background: rgba(245,158,11,0.2); color: var(--warning);">Overstock</span>
                                                <?php else: ?>
                                                    <span class="pill" style="background: rgba(12, 53, 39,0.2); color: var(--success);">Aman</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td style="padding: 8px; text-align: right;">
                                                <button onclick="editMaterial('<?php echo e($stock->product->id); ?>', '<?php echo e(addslashes($stock->product->name)); ?>', '<?php echo e($stock->product->min_stock ?? 0); ?>', '<?php echo e($stock->product->max_stock ?? 0); ?>', '<?php echo e($stock->product->standard_cost ?? 0); ?>')" class="user-pill" style="background: rgba(255,255,255,0.1); border: none; cursor: pointer; color: white; padding: 4px 8px; font-size: 10px;"><i class="fa-solid fa-pen"></i> Edit</button>
                                            </td>
                                        </tr>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Modal Edit Material -->
                        <div id="modal-edit-material" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
                            <div style="background: var(--panel-bg); border: 1px solid var(--panel-border); padding: 24px; border-radius: 12px; width: 400px; max-width: 90%;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                    <h4 style="margin: 0;">Edit Bahan Baku</h4>
                                    <button onclick="document.getElementById('modal-edit-material').style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 16px;">&times;</button>
                                </div>
                                <form method="POST" action="<?php echo e(route('master-demo.products.updateMaterial') ?? '#'); ?>" id="form-edit-material">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="material_id" id="edit-mat-id">
                                    <div class="form-group">
                                        <label>Nama Bahan</label>
                                        <input type="text" id="edit-mat-name" class="form-control" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label>Batas Minimum (Min Stock)</label>
                                        <input type="number" step="0.01" name="min_stock" id="edit-mat-min" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Batas Maksimum (Max Stock)</label>
                                        <input type="number" step="0.01" name="max_stock" id="edit-mat-max" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Harga Rata-Rata per Gram (Rp)</label>
                                        <input type="number" step="0.01" name="standard_cost" id="edit-mat-cost" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Perubahan</button>
                                </form>
                            </div>
                        </div>

                        <script>
                        function editMaterial(id, name, min, max, cost) {
                            document.getElementById('edit-mat-id').value = id;
                            document.getElementById('edit-mat-name').value = name;
                            document.getElementById('edit-mat-min').value = min;
                            document.getElementById('edit-mat-max').value = max;
                            document.getElementById('edit-mat-cost').value = cost;
                            document.getElementById('modal-edit-material').style.display = 'flex';
                        }
                        </script>
                    </div>
                    
                    <div>
                        <h4 style="margin: 0 0 12px 0; font-size: 14px;">Input Batch Produksi Baru</h4>
                        <div style="background: rgba(0,0,0,0.2); padding: 16px; border-radius: 8px; border: 1px solid var(--panel-border);">
                            <form method="post" action="<?php echo e(route('master-demo.production.backflush')); ?>">
                                <?php echo csrf_field(); ?>
                                <div style="margin-bottom: 12px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-bottom: 4px;">
                                        <span>Pilih Resep</span>
                                        <span>Kuantitas (Batch)</span>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <select name="recipe_id" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); color: white; padding: 6px 12px; border-radius: 6px; flex: 2;" required>
                                            <option value="">-- Pilih Resep BOM --</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Recipe::where('company_id', $company->id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recipe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($recipe->id); ?>"><?php echo e($recipe->name); ?> (Yield: <?php echo e($recipe->yield_quantity); ?> <?php echo e($recipe->yield_unit); ?>)</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                        <input type="number" step="0.1" name="batch_quantity" placeholder="Berapa Resep" value="1" class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid var(--panel-border); color: white; padding: 6px 12px; border-radius: 6px; flex: 1;" required>
                                    </div>
                                </div>
                                
                                <button type="submit" class="user-pill" style="background: var(--accent); color: white; border: none; cursor: pointer; width: 100%; justify-content: center;"><i class="fa-solid fa-floppy-disk"></i> Simpan Produksi & Auto-Backflush</button>
                                <p style="font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 8px;">Dengan menyimpan, stok gudang akan otomatis terpotong sesuai kalkulasi gram. (Sistem mengunci jika stok bahan baku tidak cukup).</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            </div> <!-- End prod-tab-dashboard -->
            
            <!-- Tab: Request Bahan -->
            <div id="prod-tab-request" style="display: none;">
                <div style="text-align: center; padding: 40px 20px; background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--panel-border); border-radius: 12px;">
                    <i class="fa-solid fa-boxes-packing" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px; opacity: 0.5;"></i>
                    <h4 style="margin: 0 0 8px 0; color: var(--text-heading);">Fitur dalam tahap pengembangan</h4>
                    <p style="margin: 0; font-size: 14px; color: var(--text-muted);">Sistem pengajuan bahan baku dari pabrik ke gudang akan tersedia di pembaruan berikutnya.</p>
                </div>
            </div>
            
            <!-- Tab: Otomasi Backflush -->
            <div id="prod-tab-automation" style="display: none;">
                <div style="text-align: center; padding: 40px 20px; background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--panel-border); border-radius: 12px;">
                    <i class="fa-solid fa-robot" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px; opacity: 0.5;"></i>
                    <h4 style="margin: 0 0 8px 0; color: var(--text-heading);">Fitur dalam tahap pengembangan</h4>
                    <p style="margin: 0; font-size: 14px; color: var(--text-muted);">Pengaturan matriks pemotongan stok otomatis (Backflushing) akan tersedia di pembaruan berikutnya.</p>
                </div>
            </div>
        </section>

        <!-- HIERARKI ORGANISASI VIEW -->
        <section id="view-hierarchy" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h3>Hierarki Organisasi</h3>
                    <p class="desc">Struktur organisasi perusahaan. Kelola tim, departemen, dan pelaporan hierarki.</p>
                </div>
                <button class="user-pill" style="background: var(--accent); color: white; border: none; padding: 10px 20px; font-weight: bold; cursor: pointer;" onclick="document.getElementById('modal-hire-hierarchy').style.display='flex'">
                    <i class="fa-solid fa-user-plus"></i> Tambah Staf Baru
                </button>
            </div>

            <?php
                $allUsersHierarchy = \App\Models\User::where('company_id', $company->id)->where('is_active', true)->get();
                $divisions = $allUsersHierarchy->groupBy(function($u) {
                    return $u->division ?? $u->divisionLabel();
                });
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $divName => $divUsers): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="org-division-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <h4 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-layer-group" style="color: var(--accent);"></i> <?php echo e($divName); ?>

                        </h4>
                        <span class="user-pill" style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; font-size: 11px;"><?php echo e($divUsers->count()); ?> aktif</span>
                    </div>

                    <?php
                        // Pisahkan manager dan staff di dalam divisi ini
                        $managers = $divUsers->filter(fn($u) => $u->isManager() || $u->isCEO());
                        $staffs = $divUsers->reject(fn($u) => $u->isManager() || $u->isCEO());
                    ?>

                    <div class="org-tree">
                        <ul>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mgr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li>
                                <div class="org-card" style="border-color: rgba(12, 53, 39,0.5); box-shadow: 0 0 15px rgba(12, 53, 39,0.05);">
                                    <div class="org-avatar"><?php echo e($mgr->getInitials()); ?></div>
                                    <strong style="font-size: 15px; color: var(--text-heading); display: block;"><?php echo e($mgr->name); ?></strong>
                                    <span style="font-size: 12px; color: var(--text-muted);"><?php echo e($mgr->job_title ?? $mgr->role); ?></span>
                                    
                                    <div class="org-badges">
                                        <span class="org-badge badge-role">Manager</span>
                                        <span class="org-badge badge-type"><?php echo e($mgr->employment_type ?? 'Full-Time'); ?></span>
                                        <?php $pendingTasks = $mgr->tasks()->where('status', 'pending')->count(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingTasks > 0): ?>
                                            <span class="org-badge badge-task-active" onclick="alert('Membuka statistik performa/tugas untuk <?php echo e($mgr->name); ?>')"><?php echo e($pendingTasks); ?> tugas aktif</span>
                                        <?php else: ?>
                                            <span class="org-badge badge-task" onclick="alert('Membuka statistik performa/tugas untuk <?php echo e($mgr->name); ?>')">On Track</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    
                                    <div class="org-actions">
                                        <button onclick="openEditProfileModal(<?php echo e($mgr->id); ?>, '<?php echo e($mgr->name); ?>', '<?php echo e($mgr->job_title ?? ''); ?>', '<?php echo e($mgr->employment_type ?? 'Full-Time'); ?>', '<?php echo e($mgr->target_hours_per_month ?? 160); ?>')"><i class="fa-solid fa-pen"></i> Edit</button>
                                        <button onclick="openResetPasswordModal(<?php echo e($mgr->id); ?>, '<?php echo e(addslashes($mgr->name)); ?>')" style="color: #f59e0b;"><i class="fa-solid fa-key"></i> Sandi</button>
                                        <button class="btn-delete" onclick="confirmDeleteUser(<?php echo e($mgr->id); ?>)"><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </div>
                                </div>
                                
                                <?php
                                    $myStaffs = $staffs->filter(function($s) use ($mgr) {
                                        return $s->reports_to_id == $mgr->id || (!$s->reports_to_id && $s->manager() && $s->manager()->id == $mgr->id);
                                    });
                                ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($myStaffs->count() > 0): ?>
                                <ul>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $myStaffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <li>
                                        <div class="org-card" style="border-color: rgba(59,130,246,0.3);">
                                            <div class="org-avatar" style="background: #0C3527;"><?php echo e($staff->getInitials()); ?></div>
                                            <strong style="font-size: 15px; color: var(--text-heading); display: block;"><?php echo e($staff->name); ?></strong>
                                            <span style="font-size: 12px; color: var(--text-muted);"><?php echo e($staff->job_title ?? $staff->role); ?></span>
                                            
                                            <div class="org-badges">
                                                <span class="org-badge badge-role">Staff</span>
                                                <span class="org-badge badge-type"><?php echo e($staff->employment_type ?? 'Full-Time'); ?></span>
                                                <?php $pendingTasksStaff = $staff->tasks()->where('status', 'pending')->count(); ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingTasksStaff > 0): ?>
                                                    <span class="org-badge badge-task-active" onclick="alert('Membuka statistik performa/tugas untuk <?php echo e($staff->name); ?>')"><?php echo e($pendingTasksStaff); ?> tugas aktif</span>
                                                <?php else: ?>
                                                    <span class="org-badge badge-task" onclick="alert('Membuka statistik performa/tugas untuk <?php echo e($staff->name); ?>')">On Track</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            
                                            <div class="org-actions">
                                                <button onclick="openEditProfileModal(<?php echo e($staff->id); ?>, '<?php echo e($staff->name); ?>', '<?php echo e($staff->job_title ?? ''); ?>', '<?php echo e($staff->employment_type ?? 'Full-Time'); ?>', '<?php echo e($staff->target_hours_per_month ?? 160); ?>')"><i class="fa-solid fa-pen"></i> Edit</button>
                                                <button onclick="openResetPasswordModal(<?php echo e($staff->id); ?>, '<?php echo e(addslashes($staff->name)); ?>')" style="color: #f59e0b;"><i class="fa-solid fa-key"></i> Sandi</button>
                                                <button class="btn-delete" onclick="confirmDeleteUser(<?php echo e($staff->id); ?>)"><i class="fa-solid fa-trash"></i> Hapus</button>
                                            </div>
                                        </div>
                                    </li>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </ul>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            
                            <?php
                                $orphanStaffs = $staffs->reject(function($s) use ($managers) {
                                    return $managers->contains('id', $s->reports_to_id) || ($s->manager() && $managers->contains('id', $s->manager()->id));
                                });
                            ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $orphanStaffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li>
                                <div class="org-card" style="border-color: rgba(59,130,246,0.3);">
                                    <div class="org-avatar" style="background: #0C3527;"><?php echo e($staff->getInitials()); ?></div>
                                    <strong style="font-size: 15px; color: var(--text-heading); display: block;"><?php echo e($staff->name); ?></strong>
                                    <span style="font-size: 12px; color: var(--text-muted);"><?php echo e($staff->job_title ?? $staff->role); ?></span>
                                    
                                    <div class="org-badges">
                                        <span class="org-badge badge-role">Staff</span>
                                        <span class="org-badge badge-type"><?php echo e($staff->employment_type ?? 'Full-Time'); ?></span>
                                        <?php $pendingTasksStaff = $staff->tasks()->where('status', 'pending')->count(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingTasksStaff > 0): ?>
                                            <span class="org-badge badge-task-active" onclick="alert('Membuka statistik performa/tugas untuk <?php echo e($staff->name); ?>')"><?php echo e($pendingTasksStaff); ?> tugas aktif</span>
                                        <?php else: ?>
                                            <span class="org-badge badge-task" onclick="alert('Membuka statistik performa/tugas untuk <?php echo e($staff->name); ?>')">On Track</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    
                                    <div class="org-actions">
                                        <button onclick="openEditProfileModal(<?php echo e($staff->id); ?>, '<?php echo e($staff->name); ?>', '<?php echo e($staff->job_title ?? ''); ?>', '<?php echo e($staff->employment_type ?? 'Full-Time'); ?>', '<?php echo e($staff->target_hours_per_month ?? 160); ?>')"><i class="fa-solid fa-pen"></i> Edit</button>
                                        <button onclick="openResetPasswordModal(<?php echo e($staff->id); ?>, '<?php echo e(addslashes($staff->name)); ?>')" style="color: #f59e0b;"><i class="fa-solid fa-key"></i> Sandi</button>
                                        <button class="btn-delete" onclick="confirmDeleteUser(<?php echo e($staff->id); ?>)"><i class="fa-solid fa-trash"></i> Hapus</button>
                                    </div>
                                </div>
                            </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

            <!-- Modal Hire Employee Khusus Hierarchy -->
            <div id="modal-hire-hierarchy" class="modal-overlay" style="display: none;">
                <div class="modal-content" style="max-width: 600px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="margin: 0;">Tambah Staf / Rekrutmen</h3>
                        <i class="fa-solid fa-times" style="cursor: pointer; color: var(--text-muted);" onclick="document.getElementById('modal-hire-hierarchy').style.display='none'"></i>
                    </div>
                    <form method="POST" action="<?php echo e(route('master-demo.employee.hire')); ?>">
                        <?php echo csrf_field(); ?>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Nama Jabatan (Job Title)</label>
                                <input type="text" name="job_title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Tipe Pekerjaan</label>
                                <select name="employment_type" class="form-control" required>
                                    <option value="Full-Time">Full-Time</option>
                                    <option value="Part-Time">Part-Time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Paid Internship">Paid Internship</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nama Divisi (Opsional)</label>
                                <input type="text" name="division" class="form-control" placeholder="Biarkan kosong jika ikut divisi default atasan">
                                <small style="color: var(--text-muted); font-size: 11px;">(Hanya CEO yang bisa mem-bypass pembuatan Divisi baru tanpa ACC)</small>
                            </div>
                            <div class="form-group">
                                <label>Atasan Langsung (Reports To)</label>
                                <select name="reports_to_id" class="form-control" required>
                                    <option value="">-- Pilih Atasan --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $allUsersHierarchy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?> (<?php echo e($u->job_title); ?>)</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn" style="width: 100%;"><i class="fa-solid fa-paper-plane"></i> Daftarkan Karyawan</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- GOALS & KPI VIEW -->
        <section id="view-goals" class="view-section">
            <div class="content-header" style="margin-bottom: 24px;">
                <div>
                    <h3>Setup Goals & KPI (CEO)</h3>
                    <p class="desc">Tetapkan target tahunan perusahaan dan turunkan ke Manajer Divisi.</p>
                </div>
                <button class="btn" style="background: var(--accent);" onclick="document.getElementById('modal-add-goal').style.display='flex'">
                    <i class="fa-solid fa-plus"></i> Set Goal Baru
                </button>
            </div>

            <div class="card" style="background: var(--panel-bg); border: 1px solid var(--panel-border); padding: 24px; border-radius: 12px;">
                <h4 style="margin-bottom: 16px;">Daftar Corporate Goals</h4>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; text-align: left; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--panel-border);">
                                <th style="padding: 12px; font-size: 12px; color: var(--text-muted);">GOAL</th>
                                <th style="padding: 12px; font-size: 12px; color: var(--text-muted);">DITUJUKAN KE (MANAGER)</th>
                                <th style="padding: 12px; font-size: 12px; color: var(--text-muted);">STATUS</th>
                                <th style="padding: 12px; font-size: 12px; color: var(--text-muted);">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $ceoGoals = \App\Models\Task::with('user')->where('company_id', $company->id)
                                    ->whereNull('parent_id')
                                    ->where('user_id', '!=', $user->id)
                                    ->get();
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ceoGoals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 12px;">
                                    <strong><?php echo e($goal->title); ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;"><?php echo e(Str::limit($goal->description ?? 'Tidak ada deskripsi', 50)); ?></div>
                                </td>
                                <td style="padding: 12px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="avatar-sm" style="width:24px; height:24px; border-radius:50%; background:var(--accent); color:white; display:flex; align-items:center; justify-content:center; font-size:10px;"><?php echo e($goal->user->getInitials() ?? 'U'); ?></div>
                                        <span><?php echo e($goal->user->name ?? 'N/A'); ?></span>
                                    </div>
                                </td>
                                <td style="padding: 12px;">
                                    <span class="org-badge badge-role" style="font-size: 10px;"><?php echo e($goal->status); ?></span>
                                </td>
                                <td style="padding: 12px;">
                                    <button class="user-pill" style="background: rgba(255,255,255,0.1); border:none; color:white; cursor:pointer;"><i class="fa-solid fa-eye"></i> Detail</button>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="4" style="padding: 24px; text-align: center; color: var(--text-muted);">
                                    Belum ada Goals yang diset untuk Manajer.
                                </td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Modal Add Goal -->
        <div id="modal-add-goal" class="modal-overlay">
            <div class="modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h4 style="margin: 0;">Set Corporate Goal Baru</h4>
                    <button onclick="document.getElementById('modal-add-goal').style.display='none'" style="background: none; border: none; color: white; cursor: pointer; font-size: 16px;">&times;</button>
                </div>
                <form method="POST" action="<?php echo e(route('master-demo.tasks.store') ?? '#'); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label>Judul Goal</label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Ekspansi Pasar 2026" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan secara singkat..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Tugaskan Kepada Manajer</label>
                        <select name="username" class="form-control" required>
                            <?php
                                $allManagers = \App\Models\User::where('company_id', $company->id)
                                    ->get()
                                    ->filter(fn($u) => $u->isManager());
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $allManagers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mgr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($mgr->username); ?>"><?php echo e($mgr->name); ?> (<?php echo e($mgr->division ?? 'Manajer'); ?>)</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn" style="width: 100%; justify-content: center; background: var(--accent);">
                        <i class="fa-solid fa-paper-plane"></i> Tetapkan Goal
                    </button>
                </form>
            </div>
        </div>

    
        <!-- NEW MODULES (TODO / Under Construction) -->
        <section id="view-documents" class="view-section">
            <?php echo $__env->make('documents.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </section>

        <section id="view-project_costing" class="view-section">
            <div class="card">
                <h3>Project Costing & Profitability</h3>
                <p>Backend Integrated - UI Pending</p>
                <div style="padding: 20px; background: rgba(0,0,0,0.1); border-radius: 8px; border: 1px dashed var(--accent);">
                    [UI Construction in Progress: Project Financials and Resource Costing]
                </div>
            </div>
        </section>

        <section id="view-payroll" class="view-section">
            <?php echo $__env->make('payroll.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </section>

        <section id="view-alumni_network" class="view-section">
            <div class="card">
                <h3>Alumni Network</h3>
                <p>Backend Integrated - UI Pending</p>
                <div style="padding: 20px; background: rgba(0,0,0,0.1); border-radius: 8px; border: 1px dashed var(--accent);">
                    [UI Construction in Progress: Corporate Alumni Portal]
                </div>
            </div>
        </section>

        <section id="view-client_portal" class="view-section">
            <div class="card">
                <h3>Client & Vendor Portal</h3>
                <p style="color: var(--danger); font-weight: bold;">TODO: Backend Not Available</p>
                <div style="padding: 20px; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border: 1px dashed var(--danger);">
                    [Feature completely missing backend controllers]
                </div>
            </div>
        </section>

        <section id="view-intelligence" class="view-section">
            <div class="card">
                <h3>Analytics & AI (Gemini)</h3>
                <p>Backend Integrated - UI Pending</p>
                <div style="padding: 20px; background: rgba(0,0,0,0.1); border-radius: 8px; border: 1px dashed var(--accent);">
                    [UI Construction in Progress: Gemini AI Chat Interface]
                </div>
            </div>
        </section>
        
        <section id="view-pos" class="view-section">
            <div class="card">
                <h3>Cashier / POS</h3>
                <p>This module's reporting is currently merged into CRM & Sales dashboard.</p>
                <div style="padding: 20px; text-align: center;">
                    <button class="btn" onclick="switchView('crm')">Go to Sales</button>
                </div>
            </div>
        </section>

        <?php echo $__env->make('inventory-umkm.index', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <section id="view-modules" class="view-section">
            <div class="card">
                <h3>System Modules Control</h3>
                <p class="desc" style="margin-bottom: 24px;">Manage activation state of all ERP modules for this company/tenant.</p>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="list-item">
                        <div>
                            <div class="title"><?php echo e($module['label']); ?></div>
                            <div class="desc">Group: <?php echo e($module['group']); ?> 
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$module['permanent'] && count($module['dependencies'])): ?>
                                    | Requires: <?php echo e(implode(', ', $module['dependencies'])); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($module['permanent']): ?>
                                <span style="background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: bold; color: var(--text-muted);">CORE (ALWAYS ON)</span>
                            <?php else: ?>
                                <form method="post" action="<?php echo e(route('master-demo.feature', ['company' => $company->id, 'feature' => $module['key']])); ?>" style="margin: 0; width: 130px;">
                                    <?php echo csrf_field(); ?> <?php echo method_field('patch'); ?>
                                    <select name="state" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--panel-border); background: <?php echo e($module['state'] === 'active' ? 'rgba(12, 53, 39,0.1)' : ($module['state'] === 'off' ? 'rgba(239,68,68,0.1)' : 'var(--panel-bg)')); ?>; color: <?php echo e($module['state'] === 'active' ? 'var(--success)' : ($module['state'] === 'off' ? 'var(--danger)' : 'var(--text-main)')); ?>; font-weight: 600; cursor: pointer; outline: none; font-size: 12px;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['active' => 'Active', 'read_only' => 'Read-only', 'off' => 'Off']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($value); ?>" <?php if($module['state'] === $value): echo 'selected'; endif; ?> style="color: #333; background: #fff;"><?php echo e($label); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section id="view-core_workflow" class="view-section">
            <div class="card">
                <h3>Approval, Notification & Backup</h3>
                <p>Workflow settings and global approvals.</p>
            </div>
        </section>

        <!-- AUTOMATION ENGINE VIEW -->
        <section id="view-automation_engine" class="view-section">
            <div class="card" style="text-align: center; padding: 48px; border-left: 4px solid var(--accent);">
                <i class="fa-solid fa-microchip" style="font-size: 48px; color: var(--accent); margin-bottom: 24px;"></i>
                <h3 style="font-size: 24px; color: var(--text-heading); margin-bottom: 12px; font-weight: 800;">Automation & Cron Engine</h3>
                <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 32px; max-width: 500px; margin-left: auto; margin-right: auto;">
                    Sistem otomasi berjalan di belakang layar untuk menangani antrean proses, pengingat, dan sinkronisasi data antar modul tanpa mengganggu performa server.
                </p>
                <div class="grid-2" style="text-align: left;">
                    <div class="list-item card" style="padding: 16px;">
                        <div>
                            <div class="title" style="color: var(--success);"><i class="fa-solid fa-circle-check"></i> automation:process-reports</div>
                            <div class="desc mt-1">Mengantre kalkulasi laporan harian secara background.</div>
                        </div>
                        <span style="background: rgba(12, 53, 39,0.1); color: var(--success); padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">ACTIVE</span>
                    </div>
                    <div class="list-item card" style="padding: 16px;">
                        <div>
                            <div class="title" style="color: var(--success);"><i class="fa-solid fa-circle-check"></i> automation:send-reminders</div>
                            <div class="desc mt-1">Menyiarkan notifikasi pengingat secara terjadwal.</div>
                        </div>
                        <span style="background: rgba(12, 53, 39,0.1); color: var(--success); padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">ACTIVE</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal Reset Password -->
        <div id="modal-reset-password" class="modal-overlay" style="display: none;">
            <div class="modal-content" style="max-width: 400px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0;"><i class="fa-solid fa-key" style="color: #f59e0b;"></i> Reset Password</h3>
                    <i class="fa-solid fa-times" style="cursor: pointer; color: var(--text-muted);" onclick="document.getElementById('modal-reset-password').style.display='none'"></i>
                </div>
                <form method="POST" action="<?php echo e(route('master-demo.employee.reset-password')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="user_id" id="reset-user-id">
                    <p style="font-size: 14px; margin-bottom: 16px;">Anda akan mereset password untuk <strong id="reset-user-name"></strong>.</p>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label>Password Baru</label>
                        <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimal 8 karakter">
                    </div>
                    <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%;"><i class="fa-solid fa-save"></i> Simpan Password</button>
                </form>
            </div>
        </div>

        <!-- HR LEGAL & PAKLARING VIEW -->
        <section id="view-hr_legal" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h3 style="margin: 0; margin-bottom: 4px;">Surat Legal & Paklaring</h3>
                    <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Penerbitan dan rekam jejak dokumen ketenagakerjaan (Certificate of Employment).</p>
                </div>
                <button class="ios-btn ios-btn-primary"><i class="fa-solid fa-plus"></i> Terbitkan Dokumen</button>
            </div>
            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-file-contract" style="font-size: 48px; color: var(--panel-border); margin-bottom: 16px;"></i>
                <h4 style="margin: 0 0 8px 0;">Struktur Database Siap</h4>
                <p style="color: var(--text-muted); font-size: 14px; max-width: 400px; margin: 0 auto;">Engine model HrPaklaring sudah tertanam dan beroperasi di belakang layar. UI list dokumen sedang dalam tahap sinkronisasi front-end.</p>
            </div>
        </section>

        <!-- SP KARYAWAN VIEW -->
        <section id="view-warning_letters" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h3 style="margin: 0; margin-bottom: 4px;">Surat Peringatan (Indisipliner)</h3>
                    <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Manajemen sanksi, SP1, SP2, hingga Pemutusan Hubungan Kerja (PHK).</p>
                </div>
                <button class="ios-btn ios-btn-primary"><i class="fa-solid fa-plus"></i> Buat SP Baru</button>
            </div>
            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-envelope-open-text" style="font-size: 48px; color: var(--panel-border); margin-bottom: 16px;"></i>
                <h4 style="margin: 0 0 8px 0;">Sistem Pencatatan Aktif</h4>
                <p style="color: var(--text-muted); font-size: 14px; max-width: 400px; margin: 0 auto;">Model HrWarningLetter telah terintegrasi dengan data pegawai. Seluruh peringatan indisipliner akan terekam secara persisten.</p>
            </div>
        </section>

        <!-- HR OVERTIME VIEW -->
        <section id="view-hr_overtime" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h3 style="margin: 0; margin-bottom: 4px;">Logika Overtime Khusus</h3>
                    <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Manajemen shift malam dan perhitungan overtime lintas hari (late-night multiplier).</p>
                </div>
            </div>
            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-business-time" style="font-size: 48px; color: var(--panel-border); margin-bottom: 16px;"></i>
                <h4 style="margin: 0 0 8px 0;">Algoritma Terpasang: Multiplier Aktif</h4>
                <p style="color: var(--text-muted); font-size: 14px; max-width: 400px; margin: 0 auto;">Attendance engine kini otomatis menghitung lembur di atas pukul 23:00 dengan pengali 2x (double rate) tanpa konfigurasi manual HR.</p>
            </div>
        </section>

        <!-- HR ATTENDANCE ADV VIEW -->
        <section id="view-hr_attendance_adv" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h3 style="margin: 0; margin-bottom: 4px;">Advanced Attendance & Limits</h3>
                    <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Logika penyembunyian tap tidak valid tanpa merusak audit log.</p>
                </div>
            </div>
            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-user-clock" style="font-size: 48px; color: var(--panel-border); margin-bottom: 16px;"></i>
                <h4 style="margin: 0 0 8px 0;">Safe Deletion Mode Beroperasi</h4>
                <p style="color: var(--text-muted); font-size: 14px; max-width: 400px; margin: 0 auto;">Penggunaan flag <code>is_hidden</code> telah diaktifkan pada tabel absensi. HR dapat menyembunyikan jam masuk spam tanpa kehilangan data audit.</p>
            </div>
        </section>
        <!-- ATTENDANCE & GPS VIEW -->
        <section id="view-location_tracking" class="view-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h3 style="margin: 0; margin-bottom: 4px;">Attendance & GPS Dashboard</h3>
                    <p style="margin: 0; color: var(--text-muted); font-size: 14px;">Monitor kehadiran, atur jam lembur, istirahat, dan backup data.</p>
                </div>
                <a href="<?php echo e(route('master-demo.backup')); ?>" class="ios-btn ios-btn-primary" style="text-decoration: none;">
                    <i class="fa-solid fa-download" style="margin-right: 8px;"></i> Backup Data Absensi
                </a>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                <!-- Setup Waktu Istirahat -->
                <div class="card" style="padding: 24px; border: 1px solid var(--panel-border); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <h4 style="margin: 0 0 16px 0; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-clock" style="color: var(--accent); margin-right: 8px;"></i> Setup Waktu Istirahat Wajib</h4>
                    <form id="form-rest" method="POST" action="<?php echo e(route('master-demo.attendance-settings.store')); ?>" style="margin-bottom: 20px;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="_method" id="rest-method" value="POST">
                        <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                            <input type="text" id="rest-name" name="name" class="form-control" placeholder="Nama Istirahat (Ex: Sholat Jumat)" required style="flex: 2; padding: 12px; border-radius: 12px; border: 1px solid var(--panel-border);">
                            <div style="flex: 1; display: flex; align-items: center; gap: 8px;">
                                <label style="font-size: 11px; color: var(--text-muted);">Mulai</label>
                                <input type="time" id="rest-start" name="rest_start_time" class="form-control" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--panel-border); width: 100%;">
                            </div>
                            <div style="flex: 1; display: flex; align-items: center; gap: 8px;">
                                <label style="font-size: 11px; color: var(--text-muted);">Selesai</label>
                                <input type="time" id="rest-end" name="rest_end_time" class="form-control" required style="padding: 12px; border-radius: 12px; border: 1px solid var(--panel-border); width: 100%;">
                            </div>
                            <button type="submit" id="rest-btn" class="ios-btn ios-btn-primary" style="padding: 0 20px; border-radius: 12px;"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </form>
                    
                    <table class="data-table" style="width: 100%; font-size: 13px; border-collapse: separate; border-spacing: 0 8px;">
                        <thead>
                            <tr>
                                <th style="padding: 8px 12px; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--panel-border);">Nama Istirahat</th>
                                <th style="padding: 8px 12px; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--panel-border);">Mulai - Selesai</th>
                                <th style="padding: 8px 12px; color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--panel-border);">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $attendanceSettings ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr style="background: var(--panel-secondary); box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                                <td style="padding: 12px; border-radius: 12px 0 0 12px; font-weight: 500;"><?php echo e($setting->name); ?></td>
                                <td style="padding: 12px;"><?php echo e(\Carbon\Carbon::parse($setting->rest_start_time)->format('H:i')); ?> - <?php echo e(\Carbon\Carbon::parse($setting->rest_end_time)->format('H:i')); ?></td>
                                <td style="padding: 12px; border-radius: 0 12px 12px 0; text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <button type="button" class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px;" onclick="editRest('<?php echo e($setting->id); ?>', '<?php echo e($setting->name); ?>', '<?php echo e(\Carbon\Carbon::parse($setting->rest_start_time)->format('H:i')); ?>', '<?php echo e(\Carbon\Carbon::parse($setting->rest_end_time)->format('H:i')); ?>')">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <form method="POST" action="/master-demo/attendance-settings/<?php echo e($setting->id); ?>" onsubmit="return confirm('Hapus waktu istirahat ini?');" style="margin: 0;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="ios-btn" style="padding: 6px 12px; font-size: 12px; background: rgba(239,68,68,0.1); color: var(--danger); border: none;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 16px; color: var(--text-muted); font-style: italic;">Belum ada waktu istirahat diatur</td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                    <script>
                        function editRest(id, name, start, end) {
                            document.getElementById('form-rest').action = '/master-demo/attendance-settings/' + id;
                            document.getElementById('rest-method').value = 'PUT';
                            document.getElementById('rest-name').value = name;
                            document.getElementById('rest-start').value = start;
                            document.getElementById('rest-end').value = end;
                            document.getElementById('rest-btn').innerHTML = '<i class="fa-solid fa-check"></i>';
                        }
                    </script>
                </div>

                <!-- Setup Jam Lembur -->
                <div class="card" style="padding: 24px; border: 1px solid var(--panel-border); border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <h4 style="margin-top: 0; margin-bottom: 16px; font-size: 15px; color: var(--text-heading);"><i class="fa-solid fa-business-time" style="color: var(--accent); margin-right: 8px;"></i> Setup Jenis Lembur</h4>
                    <form id="form-overtime" method="POST" action="<?php echo e(route('master-demo.overtime.store')); ?>" style="margin-bottom: 20px;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="_method" id="ot-method" value="POST">
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="ot-name" name="name" class="form-control" placeholder="Nama Lembur (Ex: Hari Libur)" required style="flex: 2; padding: 12px; border-radius: 12px; border: 1px solid var(--panel-border);">
                            <input type="number" id="ot-rate" name="rate_per_hour" class="form-control" placeholder="Tarif/Jam" required style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid var(--panel-border);">
                            <button type="submit" id="ot-btn" class="ios-btn ios-btn-primary" style="padding: 0 20px; border-radius: 12px;"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </form>
                    <table class="data-table" style="width: 100%; font-size: 13px; border-collapse: separate; border-spacing: 0 8px;">
                        <thead>
                            <tr>
                                <th style="padding: 8px 12px; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--panel-border);">Jenis Lembur</th>
                                <th style="padding: 8px 12px; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--panel-border);">Tarif/Jam</th>
                                <th style="padding: 8px 12px; color: var(--text-muted); font-weight: 600; text-align: right; border-bottom: 1px solid var(--panel-border);">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $overtimeTypes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr style="background: var(--panel-secondary); box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                                <td style="padding: 12px; border-radius: 12px 0 0 12px; font-weight: 500;"><?php echo e($ot->name); ?></td>
                                <td style="padding: 12px;">Rp <?php echo e(number_format($ot->rate_per_hour, 0, ',', '.')); ?></td>
                                <td style="padding: 12px; border-radius: 0 12px 12px 0; text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <button type="button" onclick="editOT('<?php echo e($ot->id); ?>', '<?php echo e($ot->name); ?>', '<?php echo e($ot->rate_per_hour); ?>')" class="ios-btn" style="padding: 6px 12px; font-size: 11px; background: rgba(59,130,246,0.1); color: var(--text-accent); border-radius: 20px;"><i class="fa-solid fa-pencil"></i></button>
                                        <form method="POST" action="/master-demo/overtime/<?php echo e($ot->id); ?>" onsubmit="return confirm('Hapus jenis lembur ini?');" style="margin: 0;">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="ios-btn" style="padding: 6px 12px; font-size: 11px; background: rgba(239,68,68,0.1); color: #ef4444; border-radius: 20px;"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">Belum ada setup lembur.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                    <script>
                        function editOT(id, name, rate) {
                            document.getElementById('form-overtime').action = '/master-demo/overtime/' + id;
                            document.getElementById('ot-method').value = 'PUT';
                            document.getElementById('ot-name').value = name;
                            document.getElementById('ot-rate').value = rate;
                            document.getElementById('ot-btn').innerHTML = '<i class="fa-solid fa-check"></i>';
                            document.getElementById('ot-name').focus();
                        }
                    </script>
                </div>
            </div>

            <!-- Riwayat Absensi -->
            <div class="card" style="padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h4 style="margin: 0;"><i class="fa-solid fa-list" style="margin-right: 8px;"></i> Riwayat Absensi & GPS</h4>
                    <div>
                        <select class="form-control" style="width: auto; display: inline-block;">
                            <option value="daily">Harian</option>
                            <option value="weekly">Mingguan</option>
                            <option value="monthly">Bulanan</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Status</th>
                                <th>Lokasi GPS</th>
                                <th>Lembur / Luar Jam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $attendances ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($att->clock_in->format('d M Y')); ?></td>
                                <td><strong><?php echo e($att->user->name ?? '-'); ?></strong></td>
                                <td><?php echo e($att->clock_in->format('H:i')); ?></td>
                                <td><?php echo e($att->clock_out ? $att->clock_out->format('H:i') : '-'); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($att->status == 'Present'): ?>
                                    <span style="color: var(--success); font-weight: bold;">Tepat Waktu</span>
                                    <?php elseif($att->status == 'Late'): ?>
                                    <span style="color: var(--danger); font-weight: bold;">Terlambat</span>
                                    <?php else: ?>
                                    <?php echo e($att->status); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($att->location_coordinates): ?>
                                        <a href="https://maps.google.com/?q=<?php echo e($att->location_coordinates); ?>" target="_blank" style="color: var(--accent);"><i class="fa-solid fa-map-marker-alt" style="margin-right: 4px;"></i> <?php echo e($att->location_name ?? 'Cek Map'); ?></a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);"><?php echo e($att->location_name ?? '-'); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($att->is_out_of_hours): ?>
                                    <span style="background: var(--warning); color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 11px;">Absen Ekstra</span>
                                    <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">Belum ada data absensi.</td>
                            </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
        </section>

        <!-- INTERNAL CHAT MODULE (Restored for CEO) -->
        <section id="view-chat_internal" class="view-section">
            <div class="chat-layout <?php echo e((auth()->check() && (auth()->user()->isCEO() || auth()->user()->isManager() || auth()->user()->isPlatformAdmin())) ? 'has-announcements' : ''); ?>">
                <!-- Sidebar Kontak -->
                <div class="chat-channels">
                    <div style="padding: 16px; border-bottom: 1px solid var(--panel-border);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Obrolan</h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check() && (auth()->user()->isCEO() || auth()->user()->isPlatformAdmin())): ?>
                            <button class="ios-btn ios-btn-primary" style="font-size: 11px; padding: 4px 8px;" onclick="document.getElementById('modal-create-channel').style.display='flex'">
                                <i class="fa-solid fa-plus"></i> Grup
                            </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <input type="text" placeholder="Cari pesan atau grup..." style="width: 100%; margin-top: 12px; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--panel-border); background: var(--bg-main); color: var(--text-main); outline: none; font-size: 13px;">
                    </div>
                    <div style="padding: 16px; font-weight: bold; border-bottom: 1px solid var(--panel-border); color: var(--text-muted);">Divisions</div>
                    <div id="chat-channels-list">
                        <!-- Channels List -->
                        <div class="channel-item active" onclick="selectChatChannel('general', 'Grup General')" id="chat-item-general">
                            <i class="fa-solid fa-hashtag"></i> Grup General
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isCEO() || auth()->user()->isManager() || auth()->user()->isPlatformAdmin()): ?>
                        <div class="channel-item" onclick="selectChatChannel('managers', 'Grup Manager')" id="chat-item-managers">
                            <i class="fa-solid fa-hashtag"></i> Grup Manager
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div id="main-chat-divisions"></div>
                    </div>
                </div>
                
                <!-- Main Chat Window -->
                <div class="chat-window">
                    <!-- Header -->
                    <div class="chat-header">
                        <span style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-hashtag" style="color: var(--primary)"></i> 
                            <span id="chat-header-title">Grup General</span>
                        </span>
                        <div><i class="fa-solid fa-circle-info" style="color: var(--text-muted); cursor: pointer;"></i></div>
                    </div>
                    
                    <!-- Chat History -->
                    <div class="chat-messages" id="chat-history-container">
                        <div style="text-align: center; color: var(--text-muted); font-size: 12px;">Memuat obrolan...</div>
                    </div>
                    
                    <!-- Input Area -->
                    <form id="chat-send-form" class="chat-input" onsubmit="submitChatMessage(event)">
                        <input type="file" id="main-chat-attachment" style="display:none;" onchange="handleMainAttachment(event)">
                        <button type="button" onclick="document.getElementById('main-chat-attachment').click()" style="background:none; border:none; color:var(--text-muted); font-size: 20px;"><i class="fa-solid fa-paperclip"></i></button>
                        
                        <div id="main-attachment-preview" style="display:none; padding:4px 12px; background:var(--bg-main); border-radius:12px; align-items:center; gap:8px;">
                            <span id="main-attachment-name" style="font-size:11px; font-weight:600;">file.pdf</span>
                            <button type="button" onclick="clearMainAttachment()" style="background:none; border:none; color:var(--danger); width:auto; height:auto; padding:0;"><i class="fa-solid fa-times"></i></button>
                        </div>
                        
                        <input type="text" id="chat-message-input" placeholder="Type a message...">
                        <button type="submit" id="main-chat-submit-btn"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                </div>

                <!-- Third Column: Manajemen Pengumuman (Only for CEO/Manager) -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check() && (auth()->user()->isCEO() || auth()->user()->isManager() || auth()->user()->isPlatformAdmin())): ?>
                <div class="chat-window" style="background: var(--panel-bg);">
                    <div style="padding: 16px; border-bottom: 1px solid var(--panel-border); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; font-size: 15px; font-weight: 700;"><i class="fa-solid fa-bullhorn" style="color: var(--primary); margin-right: 8px;"></i> Pengumuman</h3>
                        <button class="ios-btn ios-btn-primary" style="font-size: 11px; padding: 6px 10px;" onclick="document.getElementById('modal-create-announcement').style.display='flex'">
                            <i class="fa-solid fa-plus"></i> Buat
                        </button>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isCEO()): ?>
                    <div style="padding: 12px 16px; border-bottom: 1px solid var(--panel-border);">
                        <select id="announcement-bulk-delete" onchange="if(this.value){bulkDeleteAnnouncements(this.value); this.value='';}" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--panel-border); font-size: 12px; outline: none; cursor: pointer; background: var(--bg-main); color: var(--text-main);">
                            <option value="">Bulk Delete Berdasarkan Waktu...</option>
                            <option value="daily">Hapus Lebih dari 1 Hari</option>
                            <option value="weekly">Hapus Lebih dari 1 Minggu</option>
                            <option value="monthly">Hapus Lebih dari 1 Bulan</option>
                        </select>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div style="flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 12px;">
                        <?php
                            $activeAnnouncements = \App\Models\Announcement::latest()->take(10)->get();
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $activeAnnouncements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ann): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div style="padding: 12px; border: 1px solid var(--panel-border); border-radius: 8px; background: var(--bg-main); display: flex; flex-direction: column;">
                                <h4 style="margin: 0 0 4px 0; font-size: 13px; color: var(--text-main);"><?php echo e($ann->title); ?> <span style="font-size: 9px; padding: 2px 6px; border-radius: 4px; background: rgba(12, 53, 39, 0.1); color: var(--primary); margin-left: 6px;"><?php echo e(strtoupper($ann->target_type)); ?></span></h4>
                                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;"><?php echo e(Str::limit($ann->content, 60)); ?></div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="font-size: 10px; color: var(--text-muted);"><i class="fa-regular fa-clock"></i> <?php echo e($ann->created_at->format('d M Y, H:i')); ?></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isCEO()): ?>
                                    <button class="ios-btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 4px 8px; font-size: 10px;" onclick="deleteAnnouncement(<?php echo e($ann->id); ?>)"><i class="fa-solid fa-trash"></i></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div style="text-align: center; color: var(--text-muted); font-size: 12px; padding: 20px;">Belum ada pengumuman aktif.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

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

    <div style="text-align: center;">
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['variant' => 'danger','type' => 'button','id' => 'confirm-btn','onclick' => 'document.getElementById(\'confirm-form\').submit();']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','type' => 'button','id' => 'confirm-btn','onclick' => 'document.getElementById(\'confirm-form\').submit();']); ?>
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
            document.getElementById('metrics-crm-value').innerHTML = 'Rp 14.5B';
            document.getElementById('metrics-po-value').innerHTML = '32';
            document.getElementById('metrics-qa-value').innerHTML = '1.2%';
            document.getElementById('metrics-inv-value').innerHTML = 'Rp 8.2B';
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
                document.getElementById('metrics-crm-value').innerText = formatCurrency(data.crm.open_pipeline_value);
                document.getElementById('crm-total-leads').innerText = data.crm.total_leads;
                document.getElementById('crm-win-rate').innerText = data.crm.conversion_rate + '%';
                document.getElementById('crm-won-value').innerText = formatCurrency(data.crm.won_value);
            }
            if(data.purchasing) {
                document.getElementById('metrics-po-value').innerText = data.purchasing.pending_receipts + ' Orders';
            }
            if(data.production) {
                document.getElementById('metrics-qa-value').innerText = data.production.defect_rate + '%';
            }
            if(data.inventory) {
                document.getElementById('metrics-inv-value').innerText = formatCurrency(data.inventory.estimated_valuation);
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
    <div class="modal-content ios-modal" style="width: 480px; max-width: 95vw; padding: 24px;">
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
    <div class="modal-content ios-modal" style="width: 440px; max-width: 95vw; padding: 28px;">
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
    <div class="modal-content ios-modal" style="width: 400px; max-width: 95vw; padding: 28px;">
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
    <div class="modal-content ios-modal" style="width: 400px; max-width: 95vw; padding: 28px;">
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
    <div class="modal-content ios-modal" style="width: 400px; max-width: 95vw; padding: 28px;">
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
    <div class="modal-content ios-modal" style="width: 400px; max-width: 90vw; padding: 32px 24px; text-align: center;">
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
    <div class="modal-content ios-modal" style="width: 500px; max-width: 90vw; padding: 32px 24px;">
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
    <div class="modal-content ios-modal" style="width: 500px; max-width: 90vw; padding: 32px 24px;">
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
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...';
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
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...';
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

<?php /**PATH D:\suba-erp-master-local-latest\resources\views/master-portal.blade.php ENDPATH**/ ?>