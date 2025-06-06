export default function initThemeSwitcher() {
    function getStoredTheme() {
        return localStorage.getItem('theme')
    }

    function setStoredTheme(theme) {
        localStorage.setItem('theme', theme)
    }

    function getPreferredTheme() {
        return getStoredTheme() || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
    }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme)
        updateActiveIcon(theme)
    }

    function toggleTheme() {
        const currentTheme = getPreferredTheme()
        const newTheme = currentTheme === 'light' ? 'dark' : 'light'
        setStoredTheme(newTheme)
        setTheme(newTheme)
    }

    function updateActiveIcon(theme) {
        const icon = document.querySelector('.theme-icon-active i')
        if (!icon) return

        icon.className = theme === 'dark' ? 'ci-moon' : 'ci-sun'

        const switcher = document.querySelector('.theme-switcher')
        if (switcher) {
            switcher.setAttribute('aria-label', `Toggle theme (${theme})`)
        }
    }

    // Initial setup
    setTheme(getPreferredTheme())

    // Listen to click on the toggle button
    const switcher = document.querySelector('.theme-switcher')
    if (switcher) {
        switcher.addEventListener('click', toggleTheme)
    }
}
