<div x-data="{ searchOpen: false, query: '' }" class="relative z-50">
    <!-- Search Input -->
    <div class="relative flex items-center w-full md:w-80">
        <input 
            type="text" 
            x-model="query"
            @focus="searchOpen = true"
            @click.away="searchOpen = false"
            @keydown.escape="searchOpen = false"
            placeholder="Search everything... (⌘K)" 
            class="w-full bg-gray-100/80 backdrop-blur-md border border-gray-200/50 text-sm rounded-full pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all shadow-inner text-gray-700"
        >
        <svg class="w-4 h-4 text-gray-500 absolute left-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        
        <!-- Mac Keyboard shortcut hint -->
        <span class="absolute right-3 hidden md:flex items-center justify-center bg-white border border-gray-200 rounded text-xs text-gray-400 px-1.5 py-0.5">
            ⌘K
        </span>
    </div>

    <!-- Search Dropdown Results -->
    <div 
        x-show="searchOpen && query.length > 1" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute top-12 left-0 w-full md:w-[400px] bg-white/90 backdrop-blur-2xl rounded-2xl shadow-xl border border-gray-100/50 overflow-hidden"
        style="display: none;"
    >
        <div class="p-3 text-xs font-semibold text-gray-400 uppercase tracking-wider bg-gray-50/50 border-b border-gray-100">
            Hasil Pencarian
        </div>
        <div class="max-h-80 overflow-y-auto p-2">
            <!-- Simulated Result 1 -->
            <a href="#" class="flex items-center px-4 py-3 hover:bg-gray-50 rounded-xl transition-colors">
                <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900" x-text="query + ' (Pencocokan Barang)'"></p>
                    <p class="text-xs text-gray-500">Master Data &rsaquo; Produk</p>
                </div>
            </a>
            
            <!-- Simulated Result 2 -->
            <a href="#" class="flex items-center px-4 py-3 hover:bg-gray-50 rounded-xl transition-colors">
                <div class="bg-purple-100 text-purple-600 p-2 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900" x-text="query + ' (Pencocokan Supplier)'"></p>
                    <p class="text-xs text-gray-500">Master Data &rsaquo; Rekanan</p>
                </div>
            </a>
        </div>
        <div class="p-3 bg-gray-50/50 border-t border-gray-100 text-center">
            <a href="#" class="text-xs font-medium text-blue-600 hover:text-blue-700">Lihat semua hasil untuk "<span x-text="query"></span>"</a>
        </div>
    </div>
    
    <!-- Keyboard Shortcut Listener -->
    <script>
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                const input = document.querySelector('input[placeholder="Search everything... (⌘K)"]');
                if (input) input.focus();
            }
        });
    </script>
</div><?php /**PATH D:\suba-erp-master-local-latest\resources\views\components\global-search.blade.php ENDPATH**/ ?>