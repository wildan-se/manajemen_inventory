import "./bootstrap";

import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

// ── Turbo Drive (SPA-like navigation) ──────────────────────────────────────
import * as Turbo from "@hotwired/turbo";

// Progress bar muncul setelah 100ms
Turbo.setProgressBarDelay(100);

// ── Cegah Turbo meng-cache halaman export (PDF/CSV) ──────────────────────
// Halaman PDF/CSV punya background putih. Jika Turbo cache snapshot mereka,
// saat user kembali ke halaman sebelumnya, snapshot putih akan di-restore.
// Solusi: tandai semua halaman export sebagai no-cache.
document.addEventListener("turbo:before-cache", () => {
    const path = window.location.pathname;
    // Jika URL mengandung export (pdf atau csv), jangan cache
    if (path.includes("export-") || path.includes("/pdf") || path.includes("/csv")) {
        // Set meta turbo-cache-control ke no-cache agar tidak disimpan
        let meta = document.querySelector('meta[name="turbo-cache-control"]');
        if (!meta) {
            meta = document.createElement("meta");
            meta.name = "turbo-cache-control";
            document.head.appendChild(meta);
        }
        meta.content = "no-cache";
    }
});


// ── Sync sidebar active nav link saat Turbo navigasi ──────────────────────
// Karena sidebar pakai data-turbo-permanent, DOM-nya dibekukan.
// Class nav-link-active dari server TIDAK diupdate otomatis.
// Kita perlu update class secara manual berdasarkan URL saat ini.
function updateSidebarActiveLink() {
    const currentPath = window.location.pathname;

    document
        .querySelectorAll("#sidebar-nav .nav-link[data-active-segment]")
        .forEach((link) => {
            const segment = link.dataset.activeSegment; // e.g. "/items"
            const exact = link.dataset.activeExact === "true";

            const isActive = exact
                ? currentPath === segment || currentPath === segment + "/"
                : currentPath.startsWith(segment + "/") ||
                  currentPath === segment;

            link.classList.toggle("nav-link-active", isActive);
            // Hapus border yang mungkin tertinggal
            if (!isActive) link.style.borderColor = "";
        });
}

// ── Sync sidebar collapse state ────────────────────────────────────────────
function syncSidebarState() {
    const sidebar = document.getElementById("sidebar");
    const iconOpen = document.getElementById("toggle-icon-open");
    const iconClosed = document.getElementById("toggle-icon-closed");
    if (!sidebar) return;

    const collapsed = localStorage.getItem("sidebar-collapsed") === "true";

    if (collapsed && !sidebar.classList.contains("sidebar-collapsed")) {
        sidebar.classList.add("sidebar-collapsed");
        sidebar.style.transition = "none";
        requestAnimationFrame(() => {
            sidebar.style.transition = "";
        });
    } else if (!collapsed && sidebar.classList.contains("sidebar-collapsed")) {
        sidebar.classList.remove("sidebar-collapsed");
    }

    if (iconOpen && iconClosed) {
        iconOpen.style.display = collapsed ? "none" : "block";
        iconClosed.style.display = collapsed ? "block" : "none";
    }
}

// Jalankan saat Turbo selesai load halaman baru
document.addEventListener("turbo:load", () => {
    updateSidebarActiveLink();
    syncSidebarState();
});

// Jalankan juga saat pertama kali halaman dimuat (non-Turbo load)
document.addEventListener("DOMContentLoaded", () => {
    updateSidebarActiveLink();
    syncSidebarState();
});
