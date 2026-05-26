-- =====================================================
-- KEBANA Digital Management System - Dummy Data
-- =====================================================
USE kebana_db;

-- 1. Dummy Users
-- Password for all dummy users is: password (hashed)
INSERT INTO tbl_user (username, password_hash, role, email, cawangan_id) VALUES
('bintulu_su', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 33, 'bintulu@kebana.local', 1),
('sibu_su', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 33, 'sibu@kebana.local', 2),
('pusat_su', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'pusat@kebana.local', NULL)
ON DUPLICATE KEY UPDATE role = VALUES(role);

-- 2. Dummy Members
INSERT INTO tbl_member (full_name, ic_number, village, phone_no, status) VALUES
('Ahmad Bin Abu', '850101-13-5123', 'Kampung Bintulu', '012-3456789', 'Active'),
('Siti Binti Kassim', '900202-13-6234', 'Kampung Sibu', '013-4567890', 'Active'),
('Muthu A/L Samy', '880303-13-7345', 'Kampung Miri', '014-5678901', 'Active'),
('Chong Wei Ming', '920404-13-8456', 'Kampung Kuching', '016-6789012', 'Inactive'),
('Fatimah Binti Awang', '810505-13-9567', 'Kampung Asyakirin', '017-7890123', 'Active')
ON DUPLICATE KEY UPDATE status = VALUES(status);

-- 3. Dummy Events
INSERT INTO tbl_event (event_title, event_date, event_end_date, venue, budget_est, status, approval_status, cawangan_id, event_level, created_by) VALUES
('Mesyuarat Agung Tahunan 2026', '2026-06-15', '2026-06-15', 'Dewan Suarah Bintulu', 5000.00, 'Approved', 'Approved', 1, 'MASTER', 1),
('Sukaneka Cawangan Sibu', '2026-07-20', '2026-07-21', 'Stadium Sibu', 3000.00, 'Submitted', 'Pending President', 2, 'MASTER', 2),
('Kempen Derma Darah Miri', '2026-08-10', '2026-08-10', 'Hospital Miri', 1500.00, 'Draft', 'Pending President', 3, 'MASTER', 1),
('Bengkel Kepimpinan Kuching', '2026-09-05', '2026-09-06', 'Hotel Kuching', 10000.00, 'Approved', 'Approved', 4, 'MASTER', 3)
ON DUPLICATE KEY UPDATE status = VALUES(status);

-- 4. Dummy Documents
INSERT INTO tbl_document (event_id, doc_name, file_path, status) VALUES
(1, 'Kertas Kerja MAT 2026', 'uploads/documents/kertas_kerja_mat2026.pdf', 'Approved'),
(1, 'Minit Mesyuarat MAT 2025', 'uploads/documents/minit_mat2025.pdf', 'Approved'),
(2, 'Senarai Peserta Sukaneka', 'uploads/documents/peserta_sukaneka.xlsx', 'Pending'),
(4, 'Tentatif Bengkel Kepimpinan', 'uploads/documents/tentatif_bengkel.pdf', 'Approved');

-- 5. Dummy Transactions
INSERT INTO tbl_transaction (trans_type, amount, category, trans_date, payment_mode, event_id, month_label, recorded_by) VALUES
('Income', 500.00, 'Yuran Keahlian', '2026-01-15', 'Transfer', NULL, '2026-01', 1),
('Income', 200.00, 'Sumbangan', '2026-02-10', 'Cash', NULL, '2026-02', 1),
('Expense', 300.00, 'Alat Tulis', '2026-03-05', 'Cash', NULL, '2026-03', 1),
('Expense', 1000.00, 'Sewa Dewan', '2026-06-14', 'Transfer', 1, '2026-06', 1),
('Expense', 500.00, 'Makanan & Minuman', '2026-06-15', 'Cash', 1, '2026-06', 1);

-- 6. Dummy Attendance
INSERT INTO tbl_attendance (event_id, member_id, status, notes, marked_by) VALUES
(1, 1, 'Present', 'Hadir awal', 1),
(1, 2, 'Present', '', 1),
(1, 3, 'Absent', 'Tiada maklum balas', 1),
(1, 4, 'Excused', 'Cuti sakit', 1),
(1, 5, 'Present', '', 1);
