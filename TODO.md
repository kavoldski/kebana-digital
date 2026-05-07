- [ ] Update `modules/finance/transactions/create.php`:
  - [x] Add payment_mode field (Cash/Bank)
  - [ ] Ensure event_id dropdown allows empty/null
  - [ ] Compute month_label from trans_date
  - [ ] Insert columns using new prepared statement including event_id, payment_mode, month_label


- [ ] Update `modules/finance/dashboard.php`:
  - [ ] Switch to PDO for queries
  - [ ] Recent Transactions LEFT JOIN tbl_event and show event_title/fallback
  - [ ] Show payment_mode column in table

- [x] Fix `modules/finance/transactions/list.php` DB error (prepare transactions list):
  - [x] Correct `bind_param` type string for pagination (`LIMIT`/`OFFSET`).
  - [x] Separate filter params/types from pagination params/types for totals/count queries.
  - [x] Replace invalid sort column `created_at` with `trans_date DESC, trans_id DESC`.

- [ ] Refine Transactions List UI:
  - [ ] Improve filter arrangement/layout for cleaner appearance.
  - [ ] Arrange balance cards horizontally with elegant styling.

- [ ] Run PHP lint checks (`php -l`) for modified files.

