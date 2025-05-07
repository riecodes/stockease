<!-- sidebar.php -->
<link rel="stylesheet" href="css/sidebar.css">

<nav id="sidebar" class="bg-dark text-white">
    <div class="sidebar-header p-4 d-flex align-items-center gap-3">
        <a href="dashboard.php?section=home" class="d-flex align-items-center gap-3 text-decoration-none">
            <img src="img/cvsu.png" alt="Logo" class="sidebar-logo">
            <h3 class="mb-0 text-white">StockEase</h3>
        </a>
    </div>

    <div class="sidebar-divider"></div>

    <ul class="list-unstyled components p-3">
        <!-- Home -->
        <li class="nav-item mb-2 <?= ($currentSection == 'home') ? 'active' : '' ?>">
            <a href="dashboard.php?section=home" class="nav-link p-3 d-flex align-items-center text-white text-decoration-none rounded">
                <span class="material-icons me-3 text-white">home</span> <span class="text-white">Home</span>
            </a>
        </li>

        <!-- Inventory Management -->
        <?php
        $inventorySections = ['add_category', 'add_item', 'manage_categories', 'manage_items'];
        $inventoryActive = in_array($currentSection, $inventorySections);
        ?>
        <li class="nav-item mb-2 <?= $inventoryActive ? 'active' : '' ?>">
            <a href="#" class="nav-link p-3 d-flex align-items-center justify-content-between text-white text-decoration-none rounded dropdown-toggle" aria-expanded="<?= $inventoryActive ? 'true' : 'false' ?>">
                <div class="d-flex align-items-center">
                    <span class="material-icons me-3 text-white">inventory_2</span> <span class="text-white">Inventory</span>
                </div>
            </a>
            <ul class="submenu list-unstyled <?= $inventoryActive ? 'show' : '' ?>">
                <li class="nav-item mb-1 <?= ($currentSection == 'add_category') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=add_category" class="nav-link ps-4 py-2 d-flex align-items-center text-white text-decoration-none rounded">
                        <span class="material-icons me-3 text-white">add</span> <span class="text-white">Add Category</span>
                    </a>
                </li>
                <li class="nav-item mb-1 <?= ($currentSection == 'manage_categories') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=manage_categories" class="nav-link ps-4 py-2 d-flex align-items-center text-white text-decoration-none rounded">
                        <span class="material-icons me-3 text-white">category</span> <span class="text-white">Manage Categories</span>
                    </a>
                </li>
                <li class="nav-item mb-1 <?= ($currentSection == 'add_item') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=add_item" class="nav-link ps-4 py-2 d-flex align-items-center text-white text-decoration-none rounded">
                        <span class="material-icons me-3 text-white">add</span> <span class="text-white">Add Item</span>
                    </a>
                </li>
                <li class="nav-item mb-1 <?= ($currentSection == 'manage_items') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=manage_items" class="nav-link ps-4 py-2 d-flex align-items-center text-white text-decoration-none rounded">
                        <span class="material-icons me-3 text-white">list</span> <span class="text-white">Manage Items</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Borrowing Management -->
        <?php
        $borrowingSections = ['add_borrowing', 'manage_borrowings', 'manage_returns'];
        $borrowingActive = in_array($currentSection, $borrowingSections);
        ?>
        <li class="nav-item mb-2 <?= $borrowingActive ? 'active' : '' ?>">
            <a href="#" class="nav-link p-3 d-flex align-items-center justify-content-between text-white text-decoration-none rounded dropdown-toggle" aria-expanded="<?= $borrowingActive ? 'true' : 'false' ?>">
                <div class="d-flex align-items-center">
                    <span class="material-icons me-3 text-white">handshake</span> <span class="text-white">Borrowings</span>
                </div>
            </a>
            <ul class="submenu list-unstyled <?= $borrowingActive ? 'show' : '' ?>">
                <li class="nav-item mb-1 <?= ($currentSection == 'add_borrowing') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=add_borrowing" class="nav-link ps-4 py-2 d-flex align-items-center text-white text-decoration-none rounded">
                        <span class="material-icons me-3 text-white">add</span> <span class="text-white">Add Borrowing</span>
                    </a>
                </li>
                <li class="nav-item mb-1 <?= ($currentSection == 'manage_borrowings') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=manage_borrowings" class="nav-link ps-4 py-2 d-flex align-items-center text-white text-decoration-none rounded">
                        <span class="material-icons me-3 text-white">list</span> <span class="text-white">Manage Borrowings</span>
                    </a>
                </li>
                <li class="nav-item mb-1 <?= ($currentSection == 'manage_returns') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=manage_returns" class="nav-link ps-4 py-2 d-flex align-items-center text-white text-decoration-none rounded">
                        <span class="material-icons me-3 text-white">assignment_return</span> <span class="text-white">Manage Returns</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Profile -->
        <li class="nav-item mb-2 <?= ($currentSection == 'profile') ? 'active' : '' ?>">
            <a href="dashboard.php?section=profile" class="nav-link p-3 d-flex align-items-center text-white text-decoration-none rounded">
                <span class="material-icons me-3 text-white">person</span> <span class="text-white">Profile</span>
            </a>
        </li>
    </ul>

    <!-- Logout at bottom -->
    <div class="sidebar-footer">
        <ul class="list-unstyled components p-3">
            <li class="nav-item mt-auto">
                <a href="#" class="nav-link p-3 d-flex align-items-center text-white text-decoration-none rounded" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <span class="material-icons me-3 text-white">logout</span> <span class="text-white">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Sidebar Toggle Button (for mobile) -->
<button type="button" id="sidebarCollapse" class="btn btn-dark d-block d-md-none m-3 rounded-circle p-3">
    <span class="material-icons text-white">menu</span>
</button>

<!-- Sidebar Script for Toggle and Real-Time Caret Animation -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dropdownToggles = document.querySelectorAll(".dropdown-toggle");
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener("click", function(e) {
                e.preventDefault();
                const submenu = this.nextElementSibling;
                if (submenu.classList.contains("show")) {
                    submenu.style.maxHeight = null;
                    submenu.style.opacity = "0";
                } else {
                    submenu.style.maxHeight = submenu.scrollHeight + "px";
                    submenu.style.opacity = "1";
                }
                submenu.classList.toggle("show");
                this.setAttribute("aria-expanded", submenu.classList.contains("show"));
            });
        });

        // Mobile toggle for sidebar
        const sidebar = document.getElementById("sidebar");
        const sidebarCollapse = document.getElementById("sidebarCollapse");
        sidebarCollapse.addEventListener("click", function() {
            sidebar.classList.toggle("active");
        });

        // Logout confirmation handler
        const logoutModal = document.getElementById('logoutModal');
        if(logoutModal) {
            logoutModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
            });
        }
    });
</script>