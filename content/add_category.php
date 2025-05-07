<?php
// Generate CSRF token if not set
if (!isset($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Retrieve old input values
$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
?>

<link rel="stylesheet" href="content/css/add.css">

<body>
    <div class="container cvsu-container">
        <div class="cvsu-header">
            <h2><span class="material-icons">category</span>Add New Category</h2>
            <p>Create a new category for inventory items</p>
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

        <div class="cvsu-content-container">
            <div class="card cvsu-card">
                <div class="card-body">
                    <h5 class="card-title"><span class="material-icons">add_circle</span>Category Details</h5>
                    <form action="process/add_category_process.php" method="POST">
                        <input type="hidden" name="form_token" value="<?= $_SESSION['form_token'] ?>">

                        <div class="mb-4">
                            <label for="name" class="form-label">Category Name <span class="required">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                value="<?= isset($old['name']) ? htmlspecialchars($old['name']) : '' ?>">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description"
                                rows="3"><?= isset($old['description']) ? htmlspecialchars($old['description']) : '' ?></textarea>
                            <div class="textarea-counter">
                                <span id="charCount">0</span>/200
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="dashboard.php?section=manage_categories" class="btn btn-compact btn-secondary">
                                <span class="material-icons">arrow_back</span>Back
                            </a>
                            <button type="submit" class="btn btn-compact btn-primary">
                                <span class="material-icons">save</span>Save Category
                            </button>
                        </div>
                    </form>
                </div>
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