<?php
require_once 'process/profile_process.php';
?>

<link rel="stylesheet" href="content/css/profile.css">

<body>
    <div class="container cvsu-container">
        <div class="cvsu-header">
            <h2><span class="material-icons">person</span>Profile</h2>
            <p>Manage your account details</p>
        </div>

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

        <div class="cvsu-content-container">
            <!-- Update Profile Form -->
            <div class="card cvsu-card">
                <div class="card-body">
                    <h5 class="card-title"><span class="material-icons">edit</span>Update Profile</h5>
                    <form action="dashboard.php?section=profile" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control"
                                value="<?= htmlspecialchars($admin['username']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="<?= htmlspecialchars($admin['email']) ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <span class="material-icons">save</span>Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password Form -->
            <div class="card cvsu-card">
                <div class="card-body">
                    <h5 class="card-title"><span class="material-icons">lock</span>Change Password</h5>
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
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <span class="material-icons">vpn_key</span>Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="card cvsu-card" style="border: 2px solid red;">
                <div class="card-body">
                    <h5 class="card-title"><span class="material-icons required">delete</span><span class="required">Delete
                            Account</span></h5>
                    <p class="text-danger">Warning: This action cannot be undone. All your data will be permanently deleted.</p>
                    <form action="dashboard.php?section=profile" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete your account?');">
                        <button type="submit" name="delete_account" class="btn btn-danger">
                            <span class="material-icons">delete_forever</span>Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>