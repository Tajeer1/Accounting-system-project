<header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-8 sticky top-0 z-30">
    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500">
            <x-icon name="menu" class="w-6 h-6" />
        </button>
        <div>
            <h1 class="text-base font-bold text-slate-900">@yield('page_title', 'لوحة التحكم')</h1>
            <p class="text-xs text-slate-500 mt-0.5">@yield('page_subtitle', 'نظرة عامة على النظام')</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600">
            <x-icon name="calendar" class="w-4 h-4 text-slate-400" />
            {{ now()->translatedFormat('d M Y') }}
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200">
            <div class="w-7 h-7 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">
                م
            </div>
            <div class="hidden sm:block">
                <div class="text-xs font-semibold text-slate-900">مدير النظام</div>
                <div class="text-[10px] text-slate-500">Admin</div>
            </div>
        </div>
    </div>
</header>
