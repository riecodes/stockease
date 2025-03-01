<!-- sidebar.php -->
<link rel="stylesheet" href="css/sidebar.css">

<nav id="sidebar" class="bg-dark text-white">
    <div class="sidebar-header p-3">
        <a href="dashboard.php?section=home">
            <img src="img/cvsu.png" alt="Logo">
        </a>
        <h3>StockEase</h3>
    </div>

    <ul class="list-unstyled components p-2">
        <!-- Home -->
        <li class="<?= ($currentSection == 'home') ? 'active' : '' ?>">
            <a href="dashboard.php?section=home" class="p-2 d-block text-white text-decoration-none">
                <i class="fas fa-home me-2"></i> Home
            </a>
        </li>

        <!-- Inventory Management -->
        <?php
        $inventorySections = ['add_category', 'add_item', 'manage_categories', 'manage_items'];
        $inventoryActive = in_array($currentSection, $inventorySections);
        ?>
        <li class="<?= $inventoryActive ? 'active' : '' ?>">
            <a href="#" class="dropdown-toggle p-2 d-block text-white text-decoration-none" aria-expanded="<?= $inventoryActive ? 'true' : 'false' ?>">
                <i class="fas fa-boxes me-2"></i> Inventory
            </a>
            <ul class="submenu list-unstyled <?= $inventoryActive ? 'show' : '' ?>">
                <li class="<?= ($currentSection == 'add_category') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=add_category" class="ps-4 d-block text-white text-decoration-none">
                        <i class="fas fa-plus me-2"></i> Add Category
                    </a>
                </li>
                <li class="<?= ($currentSection == 'manage_categories') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=manage_categories" class="ps-4 d-block text-white text-decoration-none">
                        <i class="fas fa-list me-2"></i> Manage Categories
                    </a>
                </li>
                <li class="<?= ($currentSection == 'add_item') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=add_item" class="ps-4 d-block text-white text-decoration-none">
                        <i class="fas fa-plus me-2"></i> Add Item
                    </a>
                </li>
                <li class="<?= ($currentSection == 'manage_items') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=manage_items" class="ps-4 d-block text-white text-decoration-none">
                        <i class="fas fa-list me-2"></i> Manage Items
                    </a>
                </li>
            </ul>
        </li>

        <!-- Borrowing Management -->
        <?php
        $borrowingSections = ['add_borrowing', 'manage_borrowings', 'manage_returns'];
        $borrowingActive = in_array($currentSection, $borrowingSections);
        ?>
        <li class="<?= $borrowingActive ? 'active' : '' ?>">
            <a href="#" class="dropdown-toggle p-2 d-block text-white text-decoration-none" aria-expanded="<?= $borrowingActive ? 'true' : 'false' ?>">
                <i class="fas fa-hand-holding me-2"></i> Borrowings
            </a>
            <ul class="submenu list-unstyled <?= $borrowingActive ? 'show' : '' ?>">
                <li class="<?= ($currentSection == 'add_borrowing') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=add_borrowing" class="ps-4 d-block text-white text-decoration-none">
                        <i class="fas fa-plus me-2"></i> Add Borrowing
                    </a>
                </li>
                <li class="<?= ($currentSection == 'manage_borrowings') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=manage_borrowings" class="ps-4 d-block text-white text-decoration-none">
                        <i class="fas fa-list me-2"></i> Manage Borrowings
                    </a>
                </li>
                <li class="<?= ($currentSection == 'manage_returns') ? 'active' : '' ?>">
                    <a href="dashboard.php?section=manage_returns" class="ps-4 d-block text-white text-decoration-none">
                        <i class="fas fa-hand-holding me-2"></i> Manage Returns
                    </a>
                </li>
            </ul>
        </li>

        <!-- Reports -->
        <li class="<?= ($currentSection == 'reports') ? 'active' : '' ?>">
            <a href="dashboard.php?section=reports" class="p-2 d-block text-white text-decoration-none">
                <i class="fas fa-chart-bar me-2"></i> Reports
            </a>
        </li>

        <!-- Profile -->
        <li class="<?= ($currentSection == 'profile') ? 'active' : '' ?>">
            <a href="dashboard.php?section=profile" class="p-2 d-block text-white text-decoration-none">
                <i class="fas fa-user me-2"></i> Profile
            </a>
        </li>

        <!-- Logout -->
        <li>
            <a href="#" class="text-white text-decoration-none p-2 d-block" 
            data-bs-toggle="modal" 
            data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</nav>

<!-- Sidebar Toggle Button (for mobile) -->
<button type="button" id="sidebarCollapse" class="btn btn-dark d-block d-md-none m-2">
    <i class="fas fa-bars"></i>
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