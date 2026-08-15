<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Center - Suba ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        body { background-color: #F8FAFC; color: #374151; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col relative overflow-x-hidden">
    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/70 backdrop-blur-xl border-b border-gray-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4">
                    <a href="<?php echo e(url('/master-demo/app')); ?>" class="text-sm font-semibold text-gray-500 hover:text-blue-600 flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Portal CEO
                    </a>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-900 font-bold tracking-tight">Master Data Center</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                        <?php echo e(strtoupper(substr(auth()->user()->name ?? 'User', 0, 2))); ?>

                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full relative">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Background Decoration -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-10"></div>
        <div class="absolute top-40 -left-40 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl opacity-10"></div>
    </div>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\Modules\MasterData\resources\views\layouts\master.blade.php ENDPATH**/ ?>