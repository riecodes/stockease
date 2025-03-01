<?php

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$adminId = $_SESSION['admin_id'];
$error = '';
$success = '';

// Fetch admin details
try {
    $stmt = $conn->prepare("SELECT username, email FROM admins WHERE admin_id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching admin details: " . $e->getMessage());
    $error = "Error loading profile. Please try again.";
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update Profile Information
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        if (empty($username) || empty($email)) {
            $error = "Username and email are required.";
        } else {
            try {
                $stmt = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE admin_id = ?");
                $stmt->bind_param("ssi", $username, $email, $adminId);
                if ($stmt->execute()) {
                    $success = "Profile updated successfully!";
                    // Refresh admin data
                    $admin['username'] = $username;
                    $admin['email'] = $email;
                } else {
                    $error = "Error updating profile. Please try again.";
                }
                $stmt->close();
            } catch (Exception $e) {
                error_log("Error updating profile: " . $e->getMessage());
                $error = "Error updating profile. Please try again.";
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Change Password
        $currentPassword = trim($_POST['current_password']);
        $newPassword = trim($_POST['new_password']);
        $confirmPassword = trim($_POST['confirm_password']);

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "All password fields are required.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } else {
            try {
                // Verify current password
                $stmt = $conn->prepare("SELECT password_hash FROM admins WHERE admin_id = ?");
                $stmt->bind_param("i", $adminId);
                $stmt->execute();
                $result = $stmt->get_result();
                $adminData = $result->fetch_assoc();
                $stmt->close();

                if (password_verify($currentPassword, $adminData['password_hash'])) {
                    // Update password
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE admin_id = ?");
                    $stmt->bind_param("si", $newPasswordHash, $adminId);
                    if ($stmt->execute()) {
                        $success = "Password changed successfully!";
                    } else {
                        $error = "Error changing password. Please try again.";
                    }
                    $stmt->close();
                } else {
                    $error = "Current password is incorrect.";
                }
            } catch (Exception $e) {
                error_log("Error changing password: " . $e->getMessage());
                $error = "Error changing password. Please try again.";
            }
        }
    } elseif (isset($_POST['delete_account'])) {
        // Delete Account
        try {
            $stmt = $conn->prepare("DELETE FROM admins WHERE admin_id = ?");
            $stmt->bind_param("i", $adminId);
            if ($stmt->execute()) {
                session_destroy();
                header("Location: index.php");
                exit();
            } else {
                $error = "Error deleting account. Please try again.";
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error deleting account: " . $e->getMessage());
            $error = "Error deleting account. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CVSU</title>
    <link rel="stylesheet" href="css/profile.css"> <!-- Add profile-specific CSS -->
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid cvsu-container">
        <div class="cvsu-header">
            <h2><i class="fas fa-user me-2"></i>Profile</h2>
            <p>Manage your account details</p>
        </div>

        <!-- Display Success/Error Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Update Profile Form -->
        <div class="card cvsu-card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user-edit me-2"></i>Update Profile</h5>
                <form action="dashboard.php?section=profile" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn cvsu-btn-primary">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password Form -->
        <div class="card cvsu-card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-lock me-2"></i>Change Password</h5>
                <form action="dashboard.php?section=profile" method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="change_password" class="btn cvsu-btn-primary">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Delete Account Section -->
        <div class="card cvsu-card" style="border: 2px solid red;">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-trash-alt me-2 required "></i><span class="required">Delete Account</span></h5>
                <p class="text-danger">Warning: This action cannot be undone. All your data will be permanently deleted.</p>
                <form action="dashboard.php?section=profile" method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
                    <button type="submit" name="delete_account" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i>Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>