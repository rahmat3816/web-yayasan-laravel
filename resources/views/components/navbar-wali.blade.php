<!-- Navbar untuk Dashboard Wali -->
<header class="fixed top-0 left-0 right-0 md:left-64 z-30 bg-white dark:bg-gray-900 dark:text-gray-100 shadow px-6 py-3 flex justify-between items-center transition-colors duration-300">
    <!-- Toggle Button -->
    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-700 dark:text-gray-300 text-2xl focus:outline-none">
        ☰
    </button>

    <h1 class="text-lg md:text-xl font-semibold">Dashboard Wali</h1>

    <!-- Right Controls -->
    <div class="flex items-center space-x-4">
        <button
            @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); document.documentElement.classList.toggle('dark', darkMode)"
            class="text-xl focus:outline-none transition"
            :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <template x-if="darkMode">☀️</template>
            <template x-if="!darkMode">🌙</template>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="bg-red-500 text-white px-3 py-1 rounded-md text-sm font-medium hover:bg-red-600 transition">
                Logout
            </button>
        </form>
    </div>
</header>
