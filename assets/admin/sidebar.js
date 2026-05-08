document.addEventListener('DOMContentLoaded', function () {

    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.getElementById('mainContent');

    /* =========================
       RESTORE SIDEBAR SCROLL
    ========================== */
    if (sidebar) {
        const savedScroll = localStorage.getItem('sidebarScroll');
        if (savedScroll !== null) {
            sidebar.scrollTop = parseInt(savedScroll, 10);
        }

        sidebar.addEventListener('scroll', function () {
            localStorage.setItem('sidebarScroll', sidebar.scrollTop);
        });
    }

    function handleDesktopToggle() {
        if (!sidebar || !mainContent) return;

        sidebar.classList.toggle('closed'); // match CSS
        mainContent.classList.toggle('full');
    }

    function handleMobileToggle() {
        if (!sidebar || !sidebarOverlay) return;

        sidebar.classList.toggle('open'); // match CSS
        sidebarOverlay.classList.toggle('show');
    }

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function () {
            if (window.innerWidth <= 991) {
                handleMobileToggle();
            } else {
                handleDesktopToggle();
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('show');
        });
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('show');
        }
    });

    /* =========================
       AUTO SCROLL TO ACTIVE LINK
    ========================== */
    const activeLink = document.querySelector('.sidebar-link.active, .sidebar-sublink.active');

    if (activeLink && sidebar) {
        setTimeout(() => {
            activeLink.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }, 100);
    }

});