// Global JavaScript for ParkSmart Landing Page

function __initGlobalOnce() {
    if (window.__parksmartGlobalsInitialized) return;
    window.__parksmartGlobalsInitialized = true;
    initScrollToTop();
    initSmoothScrolling();
    initActiveNavigation();
    initMobileMenu();
    initAnimations();
    initBottomNavActive();
    initGlobalLoader();
    initDriverPageLoader();
    initLogoutConfirm();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', __initGlobalOnce);
} else {
    // DOM already ready, run immediately
    __initGlobalOnce();
}

// Scroll to Top Functionality
function initScrollToTop() {
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    if (!scrollToTopBtn) return;
    
    // Show/hide scroll to top button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('visible');
        } else {
            scrollToTopBtn.classList.remove('visible');
        }
    });
    
    // Scroll to top when button is clicked
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Smooth Scrolling for Navigation Links
function initSmoothScrolling() {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            
            // Skip if targetId is just "#" (empty hash)
            if (targetId === '#') {
                return;
            }
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const headerElement = document.querySelector('.header');
                const headerHeight = headerElement ? headerElement.offsetHeight : 0;
                const targetPosition = targetElement.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Active Navigation Highlighting
function initActiveNavigation() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    function updateActiveNav() {
        const scrollPosition = window.scrollY + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }
    
    window.addEventListener('scroll', updateActiveNav);
    updateActiveNav(); // Initial call
}

// Mobile Menu Toggle (for future mobile navigation)
function initMobileMenu() {
    try {
        const toggle = document.querySelector('.mobile-toggle');
        const menu = document.querySelector('.nav-menu');
        if (!toggle || !menu) return;

        let closeBtn = null;

        const createCloseButton = () => {
            if (!closeBtn) {
                closeBtn = document.createElement('button');
                closeBtn.className = 'menu-close-btn';
                closeBtn.innerHTML = '<i class="fas fa-times"></i>';
                closeBtn.setAttribute('aria-label', 'Close menu');
                menu.insertBefore(closeBtn, menu.firstChild);
                
                // Close button click
                closeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closeMenu();
                });
            }
        };

        const removeCloseButton = () => {
            if (closeBtn && closeBtn.parentNode) {
                closeBtn.parentNode.removeChild(closeBtn);
                closeBtn = null;
            }
        };

        const closeMenu = () => {
            menu.classList.add('closing');
            setTimeout(() => {
                menu.classList.remove('show', 'closing');
                removeCloseButton();
            }, 300); // Match the slideOutRight animation duration
        };
        
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpening = !menu.classList.contains('show');
            
            if (isOpening) {
                createCloseButton();
                menu.classList.add('show');
            } else {
                closeMenu();
            }
        });
        
        // Close when clicking backdrop (outside sidebar)
        document.addEventListener('click', (e) => {
            if (menu.classList.contains('show') && 
                !menu.contains(e.target) && 
                !toggle.contains(e.target)) {
                closeMenu();
            }
        });
        
        // Close when clicking a link
        menu.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
        
        // Close on resize back to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) closeMenu();
        });
        
        // Close on ESC
        document.addEventListener('keydown', (e) => { 
            if (e.key === 'Escape' && menu.classList.contains('show')) closeMenu(); 
        });
    } catch (e) { /* noop */ }
}

// Animation on Scroll
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.feature-card, .user-card, .contact-item');
    
    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(element);
    });
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Performance optimization for scroll events
const debouncedScrollHandler = debounce(function() {
    // Handle scroll events here if needed
}, 10);

window.addEventListener('scroll', debouncedScrollHandler);

// Accessibility improvements
document.addEventListener('keydown', function(e) {
    // Handle keyboard navigation
    if (e.key === 'Tab') {
        document.body.classList.add('keyboard-navigation');
    }
});

document.addEventListener('mousedown', function() {
    document.body.classList.remove('keyboard-navigation');
});

// Form validation helper (for future use)
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Local storage helper for user preferences
function saveUserPreference(key, value) {
    try {
        localStorage.setItem(`parksmart_${key}`, JSON.stringify(value));
    } catch (error) {
        console.warn('Could not save user preference:', error);
    }
}

function getUserPreference(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(`parksmart_${key}`);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.warn('Could not retrieve user preference:', error);
        return defaultValue;
    }
}

