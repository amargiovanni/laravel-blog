import.meta.glob([
    '../images/**',
]);

// Theme management component - uses Alpine from Livewire (don't import Alpine separately)
document.addEventListener('livewire:init', () => {
    // Register the persist plugin if not already registered
    if (window.Alpine && !window.Alpine.store('blog_theme')) {
        window.Alpine.store('blog_theme', localStorage.getItem('blog_theme') || 'system');
    }
});

document.addEventListener('alpine:init', () => {
    Alpine.data('themeManager', () => ({
        theme: localStorage.getItem('blog_theme') || 'system',

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
            localStorage.setItem('blog_theme', newTheme)
            this.applyTheme()
        },

        get isDark() {
            return this.theme === 'dark' ||
                (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
        }
    }))
})
