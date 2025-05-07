<?php
// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$adminId = $_SESSION['admin_id'];
$error = $success = '';

// Fetch admin details
try {
    $stmt = $conn->prepare("SELECT username, email FROM admins WHERE admin_id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching admin details: " . $e->getMessage());
    $error = "Error loading profile. Please try again.";
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
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
        $currentPassword = trim($_POST['current_password']);
        $newPassword = trim($_POST['new_password']);
        $confirmPassword = trim($_POST['confirm_password']);

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = "All password fields are required.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } else {
            try {
                $stmt = $conn->prepare("SELECT password_hash FROM admins WHERE admin_id = ?");
                $stmt->bind_param("i", $adminId);
                $stmt->execute();
                $adminData = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (password_verify($currentPassword, $adminData['password_hash'])) {
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