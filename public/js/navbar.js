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