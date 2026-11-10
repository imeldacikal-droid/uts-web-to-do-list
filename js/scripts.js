// Buka/tutup sidebar
window.addEventListener('DOMContentLoaded', function () {
    var tombol = document.body.querySelector('#sidebarToggle');
    if (tombol) {
        tombol.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
        });
    }
});