# Multi-Branch (Cawangan) Update TODO

- [x] Update login authentication to fetch and store `cawangan_id` in session.
- [x] Update event listing logic to enforce cawangan filtering for non-pusat/finance users.
- [x] Update event creation logic to save `cawangan_id` for cawangan users and `NULL` for pusat creators.
- [x] Convert role checks from string-based to INT-based in target modules.
- [x] Fix sign up and register flow to store INT roles (avoid role=0 coercion).
- [ ] Enforce scoped visibility rules for Events and Finance modules by role.
- [ ] Run PHP lint checks on all newly modified scoped-visibility files.
- [ ] Mark completed items and summarize final code changes.
