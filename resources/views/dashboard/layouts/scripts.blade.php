<!-- 1. jQuery أولاً -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2. Toastr بعد jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- 3. ثم Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Feather Icons -->
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>

<!-- Custom JavaScript -->
<script>
    // Initialize Feather Icons
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        // Hide preloader after 1.5 seconds
        setTimeout(function() {
            document.querySelector('.loader-bg').style.display = 'none';
        }, 1500);

        // Mobile sidebar toggle
        const sidebarHide = document.getElementById('sidebar-hide');
        const mobileCollapse = document.getElementById('mobile-collapse');

        if (sidebarHide) {
            sidebarHide.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('.pc-sidebar').classList.toggle('active');
            });
        }

        if (mobileCollapse) {
            mobileCollapse.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('.pc-sidebar').classList.toggle('active');
            });
        }

        // Handle dropdown submenus
        const menuItems = document.querySelectorAll('.pc-item.pc-hasmenu');
        menuItems.forEach(item => {
            const link = item.querySelector('.pc-link');
            if (link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    item.classList.toggle('active');

                    // Close other open submenus
                    menuItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains(
                                'active')) {
                            otherItem.classList.remove('active');
                        }
                    });
                });
            }
        });


        // Initialize Bootstrap dropdowns properly
        const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
        dropdownElementList.forEach(function(dropdownToggleEl) {
            const dropdown = new bootstrap.Dropdown(dropdownToggleEl, {
                autoClose: true
            });
        });
    });

    // Mock functions for theme control
    function layout_change(theme) {
        console.log('Theme changed to:', theme);
        document.body.setAttribute('data-pc-theme', theme);
    }

    function change_box_container(value) {
        console.log('Box container:', value);
    }

    function layout_rtl_change(value) {
        console.log('RTL layout:', value);
        document.body.setAttribute('dir', value === 'true' ? 'rtl' : 'ltr');
        document.body.setAttribute('data-pc-direction', value === 'true' ? 'rtl' : 'ltr');
    }

    function preset_change(preset) {
        console.log('Preset changed to:', preset);
        document.body.setAttribute('data-pc-preset', preset);
    }

    function font_change(font) {
        console.log('Font changed to:', font);
        const style = document.createElement('style');
        style.innerHTML = `* { font-family: "${font}", sans-serif !important; }`;
        document.head.appendChild(style);
    }

    // Initialize with Cairo font
    window.addEventListener('load', function() {
        font_change("Cairo");
        layout_rtl_change('true');
    });
</script>


<!-- Toastr Notifications -->
<script>
    @if (session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if (session('error'))
        toastr.error("{{ session('error') }}");
    @endif
</script>
