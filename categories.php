<?php
include 'header.php';
$page_title = 'Categories';

// Check if user is logged in and if the user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    // Redirect non-admin users to the home page
    header('Location: home.php');
    exit();
}

// Create the 'categories' table if it doesn't exist
$create_categories_table_sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
)";
$mysqli->query($create_categories_table_sql);

// Process actions to add, edit, and delete categories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $stmt_add = $mysqli->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt_add->bind_param('s', $name);
        $stmt_add->execute();
        $stmt_add->close();
    } elseif (isset($_POST['edit_category'])) {
        $category_id = intval($_POST['category_id']);
        $name = trim($_POST['name']);
        $stmt_edit = $mysqli->prepare('UPDATE categories SET name = ? WHERE id = ?');
        $stmt_edit->bind_param('si', $name, $category_id);
        $stmt_edit->execute();
        $stmt_edit->close();
    } elseif (isset($_POST['delete_category'])) {
        $category_id = intval($_POST['category_id']);
        $stmt_check = $mysqli->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $stmt_check->bind_param('i', $category_id);
        $stmt_check->execute();
        $stmt_check->bind_result($product_count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($product_count > 0) {
            $error_message = 'Cannot delete a category with associated products.';
        } else {
            $stmt_delete = $mysqli->prepare('DELETE FROM categories WHERE id = ?');
            $stmt_delete->bind_param('i', $category_id);
            $stmt_delete->execute();
            $stmt_delete->close();
        }
    }
}

// Get all categories
$sql = 'SELECT * FROM categories ORDER BY name ASC';
$result = $mysqli->query($sql);
$categories = $result->fetch_all(MYSQLI_ASSOC);
$mysqli->close();
include 'template.php';
?>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert" data-translate="errorMessage"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="row">
    <h1 data-translate="categories">Categories</h1>

    <!-- Button to open the add category modal -->
    <button class="btn btn-primary col-1 mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal" data-translate="add">
        <i class="fa fa-plus-circle fa-6" aria-hidden="true"></i> Add
    </button>

    <!-- List of Categories -->
    <div class="table-responsive">
        <table id="Data_Table_5" class="table table-striped table-bordered zero-configuration dataTable table-hover">
            <thead>
                <tr>
                    <th data-translate="name">Name</th>
                    <th data-translate="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td>
                            <!-- Button to open the edit modal -->
                            <button class="btn btn-warning edit-category-btn" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-category='<?php echo json_encode($category); ?>' data-translate="edit">Edit</button>
                            <!-- Form to delete category -->
                            <form method="POST" action="" class="d-inline-block">
                                <input type="hidden" name="delete_category" value="1">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <button type="submit" class="btn btn-danger" data-confirm="confirmDelete" data-translate="delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="2" class="text-center" data-translate="noCategoriesFound">No categories found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal fade" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel" data-translate="add">Add</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="add_category" value="1">
                    <div class="mb-3">
                        <label for="name" class="form-label" data-translate="name">Name:</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
                    <button type="submit" class="btn btn-primary" data-translate="add">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="modal fade" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel" data-translate="edit">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="edit_category" value="1">
                    <input type="hidden" id="edit_category_id" name="category_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label" data-translate="name">Name:</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="close">Close</button>
                    <button type="submit" class="btn btn-primary" data-translate="saveChanges">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<script>
    // Populate the edit modal with the selected category data
    document.addEventListener('DOMContentLoaded', function () {
        const editButtons = document.querySelectorAll('.edit-category-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                const category = JSON.parse(this.getAttribute('data-category'));
                document.getElementById('edit_category_id').value = category.id;
                document.getElementById('edit_name').value = category.name;
            });
        });
    });
</script>


