<head>
    <title>Add Category - CVSU</title>
    <link rel="stylesheet" href="css/add.css">
</head>

<body>

    <!-- Main Container -->
    <div class="container-fluid cvsu-container">
        <div class="cvsu-header">
            <h2><i class="fas fa-tag me-2"></i>Add New Category</h2>
            <p>Create a new inventory category to organize your items</p>
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

        <form action="process/add_category.php" method="POST">
            <div class="mb-4">
                <label for="categoryName" class="form-label">Category Name <span class="required">*<span></label>
                <input type="text" name="categoryName" id="categoryName"
                    class="form-control"
                    placeholder="e.g., Laboratory Equipment"
                    required>
            </div>

            <div class="mb-4">
                <label for="categoryDesc" class="form-label">Description</label>
                <textarea name="categoryDesc" id="categoryDesc"
                    class="form-control"
                    rows="4"
                    placeholder="Describe the category purpose or specifications"
                    maxlength="200"></textarea>
                <div class="textarea-counter">
                    <span id="charCount">0</span>/200
                </div>
            </div>

            <button type="submit" class="btn cvsu-btn-primary">
                Create Category <i class="fas fa-plus-circle"></i>
            </button>
        </form>
    </div>

    <!-- Character Counter Script -->
    <script>
        const textarea = document.getElementById('categoryDesc');
        const charCount = document.getElementById('charCount');

        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });
    </script>
</body>

</html>