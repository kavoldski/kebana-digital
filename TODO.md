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

- [ ] Fix Proposal Logic (RBAC step-by-step):
  - [ ] Restrict event proposal creation to `Secretary` and `Super Admin`.
  - [ ] Fix `modules/events/create.php` form structure and step flow messaging.
  - [ ] Enforce upload policy: `pdf/jpg/jpeg/png`, max 5MB.
  - [ ] Improve validation error messages for proposal upload.

- [x] FR_11: Fix transaction creation reliability in `modules/finance/transactions/create.php`:
  - [x] Correct malformed `bind_param` type string.
  - [x] Ensure nullable `event_id` is handled safely for insert.
  - [x] Run `php -l` syntax check.

- [x] FR_03: Auto logout after inactivity:
  - [x] Add inactivity timeout enforcement in `includes/auth.php` (15 minutes).
  - [x] Destroy session and redirect to login with timeout message when idle limit exceeded.
  - [x] Refresh last activity timestamp on valid requests.
  - [x] Run `php -l` syntax check for `includes/auth.php`.

- [ ] FR_13: Event proposal workflow enforcement:
  - [ ] Use `tbl_event.status` as workflow state.
  - [ ] Set default status to `Draft` on event creation.
  - [ ] Add workflow transition helpers in `includes/events_helper.php`.
  - [ ] Add submit/approve/reject actions with role restrictions in `modules/events/list.php`.
  - [ ] Display status badge in events list based on workflow state.
  - [ ] Run `php -l` syntax checks for modified files.

- [ ] Run PHP lint checks (`php -l`) for modified files.

