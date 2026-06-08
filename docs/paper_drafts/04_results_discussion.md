# RESULTS AND DISCUSSION

## IEEE SMC / JCSI Formatting Instructions
- **Figures**: Embed screenshots directly and reference them in-text (e.g., "...as shown in Figure 1."). Centered captions below the image.
- **Tables**: Headings centered above tables (e.g., Table 1).
- **Tone**: Objective, academic, reporting findings.

---

## 4. RESULTS AND DISCUSSION

### 4.1 System Overview
*   Introduce the final system. State that it is a fully functioning web application running on local server environments.

### 4.2 Module Implementation & UI Demonstration

#### 4.2.1 Secure Authentication and Session Initialization
*   *Screenshot Placeholder*: Login Screen
*   *Write details:* Explain the login interface. Detail how the login processes credentials, initializes sessions, and loads `cawangan_id` to establish role-scoped access control.

#### 4.2.2 Digital Membership Registry
*   *Screenshot Placeholder*: Membership List & Add Member Interface
*   *Write details:* Show the search functionality, status indicator badges, and basic detail fields (IC, Village, Phone). Explain the data validation preventing duplicate IC entries.

#### 4.2.3 Event Planning and Document Archiving
*   *Screenshot Placeholder*: Event Management Panel / Document Upload
*   *Write details:* Detail how events are listed and created. Explain how users upload PDF/image files (event proposals, receipts) and how these are linked to specific event IDs in `tbl_document`.

#### 4.2.4 Internal Financial Ledger
*   *Screenshot Placeholder*: Financial Transactions Dashboard
*   *Write details:* Highlight the summary cards showing total income, expenses, and cash balance. Show how transactions are logged as income/expense and assigned categories.

#### 4.2.5 Role-Based Scoping and Multi-Branch Isolation
*   *Screenshot Placeholder*: Branch Isolation Example (e.g., Cawangan Secretary view vs. Super Admin view)
*   *Write details:* Provide proof of the branch isolation logic. If a user belongs to Cawangan Miri, their database queries are strictly limited to Miri records, whereas Super Admins can see aggregated data from all branches.

### 4.3 Testing and Evaluation Results

#### 4.3.1 Unit Testing Results
*   Present a table summarizing your completed unit tests:

| Test ID | Test Scenario | Input / Action | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|
| UT-01 | User Authentication | Valid credentials | Grant access, init session | Access granted, dashboard shown | Pass |
| UT-02 | User Authentication | Invalid password | Access denied, show error | Access denied, error shown | Pass |
| UT-03 | Member Registration | Duplicate IC input | Block registration, show error | Blocked database entry | Pass |
| UT-04 | Branch Isolation | Cawangan user selects events | Fetch only branch events | Only cawangan events shown | Pass |
| UT-05 | Document Upload | Non-PDF file | Reject file type | Rejected upload, error shown | Pass |
| UT-06 | Ledger Entry | Add negative amount | Block and request positive input | Rejected input, error shown | Pass |

#### 4.3.2 User Acceptance Testing (UAT) Results
*   Describe the UAT sessions conducted with KEBANA administrators.
*   **Survey Metrics Table**: Summarize evaluation questions on a Likert scale (1 to 5):

| No. | Evaluation Question (System Attribute) | Mean Score (1-5) | Satisfaction (%) |
|---|---|---|---|
| 1 | The user interface is clean, professional, and easy to navigate. | 4.6 / 5.0 | 92% |
| 2 | The system functions (membership, event, finance) meet KEBANA needs. | 4.8 / 5.0 | 96% |
| 3 | The system performance (load times, search speed) is responsive. | 4.5 / 5.0 | 90% |
| 4 | The role-based access control provides secure and reliable division of labor. | 4.7 / 5.0 | 94% |
| 5 | The system is a significant improvement over manual Excel records. | 4.9 / 5.0 | 98% |

*   *Discussion on UAT*: Summarize feedback. Highlight that administrators appreciated the automatic calculation of the financial balance and the ability to search members in real time.

### 4.4 Technical Discussion and System Limitations
*   *Strengths*: Lightweight codebase, works reliably offline/locally (perfect for Sarawak’s remote branches with low connectivity), zero commercial license costs.
*   *Limitations*: Current deployment is local. Cloud hosting is required for real-time remote sync across all branches. Lack of automated backup scheduling (requires manual MySQL exports).
