import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// CSS is now loaded directly in base.html.twig via <link> tag
// import './styles/app.css';

// Theme switching functionality
function initTheme() {
    // Get system preference
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const systemTheme = prefersDark ? 'dark' : 'light';
    
    // Use saved theme or fall back to system preference
    const savedTheme = localStorage.getItem('theme') || systemTheme;
    
    // Apply theme
    document.documentElement.setAttribute('data-theme', savedTheme);
}

function bindThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    
    if (themeToggle && !themeToggle.hasAttribute('data-theme-listener')) {
        themeToggle.setAttribute('data-theme-listener', 'true');
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }
}

// Initialize theme once on load
initTheme();

// Listen for system theme changes (only once)
if (!window.themeChangeListenerAdded) {
    window.themeChangeListenerAdded = true;
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        // Only update if user hasn't manually set a preference
        if (!localStorage.getItem('theme')) {
            const newSystemTheme = e.matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newSystemTheme);
        }
    });
}

// Initialize UI components
function initializeComponents() {
    // Bind theme toggle
    bindThemeToggle();
    
    // Mobile navigation toggle
    const navbarToggle = document.getElementById('navbar-toggle');
    const navbarMenu = document.getElementById('navbar-menu');
    
    if (navbarToggle && navbarMenu && !navbarToggle.hasAttribute('data-nav-listener')) {
        navbarToggle.setAttribute('data-nav-listener', 'true');
        navbarToggle.addEventListener('click', function() {
            navbarMenu.classList.toggle('active');
            navbarToggle.classList.toggle('active');
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]:not([data-scroll-listener])').forEach(anchor => {
        anchor.setAttribute('data-scroll-listener', 'true');
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.offsetTop;
                const offsetPosition = elementPosition - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                if (navbarMenu && navbarMenu.classList.contains('active')) {
                    navbarMenu.classList.remove('active');
                    navbarToggle.classList.remove('active');
                }
            }
        });
    });
}

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initializeComponents);

// Initialize on Turbo navigation (for Symfony UX Turbo)
document.addEventListener('turbo:load', initializeComponents);

// Add navbar background on scroll (only once)
if (!window.scrollListenerAdded) {
    window.scrollListenerAdded = true;
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });
}

console.log('Humble website loaded! 🎉');
