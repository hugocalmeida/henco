<?php
// Include necessary files and functions
include_once 'header.php';
include_once 'config/utils.php';

$page_title = 'Categories';

// Verify if the user is an admin
if (!is_admin()) {
    header('Location: dashboard.php'); // Redirect non-admins
    exit;
}

// Process form submissions for adding, editing, or deleting categories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add a new category
    if (isset($_POST['add_category'])) {
        $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
        if (empty($name)) {
            display_message('Name cannot be empty.', 'error');
        } else {
            try {
                $mysqli = db_connect(); // Get database connection
                $stmt = $mysqli->prepare('INSERT INTO categories (name) VALUES (?)');
                if ($stmt) {
                    $stmt->bind_param('s', $name);
                    if ($stmt->execute()) {
                        display_message('Category added successfully.', 'success');
                    } else {
                        display_message('Failed to add category: ' . $stmt->error, 'error');
                    }
                    $stmt->close();
                } else {
                    display_message('Failed to prepare statement.', 'error');
                }
            } catch (Exception $e) {
                display_message('Error: ' . $e->getMessage(), 'error');
            } finally {
                if (isset($mysqli)) $mysqli->close(); // Close connection
            }
        }
    }
    // Edit an existing category
    elseif (isset($_POST['edit_category'])) {
        $category_id = $_POST['category_id'] ?? 0;
        $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
        if (empty($name) || $category_id <= 0) {
            display_message('Invalid input for editing.', 'error');
        } else {
            try {
                $mysqli = db_connect();
                $stmt = $mysqli->prepare('UPDATE categories SET name = ? WHERE id = ?');
                if ($stmt) {
                    $stmt->bind_param('si', $name, $category_id);
                    if ($stmt->execute()) {
                        display_message('Category updated successfully.', 'success');
                    } else {
                        display_message('Failed to update category: ' . $stmt->error, 'error');
                    }
                    $stmt->close();
                } else {
                    display_message('Failed to prepare update statement.', 'error');
                }
            } catch (Exception $e) {
                display_message('Error: ' . $e->getMessage(), 'error');
            } finally {
                if (isset($mysqli)) $mysqli->close();
            }
        }
    }
    // Delete a category
    elseif (isset($_POST['delete_category'])) {
        $category_id = $_POST['category_id'] ?? 0;
        if ($category_id <= 0) {
            display_message('Invalid category ID for deletion.', 'error');
        } else {
            try {
                $mysqli = db_connect();
                // Check for associated products before deleting
                $stmt_check = $mysqli->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
                $stmt_check->bind_param('i', $category_id);
                $stmt_check->execute();
                $stmt_check->bind_result($product_count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($product_count > 0) {
                    display_message('Cannot delete: Category has associated products.', 'error');
                } else {
                    $stmt_delete = $mysqli->prepare('DELETE FROM categories WHERE id = ?');
                    if ($stmt_delete) {
                        $stmt_delete->bind_param('i', $category_id);
                        if ($stmt_delete->execute()) {
                            display_message('Category deleted successfully.', 'success');
                        } else {
                            display_message('Failed to delete category: ' . $stmt_delete->error, 'error');
                        }
                        $stmt_delete->close();
                    } else {
                        display_message('Failed to prepare delete statement.', 'error');
                    }
                }
            } catch (Exception $e) {
                display_message('Error: ' . $e->getMessage(), 'error');
            } finally {
                if (isset($mysqli)) $mysqli->close();
            }
        }
    }
}

// Get all categories
$categories = get_all_categories();

include 'template.php';
?>

<!-- Display message if available -->
<?php display_message(get_message(), get_message_type()); ?>

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
                            <button class="btn btn-warning edit-category-btn" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-category='<?php echo htmlspecialchars(json_encode($category), ENT_QUOTES, 'UTF-8'); ?>' data-translate="edit">Edit</button>
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


