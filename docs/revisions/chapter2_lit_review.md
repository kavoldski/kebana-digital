# Chapter 2 Revisions: Literature Review & Gap Analysis

Copy and paste the sections below to replace or update the corresponding parts in **Chapter 2** of your FYP report.

---

## 2.4 Comparison of Existing Systems

*Replace Table 2.1 and its preceding paragraph with the updated content below:*

To understand the specific design parameters for the KEBANA Digital Management System (KDMS), a comparative analysis was conducted between the market leaders, manual general-purpose tools, and the proposed system.

### Table 2.1: Comparison of Existing Systems vs. Proposed KDMS

| Feature / Criteria | WildApricot (Commercial) | CiviCRM (Open-Source) | Microsoft Excel / WhatsApp (Current Method) | Proposed KDMS (PHP & AI Integrated) |
| :--- | :--- | :--- | :--- | :--- |
| **Licensing & Hosting Cost** | High (USD 60 - 400+/month recurring fee). Prohibitive for local community groups. | Free core license, but requires expensive dedicated servers (~RM150+/month) and high IT maintenance. | Low / Free (included in basic office suites). | **Extremely Low Cost:** Runs on entry-level PHP Shared Hosting (~RM10 - RM15/month). Perfectly sustainable on community budgets. |
| **User Friendliness** | Medium. Complicated, US-centric dashboard; requires high digital literacy. | Low. Technical interface; requires dedicated staff training. | High. Extremely familiar to all committee members. | **High / Premium:** Tailored, minimalist Tailwind CSS interface with native Malay language terminology. |
| **Data Centralization** | Yes. Centralized database. | Yes. Centralized database. | No. Scattered local files; high risk of duplication and version conflicts. | **Yes:** Single MySQL source of truth with multi-branch (Cawangan) scoping. |
| **Data Security & Access Control** | High. Standard role-based access. | High. Fine-grained custom permissions. | Low. Sharing of spreadsheets (containing ICs and phone numbers) over WhatsApp is a major leakage risk. | **High:** Secure password hashing (BCrypt), session timeouts, and IP-tracked audit logs (`tbl_audit_log`). |
| **Data Entry Efficiency** | Low. Manual form filling per contact. | Low. Manual form filling per contact. | Medium. Copy-paste but prone to typos. | **Extremely High:** Integrated OCR engine (`Tesseract.js`) extracts and autofills profiles from paper scans in under 15 seconds. |
| **Event Coordination** | Ticketing and public registration. | Bulk email campaigns and event management. | Basic event schedules shared via text chats. | **Structured Hierarchy:** Scoped Master-to-Sub event links, multi-stage approvals, and QR code check-in attendance. |
| **Knowledge Preservation** | Standard file attachment. | Case management files. | None. Document files lost in chat histories or damaged local hard drives. | **AI-Powered RAG Search:** PDF parser indexes documents into vector embeddings (via Gemini API) for natural language querying. |

---

## 2.5 Analysis of Gaps (The Problem with Existing Solutions)

*Replace Section 2.5 with this updated text that explains the rationale behind the chosen technology stack:*

The gaps identified in the current market demonstrate why a custom web application is the only viable path for KEBANA:

1. **The Shared Hosting Compatibility Gap:**
   While open-source giants like CiviCRM are technically free, their system requirements (requiring VPS servers, command-line cron configuration, and heavy memory footprints) represent a massive technical and financial barrier. By building a custom application in **clean PHP and MariaDB**, KDMS can be hosted on simple, low-cost PHP shared hosting environments (such as CPanel). This provides KEBANA with total technical independence and a negligible hosting cost of less than RM120 per year.

2. **The Data Entry Bottleneck:**
   Rural non-governmental organizations regularly struggle with "digital resistance" among volunteers who dislike typing extensive member forms. Standard commercial platforms do not address this manual labor burden. Integrating client-side **OCR (Optical Character Recognition)** directly into the membership module removes this friction. Volunteers can scan physical registration papers using a desktop webcam or mobile camera, and the system automatically fills the fields, bridging the digital literacy gap.

3. **The Loss of Institutional Memory:**
   As community leadership changes over time, historical knowledge (meeting minutes, previous grant proposals, and event reports) is consistently lost in physical folders or local drives. While typical systems offer basic file uploads, finding relevant files requires knowing their specific filenames. By implementing **Retrieval-Augmented Generation (RAG)** utilizing the Google Gemini API, KDMS allows new or active committee members to conversationally query the system's institutional database, instantly unlocking decades of past organizational wisdom.
