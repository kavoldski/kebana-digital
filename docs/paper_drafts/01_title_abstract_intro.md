# TITLE, ABSTRACT, AND INTRODUCTION

## IEEE SMC / JCSI Formatting Instructions
- **Title**: Times New Roman 20pt, centered.
- **Authors**: Times New Roman 11pt, centered.
- **Abstract**: 10pt Italic, single column, left and right justified. No references, no acronyms.
- **Main Text**: Times New Roman 10pt, single column, single line spacing, left and right justified.
- **Section Headers**: 14pt Bold, left justified, numbered consecutively.
- **Paragraphs**: Indented from left margin; no blank lines between paragraphs.

---

## TITLE
**DIGITAL MANAGEMENT SYSTEM FOR PERSATUAN KENYAH BADENG SARAWAK (KEBANA)**

---

## ABSTRACT
*Type your abstract here. It should be summarized in about 200 words. Use short, direct, and complete sentences. It should be complete, self-explanatory, and not require reference to the paper itself.*

### [Drafting Prompts & Bullet Points for Gemini]
*   **Context/Problem**: Persatuan Kenyah Badeng Sarawak (KEBANA) is an NGO representing the Kenyah Badeng community. Traditionally managed operations using manual methods, paper forms, and isolated Excel spreadsheets. Led to data duplication, loss of documents, security concerns, and financial tracking discrepancies.
*   **Proposed Solution**: Developed a localized, web-based Digital Management System using PHP and MySQL. Designed specifically to meet KEBANA’s administrative requirements.
*   **Key Features**: Includes a Digital Membership Registry, Event Proposal Management, Document Archiving, an Internal Financial Ledger, and Role-Based Access Control (RBAC). Also integrated multi-branch (cawangan) data isolation to manage different local chapters.
*   **Methodology & Evaluation**: Developed using the Agile methodology. Evaluated using Unit Testing and User Acceptance Testing (UAT) with KEBANA administrators.
*   **Key Results**: Digitized member records for quick search/retrieval, streamlined event and document tracking, improved financial reporting accuracy, and isolated cawangan access. Received positive UAT feedback on usability.
*   **Contribution**: Demonstrates a practical, cost-effective digital transformation model for rural-oriented and indigenous community NGOs in developing regions like Sarawak.

---

## 1. INTRODUCTION

### 1.1 Background of the Study
*   **Community Profile**: Introduce the Kenyah Badeng people, an indigenous Orang Ulu group in Sarawak, Malaysia.
*   **Association Background**: Detail Persatuan Kenyah Badeng Sarawak (KEBANA) as a registered NGO working to preserve culture, support welfare, and coordinate community activities.
*   **Digital Context**: Highlight the digital landscape in Sarawak (PCDS 2030 initiatives, rural digital divide, and the need for localized NGO solutions).

### 1.2 Problem Statement
*   **Data Fragmentation**: Member registry scattered across multiple Excel files. Impossible to maintain an accurate count of active/inactive members.
*   **Inefficient Event and Document Archiving**: Paper-based event proposals, receipts, and reports are easily misplaced, leading to loss of institutional knowledge.
*   **Lack of Financial Accountability**: Excel-based financial ledgers are prone to accidental formula errors and do not support audit trails or transaction categorization.
*   **No Multi-Branch Control**: Lack of role-based separation makes it difficult for branch (cawangan) secretaries and treasurers to input data securely without interfering with central headquarters.

### 1.3 Research Objectives
1.  To analyze the existing administrative processes of KEBANA and identify operational bottlenecks.
2.  To design and develop a secure, web-based digital management system containing membership, events, documents, finance, and announcements.
3.  To implement a role-based access control (RBAC) mechanism with cawangan-level data isolation.
4.  To evaluate the system's functionality and user acceptance through structured testing protocols.

### 1.4 Scope of the Work
*   **Inclusions**: Web-based PHP/MySQL portal, User Management (Super Admin, Secretary, Treasurer), Member CRUD, Event tracking, File uploads, Financial ledger, Announcements, Multi-branch database isolation.
*   **Exclusions**: Public payment gateway integration (uses manual upload of receipts), Native mobile application (uses responsive web design instead), Offline-synchronization (requires active local/internet connection).

### 1.5 Significance of the Study
*   For **KEBANA**: Streamlines administrative workflow, saves time, ensures data integrity, and improves transparency.
*   For **Sarawak/Academia**: Offers a case study and practical template for digitizing indigenous community organizations with restricted budgets.
