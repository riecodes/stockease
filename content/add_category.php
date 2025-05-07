<?php
// Generate CSRF token if not set
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Retrieve old input values
$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
?>

<head>
    <title>Add Category - CVSU</title>
    <link rel="stylesheet" href="css/add.css">
</head>

<body>

    <!-- Main Container -->
    <div class="container-fluid cvsu-container">
        <div class="d-flex justify-content-between align-items-center mb-4 cvsu-header">
            <h2><i class="fas fa-tags me-2"></i>Add New Category</h2>
            <a href="dashboard.php?section=manage_categories" class="btn cvsu-btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Categories
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <span class="required">* </span><?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="card cvsu-card">
            <div class="card-body">
                <form action="process/add_category.php" method="POST">
                    <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">
                    
                    <div class="mb-4">
                        <label for="name" class="form-label">Category Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?= isset($old['name']) ? htmlspecialchars($old['name']) : '' ?>">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= isset($old['description']) ? htmlspecialchars($old['description']) : '' ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn cvsu-btn-primary">
                            <i class="fas fa-save me-2"></i>Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Character Counter Script -->
    <script>
        const textarea = document.getElementById('description');
        const charCount = document.getElementById('charCount');

        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });
    </script>
</body>

</html>