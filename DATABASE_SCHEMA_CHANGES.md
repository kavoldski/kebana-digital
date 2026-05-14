# KEBANA Digital Management System - Database Schema Update

## Overview
The database schema has been completely rewritten to align with the FYP report requirements. The system now uses simplified, focused tables optimized for core functionality.

## New Database Schema

### 1. **tbl_user** - User Account Management
```sql
- user_id (INT, PK)
- username (VARCHAR 50, UNIQUE)
- password_hash (VARCHAR 255)
- role (ENUM: 'Super Admin', 'Secretary', 'Treasurer')
- email (VARCHAR 100, UNIQUE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```
**Purpose:** Manages system user accounts with role-based access control

---

### 2. **tbl_member** - Member Information
```sql
- member_id (INT, PK)
- full_name (VARCHAR 150)
- ic_number (VARCHAR 20, UNIQUE)
- village (VARCHAR 100)
- phone_no (VARCHAR 20)
- status (VARCHAR 50, default: 'Active')
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```
**Purpose:** Stores member/citizen information

---

### 3. **tbl_event** - Event Management
```sql
- event_id (INT, PK)
- event_title (VARCHAR 150)
- event_date (DATE)
- venue (VARCHAR 150)
- budget_est (DECIMAL 10,2)
- created_by (INT, FK to tbl_user)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```
**Purpose:** Records organization events and activities

---

### 4. **tbl_document** - Event Documents
```sql
- doc_id (INT, PK)
- event_id (INT, FK to tbl_event)
- doc_name (VARCHAR 150)
- file_path (VARCHAR 255)
- uploaded_at (TIMESTAMP)
```
**Purpose:** Stores documents related to events

---

### 5. **tbl_transaction** - Financial Transactions
```sql
- trans_id (INT, PK)
- trans_type (ENUM: 'Income', 'Expense')
- amount (DECIMAL 10,2)
- category (VARCHAR 100)
- trans_date (DATE)
- recorded_by (INT, FK to tbl_user)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```
**Purpose:** Tracks financial transactions (income and expenses)

---

## Code Updates Completed

### ✅ **Database Files**
- [database.sql](database.sql) - Completely rewritten with new 5-table schema

### ✅ **Authentication System**
- [includes/dbconnect.php](includes/dbconnect.php) - Updated schema reference
- [modules/auth/authenticate.php](modules/auth/authenticate.php) - Updated to use `tbl_user` with username/password_hash
- [modules/auth/register.php](modules/auth/register.php) - Updated to register users in `tbl_user`
- [includes/auth.php](includes/auth.php) - Updated session variables (username instead of full_name)

### ✅ **Member Management**
- [includes/members_helper.php](includes/members_helper.php) - Completely rewritten for new `tbl_member` schema
- [modules/members/add.php](modules/members/add.php) - Updated to use new member fields
- [modules/members/edit.php](modules/members/edit.php) - Updated form fields for new schema
- [modules/members/list.php](modules/members/list.php) - Compatible with updated helper functions
- [modules/members/view.php](modules/members/view.php) - Compatible with updated helper functions

### ✅ **Dashboard**
- [src/php/index.php](src/php/index.php) - Updated to use `$username` instead of `$full_name`

---

## Key Changes Summary

### Migration from Old to New Schema

| Old Table | New Table | Notes |
|-----------|-----------|-------|
| `users` | `tbl_user` | Now uses `username` + `password_hash` + `role` |
| `members` | `tbl_member` | Standalone table (no user_id dependency) |
| `events` | `tbl_event` | Simplified structure |
| `documents` | `tbl_document` | Directly linked to events |
| `finance` | `tbl_transaction` | Simplified transaction tracking |
| All others | Removed | No longer needed (projects, budget, audit_log, etc.) |

---

## Default Admin Account

After running the database initialization script:

```
Username: admin
Email: admin@kebana.local
Password: Admin@123
Role: Super Admin
```

⚠️ **Important:** Change this password immediately after first login.

---

## Function Changes in members_helper.php

### Removed Functions
- `getAvailableUsersForMember()` - No longer needed (no user_id relationship)
- `getMemberByUserId()` - Removed
- `userHasMemberProfile()` - Removed

### Updated Function Signatures

**addMember()**
```php
// OLD
addMember($conn, $user_id, $member_data)

// NEW
addMember($conn, $member_data)
```

### New Functions Added
- `getMembersByStatus($conn, $status)` - Get members by status
- `searchMembers($conn, $search_term)` - Search by name or IC
- `getMemberCount($conn)` - Get total member count

---

## Session Variables

### Before
```php
$_SESSION['full_name']
$_SESSION['email']
$_SESSION['role']
```

### After
```php
$_SESSION['username']
$_SESSION['email']
$_SESSION['role']
```

---

## Next Steps

1. **Reset Database:**
   - Drop all existing tables
   - Run `database.sql` to create new schema

2. **Update HTML Forms:**
   - Update member add/edit forms to reflect new fields:
     - ❌ Remove: date_of_birth, address, city, state, postal_code, occupation, membership_date, renewal_date
     - ✅ Use: full_name, ic_number, village, phone_no, status

3. **Update Registration Form:**
   - ❌ Remove: full_name, phone fields from user registration
   - ✅ Use: username, email, role selection

4. **Create Module Views:**
   - Event management module
   - Document management module
   - Transaction/Finance module

5. **Testing:**
   - Test user login with new credentials
   - Test member CRUD operations
   - Verify all session handling

---

## Backward Compatibility Notes

❌ **Not backward compatible** - This is a complete schema redesign
- Old data cannot be automatically migrated
- All related code has been updated
- Forms must be manually updated to reflect new fields

---

## File References

- Schema Definition: [database.sql](database.sql)
- DB Connection: [includes/dbconnect.php](includes/dbconnect.php)
- Auth Handler: [modules/auth/authenticate.php](modules/auth/authenticate.php)
- Member Functions: [includes/members_helper.php](includes/members_helper.php)
- Session Check: [includes/auth.php](includes/auth.php)

---

## Support

For questions about the new schema structure, refer to the database.sql file which includes inline comments explaining each table's purpose.
