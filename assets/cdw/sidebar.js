document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');

    if (menuToggle && sidebar && mainContent) {
        menuToggle.addEventListener('click', function () {
            if (window.innerWidth <= 991) {
                sidebar.classList.toggle('show');
                sidebar.classList.toggle('open');

                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('show');
                }
            } else {
                sidebar.classList.toggle('closed');
                mainContent.classList.toggle('full');
                document.body.classList.toggle('sidebar-collapsed');
            }
        });
    }

    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('show');
        });
    }

    if (sidebar) {
        const savedScroll = localStorage.getItem('cdwSidebarScroll');

        if (savedScroll !== null) {
            sidebar.scrollTop = parseInt(savedScroll, 10);
        }

        sidebar.addEventListener('scroll', function () {
            localStorage.setItem('cdwSidebarScroll', sidebar.scrollTop);
        });
    }
});