const chartData = window.viewSubmittedReportChartData || null;

if (chartData) {
    const graphLabels = chartData.graphLabels || [];
    const graphWeights = chartData.graphWeights || [];
    const graphHeights = chartData.graphHeights || [];

    const weightCtx = document.getElementById('weightChart');
    if (weightCtx) {
        new Chart(weightCtx, {
            type: 'line',
            data: {
                labels: graphLabels,
                datasets: [{
                    label: 'Weight (kg)',
                    data: graphWeights,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.10)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.25,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Age in Months'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Weight (kg)'
                        },
                        beginAtZero: false
                    }
                }
            }
        });
    }

    const heightCtx = document.getElementById('heightChart');
    if (heightCtx) {
        new Chart(heightCtx, {
            type: 'line',
            data: {
                labels: graphLabels,
                datasets: [{
                    label: 'Height (cm)',
                    data: graphHeights,
                    borderColor: '#163b68',
                    backgroundColor: 'rgba(22, 59, 104, 0.10)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.25,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Age in Months'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Height (cm)'
                        },
                        beginAtZero: false
                    }
                }
            }
        });
    }
}

const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const mainContent = document.getElementById('mainContent');

function handleDesktopToggle() {
    sidebar.classList.toggle('hidden');
    mainContent.classList.toggle('full');
}

function handleMobileToggle() {
    sidebar.classList.toggle('show');
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
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    });
}

window.addEventListener('resize', function () {
    if (window.innerWidth > 991) {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
    }
});