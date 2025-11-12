import Alpine from 'alpinejs'

window.Alpine = Alpine

const ALLOWED_THEMES = ['emerald', 'emerald-dark']

window.appShell = () => ({
    sidebarOpen: window.innerWidth >= 1024,
    theme: (() => {
        const stored = localStorage.getItem('app-theme')
        if (ALLOWED_THEMES.includes(stored)) {
            return stored
        }
        localStorage.setItem('app-theme', 'emerald')
        return 'emerald'
    })(),
    init() {
        this.applyTheme()
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                this.sidebarOpen = true
            }
        })
    },
    applyTheme() {
        document.documentElement.dataset.theme = this.theme
        document.body.dataset.theme = this.theme
    },
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen
    },
    toggleTheme() {
        this.theme = this.theme === 'emerald' ? 'emerald-dark' : 'emerald'
        localStorage.setItem('app-theme', this.theme)
        this.applyTheme()
    },
})

Alpine.start()