// Bottom nav active indicator based on current page
function initBottomNavActive() {
    const items = document.querySelectorAll('.bottom-nav .nav-item');
    if (!items.length) return;

    const path = window.location.pathname;
    const currentFile = (path.substring(path.lastIndexOf('/') + 1) || '').toLowerCase();

    items.forEach((a) => {
        a.classList.remove('active');
        const href = (a.getAttribute('href') || '').toLowerCase();
        const file = href.split('#')[0].split('?')[0].split('/').pop();
        if (file && currentFile === file) {
            a.classList.add('active');
        }
    });
}

// Console welcome message
console.log(`
🚗 Welcome to ParkSmart! 🚗
Parking Management System
Version 1.0.0
Built with accessibility and user experience in mind.
`);

// Logout with confirmation modal
function initLogoutConfirm() {
    if (document.getElementById('logoutConfirmModal')) return;
    const modal = document.createElement('div');
    modal.id = 'logoutConfirmModal';
    modal.className = 'confirm-modal';
    modal.innerHTML = `
      <div class="confirm-backdrop"></div>
      <div class="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="logoutConfirmTitle">
        <div class="confirm-header">
          <h3 id="logoutConfirmTitle">Logout</h3>
        </div>
        <div class="confirm-body">Are you sure you want to log out?</div>
        <div class="confirm-actions">
          <button type="button" class="btn btn-outline" id="logoutCancelBtn">Cancel</button>
          <button type="button" class="btn btn-primary" id="logoutConfirmBtn">Logout</button>
        </div>
      </div>`;
    document.body.appendChild(modal);
    const close = () => modal.classList.remove('show');
    modal.querySelector('.confirm-backdrop').addEventListener('click', close);
    document.getElementById('logoutCancelBtn').addEventListener('click', close);
    document.getElementById('logoutConfirmBtn').addEventListener('click', async () => {
        close();
        await doLogout();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });
}

function openLogoutModal() {
    let modal = document.getElementById('logoutConfirmModal');
    if (!modal) initLogoutConfirm(), modal = document.getElementById('logoutConfirmModal');
    if (modal) modal.classList.add('show');
}

// Global logout usable from any section (driver/admin/root)
async function doLogout() {
    try {
        const path = window.location.pathname;
        const inDriver = /\/driver\//.test(path);
        const inAdmin = /\/admin\//.test(path);
        const apiUrl = inDriver || inAdmin ? '../backend/api/logout.php' : 'backend/api/logout.php';
        const loginUrl = inDriver || inAdmin ? '../login.php' : 'login.php';

        if (typeof window.showLoader === 'function') window.showLoader();
        const res = await fetch(apiUrl, { method: 'POST', credentials: 'include' });
        const data = await res.json().catch(() => ({ success: false }));
        if (data && data.success) {
            window.location.href = loginUrl;
        } else {
            alert('Logout failed. Please try again.');
        }
    } catch (e) {
        console.error('Logout error:', e);
        alert('Network error. Please try again.');
    } finally {
        if (typeof window.hideLoader === 'function') window.hideLoader();
    }
}

// Expose function used by inline onclick in headers
function logout() { openLogoutModal(); }
window.logout = logout;

// Generic confirmation modal usable anywhere
function ensurePsConfirmModal() {
    if (document.getElementById('psConfirmModal')) return;
    const modal = document.createElement('div');
    modal.id = 'psConfirmModal';
    modal.className = 'confirm-modal';
    modal.innerHTML = `
      <div class="confirm-backdrop"></div>
      <div class="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="psConfirmTitle">
        <div class="confirm-header"><h3 id="psConfirmTitle">Confirm</h3></div>
        <div class="confirm-body" id="psConfirmBody">Are you sure?</div>
        <div class="confirm-actions">
          <button type="button" class="btn btn-outline" id="psConfirmCancel">Cancel</button>
          <button type="button" class="btn btn-primary" id="psConfirmOk">OK</button>
        </div>
      </div>`;
    document.body.appendChild(modal);
}

