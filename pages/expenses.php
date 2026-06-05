<?php
// ============================================================
// pages/expenses.php – View, Add, Edit, Delete (Fixed Version)
// ============================================================
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$user_id = (int)$_SESSION['user_id'];
$errors  = [];
$success = '';

// Helper function in case it isn't defined in your includes
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

// ============================================================
// DELETE an entry
// ============================================================
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $del_id, $user_id);
    $stmt->execute();
    header("Location: expenses.php?msg=deleted");
    exit;
}

// ============================================================
// ADD a new entry
// ============================================================
if (isset($_POST['add_expense'])) {
    $type        = trim($_POST['type']        ?? '');
    $amount      = trim($_POST['amount']      ?? '');
    $category    = trim($_POST['category']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $entry_date  = trim($_POST['entry_date']  ?? '');

    if (!in_array($type, ['income','expense']))  $errors[] = "Invalid type.";
    if (!is_numeric($amount) || $amount <= 0)    $errors[] = "Enter a valid amount greater than 0.";
    if (empty($category))                        $errors[] = "Please select a category.";
    if (empty($entry_date))                      $errors[] = "Please pick a date.";

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO expenses (user_id, type, amount, category, description, entry_date)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isdsss", $user_id, $type, $amount, $category, $description, $entry_date);
        if ($stmt->execute()) {
            $success = "Entry added successfully!";
        } else {
            $errors[] = "Database error. Please try again.";
        }
    }
}

// ============================================================
// EDIT / UPDATE an existing entry
// ============================================================
if (isset($_POST['edit_expense'])) {
    $edit_id     = (int)trim($_POST['edit_id']       ?? 0);
    $type        = trim($_POST['edit_type']           ?? '');
    $amount      = trim($_POST['edit_amount']         ?? '');
    $category    = trim($_POST['edit_category']       ?? '');
    $description = trim($_POST['edit_description']    ?? '');
    $entry_date  = trim($_POST['edit_entry_date']     ?? '');

    if (!in_array($type, ['income','expense']))  $errors[] = "Invalid type.";
    if (!is_numeric($amount) || $amount <= 0)    $errors[] = "Enter a valid amount.";
    if (empty($category))                        $errors[] = "Please select a category.";
    if (empty($entry_date))                      $errors[] = "Please pick a date.";

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "UPDATE expenses SET type=?, amount=?, category=?, description=?, entry_date=?
             WHERE id=? AND user_id=?"
        );
        $stmt->bind_param("sdsssii", $type, $amount, $category, $description, $entry_date, $edit_id, $user_id);
        if ($stmt->execute()) {
            $success = "Entry updated successfully!";
        } else {
            $errors[] = "Database error. Could not update.";
        }
    }
}

// ============================================================
// READ / FILTER entries
// ============================================================
$filter_category   = trim($_GET['category']   ?? '');
$filter_type       = trim($_GET['type']        ?? '');
$filter_date_from  = trim($_GET['date_from']   ?? '');
$filter_date_to    = trim($_GET['date_to']     ?? '');

$where   = "WHERE user_id = ?";
$params  = [$user_id];
$types   = 'i';

if ($filter_category !== '') {
    $where  .= " AND category = ?";
    $types  .= 's';
    $params[] = $filter_category;
}
if ($filter_type !== '') {
    $where  .= " AND type = ?";
    $types  .= 's';
    $params[] = $filter_type;
}
if ($filter_date_from !== '') {
    $where  .= " AND entry_date >= ?";
    $types  .= 's';
    $params[] = $filter_date_from;
}
if ($filter_date_to !== '') {
    $where  .= " AND entry_date <= ?";
    $types  .= 's';
    $params[] = $filter_date_to;
}

$stmt = $conn->prepare("SELECT * FROM expenses $where ORDER BY entry_date DESC, id DESC");
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$entries = $stmt->get_result();

$income_categories  = ['Salary','Freelance','Business','Investment','Other'];
$expense_categories = ['Food','Transport','Rent','Shopping','Entertainment','Utilities','Healthcare','Other'];
$all_categories     = array_unique(array_merge($income_categories, $expense_categories));

