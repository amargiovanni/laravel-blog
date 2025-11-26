import.meta.glob([
    '../images/**',
]);

// Apply theme immediately to prevent flash
(function() {
    const theme = localStorage.getItem('blog_theme') || 'system';
    const isDark = theme === 'dark' ||
        (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
})();

// Theme management component - uses Alpine from Livewire
document.addEventListener('alpine:init', () => {
    Alpine.data('themeManager', () => ({
        theme: localStorage.getItem('blog_theme') || 'system',

        init() {
            this.applyTheme();

            // Listen for system preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.theme === 'system') {
                    this.applyTheme();
                }
            });
        },

        applyTheme() {
            const isDark = this.theme === 'dark' ||
                (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

            document.documentElement.classList.toggle('dark', isDark);
        },

        setTheme(newTheme) {
            this.theme = newTheme;
            localStorage.setItem('blog_theme', newTheme);
            this.applyTheme();
        },

        get isDark() {
            return this.theme === 'dark' ||
                (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        }
    }));
});

// Re-apply theme after Livewire navigation
document.addEventListener('livewire:navigated', () => {
    const theme = localStorage.getItem('blog_theme') || 'system';
    const isDark = theme === 'dark' ||
        (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark);
});
