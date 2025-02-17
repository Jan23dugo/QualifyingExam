// DOM Content Loaded Event for initializing charts
document.addEventListener("DOMContentLoaded", function() {
    var charts = document.querySelectorAll("[data-bss-chart]");

    for (var chartElement of charts) {
        chartElement.chart = new Chart(chartElement, JSON.parse(chartElement.dataset.bssChart));
    }
}, false);

// Sidebar Toggle and Collapse Handling
(function() {
    "use strict";

    var sidebar = document.querySelector(".sidebar");
    var sidebarToggleButtons = document.querySelectorAll("#sidebarToggle, #sidebarToggleTop");

    // Check if sidebar exists
    if (sidebar) {
        // Collapse elements within sidebar
        var collapses = [].slice.call(document.querySelectorAll(".sidebar .collapse"))
                           .map(function(element) {
                               return new bootstrap.Collapse(element, { toggle: false });
                           });

        // Toggle sidebar on button click
        for (var toggleButton of sidebarToggleButtons) {
            toggleButton.addEventListener("click", function() {
                document.body.classList.toggle("sidebar-toggled");
                sidebar.classList.toggle("toggled");

                // Hide all collapses if sidebar is toggled
                if (sidebar.classList.contains("toggled")) {
                    for (var collapse of collapses) {
                        collapse.hide();
                    }
                }
            });
        }

        // Handle window resize
        window.addEventListener("resize", function() {
            if (Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0) < 768) {
                for (var collapse of collapses) {
                    collapse.hide();
                }
            }
        });
    }

    // Prevent scrolling on sidebar when the window width is greater than 768px
    var fixedNavSidebar = document.querySelector("body.fixed-nav .sidebar");
    
    if (fixedNavSidebar) {
        fixedNavSidebar.addEventListener("mousewheel DOMMouseScroll wheel", function(event) {
            if (Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0) > 768) {
                var originalEvent = event.originalEvent;
                var delta = originalEvent.wheelDelta || -originalEvent.detail;
                this.scrollTop += 30 * (delta < 0 ? 1 : -1);
                event.preventDefault();
            }
        });
    }

    // Scroll-to-top button visibility
    var scrollToTopButton = document.querySelector(".scroll-to-top");

    if (scrollToTopButton) {
        window.addEventListener("scroll", function() {
            var yOffset = window.pageYOffset;
            scrollToTopButton.style.display = yOffset > 100 ? "block" : "none";
        });
    }

})();
