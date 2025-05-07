<?php 
session_start();
require 'include/db.php'; // Adjust path as needed
// Check if the session variable is not set (user is not logged in)
if (!isset($_SESSION['admin_id'])) {
    // Redirect to the login page
    header("Location: index.php");
    exit();
}

// Determine which section to display based on a parameter or user action
$currentSection = isset($_GET['section']) ? $_GET['section'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>    
    <?php include('include/link.php'); ?>
    
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/buttons.css">
    <link rel="stylesheet" href="css/form.css">
    <link rel="stylesheet" href="css/general.css">
</head>
<body>
    <div class="sidebar">
        <?php include('sidebar.php'); ?>
    </div>
    
    <div id="content">
        <?php
        // Include the specific content based on the selected section
        
        switch ($currentSection) {
            case 'home':
                include('content/home.php');
                break;
            case 'add_category':
                include('content/add_category.php');
                break;
            case 'add_item':
                include('content/add_item.php');
                break;
            case 'manage_categories':
                include('content/manage_categories.php');
                break;
            case 'manage_items':
                include('content/manage_items.php');
                break;
            case 'add_borrowing':
                include('content/add_borrowing.php');
                break;
            case 'manage_borrowings':
                include('content/manage_borrowings.php');
                break;
            case 'reports':
                include('content/reports.php');
                break;
            case 'profile':
                include('content/profile.php');
                break;
            case 'edit_item':
                include('content/edit_item.php');
                break;
            case 'edit_category':
                include('content/edit_category.php');
                break;
            case 'edit_borrowing':
                include('content/edit_borrowing.php');
                break;
            case 'manage_returns':
                include('content/manage_returns.php');                    
                break;
            default:                                        
                break;
        }
        ?>        
    </div>
    
    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to log out?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="logout.php" class="btn btn-danger">Log Out</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Borrowing Modal -->
    <div class="modal fade" id="borrowingModal" tabindex="-1" aria-labelledby="borrowingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header cvsu-bg-green text-white">
                    <h5 class="modal-title" id="borrowingModalLabel">Borrowing Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="borrowingDetails">
                    <!-- Details loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- View Categories Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header cvsu-bg-green text-white">
                    <h5 class="modal-title">Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="categoryDetails">
                    <!-- Details loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- View Item Modal -->
    <div class="modal fade" id="itemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header cvsu-bg-green text-white">
                    <h5 class="modal-title">Item Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="itemDetails">
                    <!-- Details loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

</body>
</html>
