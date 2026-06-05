// ============================================================
// js/app.js – Client-side logic for the Expense Tracker
// No frameworks – plain vanilla JavaScript only!
// ============================================================

// ---- Utility: format a number as currency -----------------
function formatCurrency(amount) {
    return 'KSh ' + parseFloat(amount).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// ---- Live amount preview in the Add/Edit modal ------------
// Shows "KSh X" next to the amount field as the user types
function setupAmountPreview() {
    var amountInput = document.getElementById('amount');
    var preview     = document.getElementById('amount-preview');
    if (!amountInput || !preview) return;

    amountInput.addEventListener('input', function () {
        var val = parseFloat(this.value);
        preview.textContent = isNaN(val) ? '' : formatCurrency(val);
    });
}

// ---- Type toggle: change category options when Income/Expense changes
function setupTypeToggle() {
    var typeSelect = document.getElementById('type');
    if (!typeSelect) return;

    var incomeCategories  = ['Salary', 'Freelance', 'Business', 'Investment', 'Other'];
    var expenseCategories = ['Food', 'Transport', 'Rent', 'Shopping', 'Entertainment', 'Utilities', 'Healthcare', 'Other'];

    typeSelect.addEventListener('change', function () {
        var catSelect = document.getElementById('category');
        if (!catSelect) return;

        var list = this.value === 'income' ? incomeCategories : expenseCategories;
        var current = catSelect.value; // try to keep current selection if still valid

        catSelect.innerHTML = '';
        list.forEach(function (cat) {
            var opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            if (cat === current) opt.selected = true;
            catSelect.appendChild(opt);
        });
    });
}

// ---- Client-side validation for the expense form ----------
function validateExpenseForm() {
    var amount      = document.getElementById('amount');
    var category    = document.getElementById('category');
    var entryDate   = document.getElementById('entry_date');
    var isValid     = true;

    // Reset previous error styles
    [amount, category, entryDate].forEach(function (el) {
        if (el) el.classList.remove('is-invalid');
    });

    if (!amount || isNaN(parseFloat(amount.value)) || parseFloat(amount.value) <= 0) {
        if (amount) amount.classList.add('is-invalid');
        isValid = false;
    }
    if (!category || category.value === '') {
        if (category) category.classList.add('is-invalid');
        isValid = false;
    }
    if (!entryDate || entryDate.value === '') {
        if (entryDate) entryDate.classList.add('is-invalid');
        isValid = false;
    }
    return isValid;
}

// ---- Validate the auth forms (login / register) -----------
function validateAuthForm(formId) {
    var form = document.getElementById(formId);
    if (!form) return true; // nothing to validate

    var inputs = form.querySelectorAll('[required]');
    var isValid = true;

    inputs.forEach(function (input) {
        input.classList.remove('is-invalid');
        if (input.value.trim() === '') {
            input.classList.add('is-invalid');
            isValid = false;
        }
    });

    // Extra: check passwords match on the register form
    var pw  = form.querySelector('#password');
    var pw2 = form.querySelector('#confirm_password');
    if (pw && pw2 && pw.value !== pw2.value) {
        pw2.classList.add('is-invalid');
        isValid = false;
    }

    return isValid;
}

// ---- Open the Edit modal and pre-fill it ------------------
// Called by the "Edit" button in each table row
function openEditModal(id, type, amount, category, description, date) {
    document.getElementById('edit_id').value          = id;
    document.getElementById('edit_type').value        = type;
    document.getElementById('edit_amount').value      = amount;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_entry_date').value  = date;

    // Rebuild the category list to match the type, then select the right one
    var catSelect = document.getElementById('edit_category');
    var incomeCategories  = ['Salary', 'Freelance', 'Business', 'Investment', 'Other'];
    var expenseCategories = ['Food', 'Transport', 'Rent', 'Shopping', 'Entertainment', 'Utilities', 'Healthcare', 'Other'];
    var list = type === 'income' ? incomeCategories : expenseCategories;

    catSelect.innerHTML = '';
    list.forEach(function (cat) {
        var opt = document.createElement('option');
        opt.value = cat;
        opt.textContent = cat;
        if (cat === category) opt.selected = true;
        catSelect.appendChild(opt);
    });

    // Show the Bootstrap modal
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}

// ---- Confirm before deleting an entry ---------------------
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this entry?')) {
        window.location.href = 'pages/expenses.php?delete=' + id;
    }
}

// ---- Search / filter: live row filtering on the expenses table
function setupSearch() {
    var searchInput = document.getElementById('search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        var query = this.value.toLowerCase();
        var rows  = document.querySelectorAll('#expenses-table tbody tr');

        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
}

// ---- Run all setup functions when the page loads ----------
document.addEventListener('DOMContentLoaded', function () {
    setupAmountPreview();
    setupTypeToggle();
    setupSearch();

    // Attach validation to the add-expense form
    var addForm = document.getElementById('add-expense-form');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            if (!validateExpenseForm()) e.preventDefault();
        });
    }

    // Attach validation to auth forms
    ['login-form', 'register-form'].forEach(function (id) {
        var f = document.getElementById(id);
        if (f) {
            f.addEventListener('submit', function (e) {
                if (!validateAuthForm(id)) e.preventDefault();
            });
        }
    });
});