$open_add_modal = isset($_GET['action']) && $_GET['action'] === 'add';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expenses – Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">💰 <span>ExpenseTracker</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item"><a class="nav-link fw-semibold" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active fw-semibold" href="expenses.php"><i class="bi bi-list-ul"></i> Expenses</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-outline-danger btn-sm" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="fw-800 mb-0">All Entries</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg me-1"></i> Add Entry
        </button>
    </div>

    <!-- Alerts -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2">
            <?php foreach ($errors as $e): ?>
                <div><?= sanitize($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success || isset($_GET['msg'])): ?>
        <div class="alert alert-success py-2">
            <?= sanitize($success ?: ucfirst($_GET['msg'])) ?>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="filter-bar mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label form-label-sm fw-semibold mb-1">Search</label>
                <input type="text" id="search" class="form-control form-control-sm" placeholder="Search…">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label form-label-sm fw-semibold mb-1">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="income"  <?= $filter_type==='income' ? 'selected':'' ?>>Income</option>
                    <option value="expense" <?= $filter_type==='expense' ? 'selected':'' ?>>Expense</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label form-label-sm fw-semibold mb-1">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach ($all_categories as $cat): ?>
                        <option value="<?= sanitize($cat) ?>" <?= $filter_category===$cat ? 'selected':'' ?>><?= sanitize($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label form-label-sm fw-semibold mb-1">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= sanitize($filter_date_from) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label form-label-sm fw-semibold mb-1">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= sanitize($filter_date_to) ?>">
            </div>
            <div class="col-sm-6 col-md-1 d-flex gap-1">
                <button class="btn btn-primary btn-sm flex-fill">Filter</button>
                <a href="expenses.php" class="btn btn-outline-secondary btn-sm" title="Clear">✕</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="expenses-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th class="hide-sm">Description</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($entries->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No entries found. <a href="#" data-bs-toggle="modal" data-bs-target="#addModal">Add your first one!</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($row = $entries->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['entry_date'])) ?></td>
                            <td><span class="badge bg-<?= $row['type'] === 'income' ? 'success' : 'danger' ?>"><?= ucfirst($row['type']) ?></span></td>
                            <td><span class="cat-badge"><?= sanitize($row['category']) ?></span></td>
                            <td class="hide-sm text-muted small"><?= sanitize($row['description']) ?></td>
                            <td class="fw-semibold text-<?= $row['type']==='income' ? 'success' : 'danger' ?>">
                                <?= $row['type']==='income' ? '+' : '-' ?>KSh <?= number_format($row['amount'],2) ?>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm"
                                    onclick="openEditModal(
                                        <?= $row['id'] ?>,
                                        '<?= $row['type'] ?>',
                                        '<?= $row['amount'] ?>',
                                        '<?= sanitize($row['category']) ?>',
                                        '<?= addslashes(sanitize($row['description'])) ?>',
                                        '<?= $row['entry_date'] ?>'
                                    )">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm ms-1" onclick="confirmDelete(<?= $row['id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" novalidate>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" id="type" class="form-select" onchange="updateAddCategories()">
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category" id="category" class="form-select"></select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Amount (KSh)</label>
                            <input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description <small class="text-muted">(optional)</small></label>
                            <input type="text" name="description" class="form-control" placeholder="e.g. Groceries at Naivas">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" name="entry_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_expense" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" novalidate>
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="edit_type" id="edit_type" class="form-select" onchange="updateEditCategories()"></select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="edit_category" id="edit_category" class="form-select"></select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Amount (KSh)</label>
                            <input type="number" name="edit_amount" id="edit_amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="edit_description" id="edit_description" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" name="edit_entry_date" id="edit_entry_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_expense" class="btn btn-primary"><i class="bi bi-save me-1"></i> Update Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Share PHP Category Arrays with JavaScript
    const incomeCats = <?= json_encode($income_categories) ?>;
    const expenseCats = <?= json_encode($expense_categories) ?>;

    // Helper to change category options dynamically
    function populateDropdown(selectId, categories, selectedVal = '') {
        const select = document.getElementById(selectId);
        select.innerHTML = '';
        categories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            if(cat === selectedVal) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function updateAddCategories() {
        const type = document.getElementById('type').value;
        populateDropdown('category', type === 'income' ? incomeCats : expenseCats);
    }

    function updateEditCategories(selectedCat = '') {
        const type = document.getElementById('edit_type').value;
        populateDropdown('edit_category', type === 'income' ? incomeCats : expenseCats, selectedCat);
    }

    // Modal triggers & actions
    function openEditModal(id, type, amount, category, description, date) {
        document.getElementById('edit_id').value = id;
        
        // Setup types dropdown
        const typeSelect = document.getElementById('edit_type');
        typeSelect.innerHTML = `<option value="expense" ${type==='expense'?'selected':''}>Expense</option>
                                <option value="income" ${type==='income'?'selected':''}>Income</option>`;
        
        // Sync proper categories
        updateEditCategories(category);

        document.getElementById('edit_amount').value = amount;
        document.getElementById('edit_description').value = description;
        document.getElementById('edit_entry_date').value = date;

        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this entry?")) {
            window.location.href = `expenses.php?delete=${id}`;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateAddCategories(); // Init add modal selections

        <?php if ($open_add_modal || (!empty($errors) && isset($_POST['add_expense']))): ?>
            new bootstrap.Modal(document.getElementById('addModal')).show();
        <?php endif; ?>
    });

    // Front-end Filter Search feature (Client-side fast search)
    document.getElementById('search').addEventListener('input', function(e){
        const text = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#expenses-table tbody tr');
        rows.forEach(row => {
            if(row.cells.length > 1) {
                const match = row.innerText.toLowerCase().includes(text);
                row.style.display = match ? '' : 'none';
            }
        });
    });
</script>
</body>
</html>