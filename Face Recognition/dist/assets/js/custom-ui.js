document.addEventListener("DOMContentLoaded", function () {
    var toggle = document.querySelector(".sidebar-mini-btn");
    if (!toggle) {
        return;
    }

    var body = document.body;
    var stored = localStorage.getItem("sidebarCollapsed");
    if (stored === "true") {
        body.classList.add("sidebar-collapsed");
    }

    toggle.addEventListener("click", function () {
        body.classList.toggle("sidebar-collapsed");
        localStorage.setItem("sidebarCollapsed", body.classList.contains("sidebar-collapsed"));
    });
});
