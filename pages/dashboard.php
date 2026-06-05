<?php
// ============================================================
// pages/dashboard.php
// ============================================================
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireLogin();

$user_id = (int)$_SESSION['user_id'];

// 1. Calculate Total Income
$income_stmt = $conn->prepare("SELECT SUM(amount) AS total_income FROM expenses WHERE user_id = ? AND type = 'income'");
$income_stmt->bind_param("i", $user_id);
$income_stmt->execute();
$income_result = $income_stmt->get_result()->fetch_assoc();
$total_income = $income_result['total_income'] ?? 0.00;

// 2. Calculate Total Expenses
$expense_stmt = $conn->prepare("SELECT SUM(amount) AS total_expense FROM expenses WHERE user_id = ? AND type = 'expense'");
$expense_stmt->bind_param("i", $user_id);
$expense_stmt->execute();
$expense_result = $expense_stmt->get_result()->fetch_assoc();
$total_expense = $expense_result['total_expense'] ?? 0.00;

// 3. Calculate Net Balance
$net_balance = $total_income - $total_expense;

// 4. Fetch 5 Recent Transactions for the overview table
$recent_stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY entry_date DESC, id DESC LIMIT 5");
$recent_stmt->bind_param("i", $user_id);
$recent_stmt->execute();
$recent_entries = $recent_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard – Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">💰 <span>ExpenseTracker</span></a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link active me-3" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a class="nav-link me-3" href="expenses.php"><i class="bi bi-list-ul"></i> Expenses</a>
            <a class="btn btn-outline-danger btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
   <h2 class="fw-bold mb-4">
    Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!
</h2>

    <!-- Summary Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Balance Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white text-dark">
                <div class="text-muted small fw-semibold uppercase">Net Balance</div>
                <h3 class="fw-bold mt-1 text-<?= $net_balance >= 0 ? 'success' : 'danger' ?>">
                    KSh <?= number_format($net_balance, 2) ?>
                </h3>
            </div>
        </div>
        <!-- Income Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-muted small fw-semibold">Total Income</div>
                <h3 class="fw-bold mt-1 text-success">+ KSh <?= number_format($total_income, 2) ?></h3>
            </div>
        </div>
        <!-- Expense Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-muted small fw-semibold">Total Expenses</div>
                <h3 class="fw-bold mt-1 text-danger">- KSh <?= number_format($total_expense, 2) ?></h3>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold card-title mb-0">Recent Activity</h5>
                <a href="expenses.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_entries->num_rows === 0): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No recent transactions.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $recent_entries->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($row['entry_date'])) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['category']) ?></span></td>
                                    <td class="text-muted small"><?= htmlspecialchars($row['description']) ?></td>
                                    <td class="fw-semibold text-<?= $row['type'] === 'income' ? 'success' : 'danger' ?>">
                                        <?= $row['type'] === 'income' ? '+' : '-' ?> KSh <?= number_format($row['amount'], 2) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>