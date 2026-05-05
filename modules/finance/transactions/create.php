<?php
$page_title = 'New Transaction';
$css_path = '../../../src/css/dashboard.css';
$extra_css = '../../../src/css/finance.css';

require_once '../../../includes/header.php';
require_once '../../../includes/auth.php';

if (!hasRole(['Treasurer', 'Super Admin'])) {
    die('Access denied. Treasurer/Super Admin only.');
}

$message = '';
$type = $_POST['trans_type'] ?? 'Income';
$amount = $_POST['amount'] ?? '';
$category = $_POST['category'] ?? '';
$trans_date = $_POST['trans_date'] ?? date('Y-m-d');

if ($_POST['submit']) {
    $amount = floatval($amount);
    $trans_date = $_POST['trans_date'];
    
    if ($amount <= 0) {
        $message = 'Amount must be greater than 0';
    } elseif (empty($category)) {
        $message = 'Category is required';
    } elseif (empty($trans_date)) {
        $message = 'Transaction date is required';
    } else {
        $stmt = $conn->prepare("INSERT INTO tbl_transaction (trans_type, amount, category, trans_date, recorded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssi", $type, $amount, $category, $trans_date, $user_id);
        if ($stmt->execute()) {
            $message = 'Transaction created successfully!';
            $trans_id = $conn->insert_id;
            // Reset form
            $amount = '';
            $category = '';
            $trans_date = date('Y-m-d');
        } else {
            $message = 'Error creating transaction';
        }
        $stmt->close();
    }
}

// Get categories for dropdown
$categories = $conn->query("SELECT DISTINCT category FROM tbl_transaction ORDER BY category")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-xl py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="recent-transactions-card">
                <div class="card-header text-center py-4" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white;">
                    <h2 style="font-weight: 700; margin: 0;">New Transaction</h2>
                    <p class="mb-0 opacity-90">Record Income or Expense</p>
                </div>
                <div class="card-body p-5">
                    <?php if ($message): ?>
                        <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?> mb-4">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-600 mb-3">Transaction Type</label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="trans_type" id="income" value="Income" <?php echo $type == 'Income' ? 'checked' : ''; ?>>
                                    <label class="form-check-label finance-kpi-card income-card p-3 flex-fill text-center rounded-3 cursor-pointer" for="income">
                                        <div class="kpi-icon-finance mb-2">📈</div>
                                        <div class="fw-600">Income</div>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="trans_type" id="expense" value="Expense" <?php echo $type == 'Expense' ? 'checked' : ''; ?>>
                                    <label class="form-check-label finance-kpi-card expense-card p-3 flex-fill text-center rounded-3 cursor-pointer" for="expense">
                                        <div class="kpi-icon-finance mb-2">📉</div>
                                        <div class="fw-600">Expense</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="amount" class="form-label fw-600">Amount (RM)</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control form-control-lg" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" step="0.01" min="0.01" required placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="trans_date" class="form-label fw-600">Date</label>
                                <input type="date" class="form-control form-control-lg" id="trans_date" name="trans_date" value="<?php echo htmlspecialchars($trans_date); ?>" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="category" class="form-label fw-600 mb-3">Category</label>
                            <select class="form-select form-select-lg" id="category" name="category" required>
                                <option value="">Select or type category...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Popular categories will appear. Type new ones directly.</div>
                        </div>

                        <div class="mt-5 text-center">
                            <button type="submit" name="submit" class="btn btn-lg px-5 py-3" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border: none; border-radius: 50px; font-weight: 700; font-size: 1.1rem; box-shadow: 0 10px 30px rgba(13,110,253,0.4);">
                                <i class="fas fa-save me-2"></i>Save Transaction
                            </button>
                            <a href="../dashboard.php" class="btn btn-outline-secondary btn-lg px-5 py-3 ms-3">
                                ← Back to Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../../includes/footer.php'; ?>

