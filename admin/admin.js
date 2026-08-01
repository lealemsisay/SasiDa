// admin/admin.js
(function() {
    'use strict';

    // ─── THEME TOGGLE ───────────────────────────
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
            // Update icon visibility
            const lightIcon = themeToggle.querySelector('.theme-icon.light');
            const darkIcon = themeToggle.querySelector('.theme-icon.dark');
            
            if (theme === 'light') {
                if (lightIcon) lightIcon.style.display = 'flex';
                if (darkIcon) darkIcon.style.display = 'none';
            } else {
                if (lightIcon) lightIcon.style.display = 'none';
                if (darkIcon) darkIcon.style.display = 'flex';
            }
        }

        // Apply stored theme or default to light
        const storedTheme = localStorage.getItem('theme') || 'light';
        applyTheme(storedTheme);

        themeToggle.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'light' ? 'dark' : 'light';
            applyTheme(next);
        });
    }

    // ─── Sidebar Toggle ──────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 992 && sidebar.classList.contains('open') &&
                !sidebar.contains(e.target) && e.target !== sidebarToggle) {
                sidebar.classList.remove('open');
            }
        });
    }

    // ─── Confirm Dialog Helper ──────────────────
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

    // ─── Toast Notification ──────────────────────
    window.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = 'admin-toast';
        toast.style.cssText = `
            position: fixed; bottom: 20px; right: 20px;
            padding: 12px 24px; border-radius: 8px;
            background: ${type === 'success' ? '#2ecc71' : '#e74c3c'};
            color: #fff; font-weight: 500;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            z-index: 9999;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // ─── Auto-close modals on backdrop click ────
    document.querySelectorAll('.admin-modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });

})();