# METHODOLOGY

## IEEE SMC / JCSI Formatting Instructions
- **Tables**: Place as close as possible to the mention text. Table headings should be placed (centered) above the table in Arabic numbers (e.g., Table 1).
- **Figures**: Place as close as possible to mention text. Figure captions centered beneath the figure (e.g., Figure 1).
- **Subsections**: Up to 4 levels allowed (e.g., 3.1.1.1). Font size 12pt Bold.

---

## 3. METHODOLOGY

### 3.1 Agile Development Methodology
*   Explain why Agile was used: Iterative feedback cycles, collaboration with KEBANA administrators, ability to adapt to changing database requirements during development.

#### 3.1.1 Development Lifecycle Phases
1.  **Requirements Gathering**: Conducted unstructured interviews with KEBANA central committee members to document their physical workflows.
2.  **System Design**: Created database schema, ERDs, use cases, and wireframes.
3.  **Iterative Development**: Developed core modules (Authentication, Members, Events) followed by secondary modules (Finance, Documents, Announcements, Cawangan isolation).
4.  **Testing**: Executed Unit testing and conducted UAT sessions.
5.  **Deployment & Deployment Review**: Finalizing deployment configuration on a local server environment.

### 3.2 Requirements Analysis

#### 3.2.1 Functional Requirements (FR)
*   **FR-01 (Authentication)**: User authentication via username and bcrypt hashed password.
*   **FR-02 (User Roles/RBAC)**: Enforce access controls for Super Admin, Secretary, and Treasurer.
*   **FR-03 (Member Management)**: Full CRUD (Create, Read, Update, Delete) capability on member profiles (Name, IC, Village, Phone, Status).
*   **FR-04 (Event Management)**: Central and branch secretaries can create and manage events.
*   **FR-05 (Document Archiving)**: Link event proposals, financial reports, or receipts to events.
*   **FR-06 (Finance Ledger)**: Records income/expense transactions, amounts, dates, and categories.
*   **FR-07 (Multi-Branch Isolation)**: Branch users (cawangan) can only view and edit records associated with their designated branch, while central Super Admin / Treasurer can view all.
*   **FR-08 (Announcements)**: Secretary can publish news with up to 5 image uploads.

#### 3.2.2 Non-Functional Requirements (NFR)
*   **NFR-01 (Security)**: Hash passwords, use parameterized SQL queries to prevent SQL injections.
*   **NFR-02 (Performance)**: Page loading times under 2 seconds.
*   **NFR-03 (Compatibility)**: Cross-browser responsive interface.

### 3.3 Database Design (ERD & Schema)
*   Describe the simplified 6-table database schema designed for the system:
    1.  `tbl_user` (user_id, username, password_hash, role, email, cawangan_id, created_at, updated_at)
    2.  `tbl_member` (member_id, full_name, ic_number, village, phone_no, status, cawangan_id, created_at, updated_at)
    3.  `tbl_event` (event_id, event_title, event_date, venue, budget_est, created_by, cawangan_id, created_at, updated_at)
    4.  `tbl_document` (doc_id, event_id, doc_name, file_path, uploaded_at)
    5.  `tbl_transaction` (trans_id, trans_type, amount, category, trans_date, recorded_by, cawangan_id, created_at, updated_at)
    6.  `tbl_announcement_image` (image_id, announcement_id, image_path, created_at)

### 3.4 System Architecture & RBAC Flow
*   Provide a workflow outline of how Role-Based Access Control (RBAC) and Multi-Branch scoping work:
    *   On login, user's `role` and `cawangan_id` are loaded into the session.
    *   Access checks block unauthorized URLs (e.g., a branch user accessing the user directory).
    *   Database queries dynamically apply `WHERE cawangan_id = X` filters if the user is not a central "Pusat" super-user.

### 3.5 Testing Methods
*   **Unit Testing**: Manual test cases executed during development to test individual helper functions, authentication scripts, and database insertion/retrieval.
*   **User Acceptance Testing (UAT)**: Scenario-based testing sessions with KEBANA admins using a feedback questionnaire covering usability, visual appearance, system functionality, and performance.
