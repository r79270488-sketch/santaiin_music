(function () {
    function applyTheme(theme) {
        document.documentElement.setAttribute("data-theme", theme);
        localStorage.setItem("musicTheme", theme);
    }

    function applyAccent(accent) {
        document.documentElement.setAttribute("data-accent", accent);
        localStorage.setItem("musicAccent", accent);
    }

    function syncButtons() {
        var theme = document.documentElement.getAttribute("data-theme") || "light";
        var accent = document.documentElement.getAttribute("data-accent") || "pink";

        document.querySelectorAll("[data-theme-choice]").forEach(function (button) {
            button.classList.toggle("is-active", button.getAttribute("data-theme-choice") === theme);
        });

        document.querySelectorAll(".accent-swatch[data-accent]").forEach(function (button) {
            button.classList.toggle("is-active", button.getAttribute("data-accent") === accent);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll("[data-theme-choice]").forEach(function (button) {
            button.addEventListener("click", function () {
                applyTheme(button.getAttribute("data-theme-choice"));
                syncButtons();
            });
        });

        document.querySelectorAll(".accent-swatch[data-accent]").forEach(function (button) {
            button.addEventListener("click", function () {
                applyAccent(button.getAttribute("data-accent"));
                syncButtons();
            });
        });

        syncButtons();
    });
})();
