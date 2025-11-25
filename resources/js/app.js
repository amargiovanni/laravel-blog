import.meta.glob([
    '../images/**',
]);

import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'

window.Alpine = Alpine

Alpine.plugin(persist)

// Theme management component
Alpine.data('themeManager', () => ({
    theme: Alpine.$persist('system').as('blog_theme'),

    init() {
        this.applyTheme()

        // Listen for system preference changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.theme === 'system') {
                this.applyTheme()
            }
        })
    },

    applyTheme() {
        const isDark = this.theme === 'dark' ||
            (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)

        document.documentElement.classList.toggle('dark', isDark)
    },

    setTheme(newTheme) {
        this.theme = newTheme
        this.applyTheme()
    },

    get isDark() {
        return this.theme === 'dark' ||
            (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
    }
}))

Alpine.start()
