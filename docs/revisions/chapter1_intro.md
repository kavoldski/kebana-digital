# Chapter 1 Revisions: Introduction & Project Scope

Copy and paste the sections below to replace or update the corresponding parts in **Chapter 1** of your FYP report.

---

## 1.3 Aims & Objectives

*Replace the existing objectives list with this updated, highly professional list that captures all the advanced features of your codebase:*

This project aims to achieve the following core objectives:

1. **Multi-Branch Event Lifecycle & Proposal Archiving:**
   To design and develop an event management module that enables KEBANA Central (Pusat) and Cawangan (Branch) committee members to dynamically create, coordinate, and archive event proposals, meeting minutes, and post-mortem reports through a structured, multi-tier approval workflow.

2. **Automated Auditable Ledger:**
   To implement a secure, scoped financial ledger capable of tracking branch and central income and expenditures, generating compliant monthly and annual financial statements (via automated PDF rendering), and maintaining complete transparency for auditing.

3. **Centralized Member Repository (Single Source of Truth):**
   To create a unified database schema for registering, managing, and searching the personal profiles of all KEBANA members, resolving the data redundancy and security risks of decentralized spreadsheets.

4. **Client-Side OCR Automation (OCR Form Scanning):**
   To integrate client-side Optical Character Recognition (OCR) using `Tesseract.js`—supporting both desktop local file uploads and dynamic, QR-linked mobile camera scanning—to instantly extract and pre-populate member profile details (Name, IC, Gender, Location), minimizing manual data entry bottlenecks.

5. **AI-Driven Document Assistant (RAG Integration):**
   To incorporate a Retrieval-Augmented Generation (RAG) assistant leveraging the Google Gemini API (`gemini-embedding-001` and `gemini-2.5-flash`) to parse archived event documents, generate secure vector embeddings, and enable committee members to query institutional archives using conversational, natural language.

---

## 1.4 Scope

*Replace the existing Scope description with this comprehensive breakdown, detailing the actual inclusions of your robust PHP application:*

The **KEBANA Digital Management System (KDMS)** is designed as a secure, lightweight, and modern administrative web application tailored for the distributed committee members of Persatuan Kenyah Badeng Sarawak. 

### 1.4.1 System Inclusions

To meet the administrative demands of KEBANA's multi-branch structure, the scope of the system incorporates the following functional inclusions:

1. **Multi-Branch Scoped Role-Based Access Control (RBAC):**
   - Hierarchical access control with specific integer-coded roles representing Central Leadership (Pusat, roles 1-7), Branch Officers (Cawangan, roles 11-66), and System Admins (role 888).
   - Data scoping that restricts Branch Secretaries and Treasurers to their designated `cawangan_id` data, while granting Pusat officers master read/write access.

2. **Automated OCR Member Registration:**
   - A dual-mode Optical Character Recognition engine.
   - **Local Scanning Mode:** Direct dropzone upload of physical member form images on a desktop browser.
   - **Mobile QR Capture Mode:** Desktop generates a temporary session QR code which is scanned by a mobile phone camera. The mobile device captures the form and uploads it, triggering instant browser-side Tesseract OCR parsing.

3. **AI-Enabled Knowledge Management (RAG):**
   - An intelligent archiving repository where PDF documents (e.g., meeting minutes, official approval letters) are parsed using `smalot/pdfparser` on the server-side.
   - A chunking and indexing utility that generates vector embeddings using Google's `gemini-embedding-001` model.
   - An interactive RAG chat widget utilizing `gemini-2.5-flash` to synthesize context-aware answers to natural language administrative queries.

4. **Event Management & Multi-Stage Approvals:**
   - Hierarchical event structuring ("Master" events vs "Sub" events).
   - Multi-tier workflow states (`Draft`, `Submitted`, `Approved`, `Rejected`) that track the progression of proposals from Cawangan drafting through to Central President approval.

5. **Self Check-In & QR Attendance:**
   - Automated QR code generator for active events.
   - Allows members to perform self check-in via a QR scan at local meetings, automatically updating check-in states in the attendance database.

6. **Secured Communication & Encryption:**
   - A real-time encrypted messaging module (`tbl_chat`) enabling secure, end-to-end scrambled communications between active committee members.

7. **Security Logs & Audit Trails:**
   - A centralized security ledger (`tbl_audit_log`) that logs detailed user actions, modules modified, transaction categories, and active IP addresses to ensure robust administrative accountability.

### 1.4.2 System Exclusions

To ensure feasibility within the academic timeline, the following elements remain excluded from the scope:
1. **Real-time Payment Gateway Integration:** The system tracks manual transactions, receipt uploads, and cash ledger balances but does not integrate real-time merchant credit/debit card processing or automated bank feeds.
2. **Native Mobile App Compilation:** The system is fully optimized as a mobile-responsive web application (using CSS Flexbox, Grid, and Tailwind CSS media queries) but will not be compiled into native Android (APK) or iOS packages.
