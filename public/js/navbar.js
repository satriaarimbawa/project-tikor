document.addEventListener("DOMContentLoaded", function() {
    const currentUrl = window.location.href;
    const navLinks = document.querySelectorAll('nav a');

    navLinks.forEach(link => {
        if (link.href === currentUrl || currentUrl.startsWith(link.href)) {
            
            link.classList.add('bg-[#253D6B]/50');
            
            link.classList.remove('text-white/60');
            link.classList.add('text-white');
            const textSpan = link.querySelector('span');
            if (textSpan) {
                textSpan.classList.remove('font-medium');
                textSpan.classList.add('font-bold');
            }

            const iconImg = link.querySelector('img');
            if (iconImg) {
                iconImg.classList.replace('opacity-60', 'opacity-100');
            }

            const subMenu = link.closest('#subMenuLaporan');
            if (subMenu) {
                subMenu.classList.remove('hidden'); 
                const chevron = document.getElementById('chevron-icon');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
                
                const parentBtn = subMenu.previousElementSibling;
                if (parentBtn) parentBtn.classList.add('text-white');
            }
        }
    });

    // Mobile Sidebar Toggle Logic
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobile-overlay');

    if (mobileBtn && sidebar && overlay) {
        const toggleSidebar = (show) => {
            if (show) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => {
                    overlay.classList.add('hidden');
                }, 300);
                document.body.style.overflow = 'auto';
            }
        };

        mobileBtn.addEventListener('click', () => {
            const isClosed = sidebar.classList.contains('-translate-x-full');
            toggleSidebar(isClosed);
        });

        overlay.addEventListener('click', () => toggleSidebar(false));

        // Close sidebar when navigating (especially on single page apps or when clicking same-page links)
        const sidebarLinks = sidebar.querySelectorAll('nav a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) { // only on mobile/tablet
                    toggleSidebar(false);
                }
            });
        });
    }
});

function toggleSubMenu() {
    const subMenu = document.getElementById('subMenuLaporan');
    const icon = document.getElementById('chevron-icon');
    
    // Toggle class hidden
    if (subMenu.classList.contains('hidden')) {
        subMenu.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        subMenu.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
}