// Returns Promise<boolean>
function psConfirm(message, opts = {}) {
    ensurePsConfirmModal();
    const modal = document.getElementById('psConfirmModal');
    const titleEl = modal.querySelector('#psConfirmTitle');
    const bodyEl = modal.querySelector('#psConfirmBody');
    const okBtn = modal.querySelector('#psConfirmOk');
    const cancelBtn = modal.querySelector('#psConfirmCancel');
    titleEl.textContent = opts.title || 'Confirm';
    bodyEl.textContent = message || 'Are you sure?';
    okBtn.textContent = opts.confirmText || 'OK';
    cancelBtn.textContent = opts.cancelText || 'Cancel';
    if (opts.confirmClass) okBtn.className = `btn ${opts.confirmClass}`; else okBtn.className = 'btn btn-primary';

    return new Promise((resolve) => {
        const close = (v) => { modal.classList.remove('show'); cleanup(); resolve(v); };
        const onBackdrop = () => close(false);
        const onCancel = () => close(false);
        const onOk = () => close(true);
        const onKey = (e) => { if (e.key === 'Escape') close(false); if (e.key === 'Enter') close(true); };
        modal.querySelector('.confirm-backdrop').addEventListener('click', onBackdrop);
        cancelBtn.addEventListener('click', onCancel);
        okBtn.addEventListener('click', onOk);
        document.addEventListener('keydown', onKey);
        function cleanup() {
            modal.querySelector('.confirm-backdrop').removeEventListener('click', onBackdrop);
            cancelBtn.removeEventListener('click', onCancel);
            okBtn.removeEventListener('click', onOk);
            document.removeEventListener('keydown', onKey);
        }
        modal.classList.add('show');
    });
}

// expose
window.psConfirm = psConfirm;

// Global toast notifications
(() => {
    if (window.showToast) return;
    let container = null;
    function ensureContainer() {
        if (container && document.body.contains(container)) return container;
        container = document.createElement('div');
        container.className = 'ps-toast-container';
        document.body.appendChild(container);
        // inject styles once
        if (!document.getElementById('ps-toast-styles')) {
            const style = document.createElement('style');
            style.id = 'ps-toast-styles';
            style.textContent = `
                .ps-toast-container{position:fixed;top:1rem;right:1rem;z-index:2000;display:flex;flex-direction:column;gap:.5rem}
                .ps-toast{background:#fff;border-left:4px solid #2563eb;box-shadow:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -2px rgba(0,0,0,.05);border-radius:.5rem;padding:.75rem 1rem;display:flex;align-items:center;gap:.5rem;transform:translateX(120%);opacity:.98;transition:transform .25s ease}
                .ps-toast.show{transform:translateX(0)}
                .ps-toast.success{border-left-color:#10b981}
                .ps-toast.error{border-left-color:#ef4444}
                .ps-toast i{color:#2563eb}
                .ps-toast.success i{color:#10b981}
                .ps-toast.error i{color:#ef4444}
                @media (max-width: 768px){.ps-toast-container{right:.75rem;left:.75rem}.ps-toast{transform:translateY(-20px);}}
            `;
            document.head.appendChild(style);
        }
        return container;
    }
    window.showToast = function(message, type = 'info', duration = 4000) {
        const root = ensureContainer();
        const toast = document.createElement('div');
        toast.className = `ps-toast ${type}`;
        const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
        toast.innerHTML = `<i class="fas fa-${icon}"></i><span>${message}</span>`;
        root.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 250);
        }, Math.max(1500, duration));
    };
})();

function initGlobalLoader() {
    if (document.querySelector('.loader-overlay')) return;
    const overlay = document.createElement('div');
    overlay.className = 'loader-overlay';
    overlay.innerHTML = '<div class="loader" aria-label="Loading" role="status"></div>';
    document.body.appendChild(overlay);

    window.showLoader = function() {
        overlay.classList.add('show');
        document.body.style.cursor = 'progress';
    };

    window.hideLoader = function() {
        overlay.classList.remove('show');
        document.body.style.cursor = '';
    };
}

// Minimal page-loading pulse for driver pages (0.6–0.75s)
function initDriverPageLoader() {
    try {
        const isDriver = /\/driver\//.test(window.location.pathname);
        if (!isDriver) return;
        const duration = 250; // 0.25s pulse
        if (typeof window.showLoader === 'function' && typeof window.hideLoader === 'function') {
            // Initial page load pulse
            window.showLoader();
            setTimeout(() => window.hideLoader(), duration);
        }

        // Optional: pulse on bottom-nav clicks for quick feedback
        const navItems = document.querySelectorAll('.bottom-nav .nav-item');
        navItems.forEach((a) => {
            a.addEventListener('click', () => {
                if (typeof window.showLoader === 'function') {
                    window.showLoader();
                    setTimeout(() => { if (typeof window.hideLoader === 'function') window.hideLoader(); }, 250);
                }
            });
        });
    } catch (e) { /* noop */ }
}
