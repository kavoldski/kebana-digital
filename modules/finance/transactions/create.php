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
$event_id_post = $_POST['event_id'] ?? '';
$payment_mode = $_POST['payment_mode'] ?? 'Cash';

if (isset($_POST['submit'])) {
    $amount = floatval($amount);
    $trans_date = $_POST['trans_date'];
    $month_label = strtoupper(date('M', strtotime($trans_date)));
    $event_id = !empty($_POST['event_id']) ? (int)$_POST['event_id'] : null;

    // Ensure NULL for empty event_id
    $event_id = $event_id === '' ? null : $event_id;

    
    if ($amount <= 0) {
        $message = 'Amount must be greater than 0';
    } elseif (empty($category)) {
        $message = 'Category is required';
    } elseif (empty($trans_date)) {
        $message = 'Transaction date is required';
    } else {
$stmt = $conn->prepare("INSERT INTO tbl_transaction (trans_type, amount, category, trans_date, recorded_by, event_id, payment_mode, month_label) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");


$stmt->bind_param(
            "sdsssi ss",
            $type,
            $amount,
            $category,
            $trans_date,
            $user_id,
            $event_id,
            $payment_mode,
            $month_label
        );




        
        if ($stmt->execute()) {
            $message = 'Transaction created successfully!';
            // Reset form
            $amount = '';
            $category = '';
            $trans_date = date('Y-m-d');
            $event_id_post = '';
        } else {
            $message = 'Error creating transaction: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Get categories for datalist
$categories = [];
$cat_result = $conn->query("SELECT DISTINCT category FROM tbl_transaction ORDER BY category");
if ($cat_result) {
    $categories = $cat_result->fetch_all(MYSQLI_ASSOC);
}

// Get active events for dropdown
$events = [];
$event_result = $conn->query("SELECT event_id, event_title FROM tbl_event ORDER BY event_date DESC");
if ($event_result) {
    $events = $event_result->fetch_all(MYSQLI_ASSOC);
}
?>

<div class="container-xl py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="recent-transactions-card shadow-sm border-0 rounded-4">
                <div class="card-header text-center py-4 rounded-top-4" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white;">
                    <h2 style="font-weight: 700; margin: 0;">New Transaction</h2>
                    <p class="mb-0 opacity-90">Record Income or Expense</p>
                </div>
                <div class="card-body p-5">
                    <?php if ($message): ?>
                        <div class="alert <?php echo strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?> mb-4 shadow-sm border-0">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="transaction-create-form">
                        <div class="mb-4 transaction-type-block">
                            <label class="form-label fw-bold mb-2">Transaction Type</label>
                            <div class="d-flex gap-3">
                                <div class="form-check flex-fill mb-0">
                                    <input class="form-check-input" type="radio" name="trans_type" id="income" value="Income" <?php echo $type == 'Income' ? 'checked' : ''; ?> >
                                    <label class="form-check-label fw-bold" for="income">
                                        <i class="fas fa-arrow-up me-2 text-success"></i>Income
                                    </label>
                                </div>
                                <div class="form-check flex-fill mb-0">
                                    <input class="form-check-input" type="radio" name="trans_type" id="expense" value="Expense" <?php echo $type == 'Expense' ? 'checked' : ''; ?> >
                                    <label class="form-check-label fw-bold" for="expense">
                                        <i class="fas fa-arrow-down me-2 text-danger"></i>Expense
                                    </label>
                                </div>
                            </div>


                            <script>
                                document.querySelectorAll('.form-check-input').forEach(input => {
                                    input.addEventListener('change', function() {
                                        document.querySelector('label[for="income"]').classList.remove('border', 'border-2', 'border-success', 'shadow-sm');
                                        document.querySelector('label[for="expense"]').classList.remove('border', 'border-2', 'border-danger', 'shadow-sm');
                                        
                                        if (this.value === 'Income') {
                                            document.querySelector('label[for="income"]').classList.add('border', 'border-2', 'border-success', 'shadow-sm');
                                        } else {
                                            document.querySelector('label[for="expense"]').classList.add('border', 'border-2', 'border-danger', 'shadow-sm');
                                        }
                                    });
                                });
                            </script>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="amount" class="form-label fw-bold">Amount (RM)</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light border-end-0 text-muted fw-bold">RM</span>
                                    <input type="number" class="form-control border-start-0 ps-0" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" step="0.01" min="0.01" required placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="trans_date" class="form-label fw-bold">Date</label>
                                <input type="date" class="form-control form-control-lg shadow-sm" id="trans_date" name="trans_date" value="<?php echo htmlspecialchars($trans_date); ?>" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="category" class="form-label fw-bold mb-2">Category</label>
                            <input class="form-control form-control-lg shadow-sm" list="categoryOptions" id="category" name="category" value="<?php echo htmlspecialchars($category); ?>" placeholder="Type or select a category..." required>
                            <datalist id="categoryOptions">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="mt-4">
                            <label for="event_id" class="form-label fw-bold mb-2">Link to Project / Event (Optional)</label>
                            <select class="form-select form-select-lg shadow-sm" id="event_id" name="event_id">
                                <option value="">-- General Association Fund --</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?php echo htmlspecialchars($event['event_id']); ?>" <?php echo $event_id_post == $event['event_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['event_title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i>Leave blank for general transactions not tied to a specific project/event.</div>
                        </div>

                        <div class="mt-4">
                            <label for="payment_mode" class="form-label fw-bold mb-2">Payment Mode</label>
                            <select class="form-select form-select-lg shadow-sm" id="payment_mode" name="payment_mode" required>
                                <option value="Cash" <?php echo $payment_mode === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                                <option value="Bank" <?php echo $payment_mode === 'Bank' ? 'selected' : ''; ?>>Bank</option>
                            </select>
                        </div>


                        <div class="transaction-footer-actions mt-5 text-center d-flex flex-column flex-sm-row justify-content-center gap-3" style="position: relative;">


                            <button type="submit" name="submit" class="btn btn-lg px-5 py-3 text-white" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border: none; border-radius: 50px; font-weight: 700; font-size: 1.1rem; box-shadow: 0 10px 30px rgba(13,110,253,0.3);">
                                <i class="fas fa-save me-2"></i>Save Transaction
                            </button>
                            <a href="../dashboard.php" class="btn btn-outline-secondary btn-lg px-4 py-3" style="border-radius: 50px; font-weight: 600;">
                                Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.2s ease-in-out; }
</style>

<?php require_once '../../../includes/footer.php'; ?>
