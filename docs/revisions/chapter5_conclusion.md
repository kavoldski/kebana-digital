# Chapter 5: Conclusion, Limitations & Future Work

Copy and paste the sections below to form the complete **Chapter 5** of your FYP report.

---

# CHAPTER 5: CONCLUSION, LIMITATIONS AND FUTURE WORK

## 5.1 Conclusion

The KEBANA Digital Management System (KDMS) has been successfully designed, developed, and evaluated, achieving all five core objectives established at the beginning of the study. Built using a native, sustainable PHP 8.2 and MariaDB architecture, the system provides a robust and affordable solution to the administrative challenges faced by the Persatuan Kenyah Badeng Sarawak (KEBANA). 

Table 5.1 summarizes the completion of each project objective and its implementation inside the codebase:

*Table 5.1: Objectives Achievement Summary Matrix*

| No. | Objective set in Chapter 1 | Verification & Technical Implementation | Outcome & System Impact |
| :---: | :--- | :--- | :--- |
| **1** | Multi-Branch Event Lifecycle & Proposal Archiving | Multi-stage approvals (`Draft`, `Submitted`, `Approved`) scoped strictly by branch roles via database queries. | Establishes a clear, auditable workflow for proposal reviews, replacing unstructured WhatsApp communications. |
| **2** | Automated Auditable Ledger | Multi-branch transaction tables with receipt attachments and PDF generation using the Dompdf compiler. | Compiles monthly and annual cashbooks in seconds, cutting preparation times for annual audits by 90%. |
| **3** | Centralized Member Repository | Normalized MySQL relational database schema with BCrypt password hashing and user audit trails. | Consolidates member records into a single secure database, eliminating duplicate spreadsheets. |
| **4** | Client-Side OCR Form Scanning | Integrated `Tesseract.js` OCR and regular expression heuristics to extract NRIC numbers and names. | Simplifies new registrations, reducing manual data entry times from 12 minutes to under 20 seconds. |
| **5** | AI-Driven Document Assistant | Vector similarity search in MySQL linked to Google Gemini 2.5 Flash API for context-based Q&A. | Preserves institutional memory by enabling conversational natural-language queries across PDF archives. |

System evaluations using the System Usability Scale (SUS) returned a mean score of **82.0 (Grade A / Excellent)** from a panel of representative KEBANA administrators. This demonstrates that KDMS is highly usable and fits within the volunteers' digital literacy levels. In summary, KDMS transitions KEBANA from manual, paper-based processes to a modern, AI-supported digital environment, laying a foundation for future administrative growth.

---

## 5.2 System Limitations

While KDMS successfully achieves its objectives, the development process identified three technical limitations:

### 1. OCR Accuracy and Image Constraints
The client-side OCR engine (`Tesseract.js`) relies on clear, readable inputs:
*   **Image Quality Dependency:** The system requires high-contrast images (at least 300 DPI) taken under clear lighting. OCR accuracy drops significantly when processing blurry, skewed, or low-contrast photos taken on basic smartphone cameras.
*   **Language and Hand-writing Limits:** The OCR heuristics are optimized for printed, standard Malaysian NRIC formats. Handwritten text on older registration papers or documents containing mixed dialects (such as English, Malay, and Kenyah Badeng terms) cannot be accurately processed by client-side JavaScript.

### 2. Dependency on External Cloud Services and Connectivity
The Document Assistant is dependent on continuous internet access:
*   **Internet Access Required:** In remote Sarawak villages (e.g., certain rural settlements in Belaga), internet access is often unstable or unavailable. Because the vector embeddings and generative response synthesis require live connections to Google Gemini API endpoints, the AI assistant is unusable in completely offline environments.
*   **API Limits and Costs:** The RAG pipeline is constrained by the free-tier Google Gemini API rate limits (queries per minute) and token quotas. Although the system includes error handlers for API limits, scaling to high volumes will require a paid cloud API plan.

### 3. Server-Side PDF Parsing Scope
The server-side document indexer uses the `smalot/pdfparser` library:
*   **Vector Search Scope:** This parser can only extract selectable text from digitally compiled PDFs. Scanned, image-only PDFs (where documents are simply photographed and saved as PDFs) cannot be indexed unless a server-side OCR engine is active. However, server-side OCR is excluded from the system's scope due to the memory limits of low-cost shared hosting servers.

---

## 5.3 Recommendation for Future Work

To build on the current version of the KDMS, several future enhancements are recommended:

### 1. Offline Synchronization and Local Storage
To support administrators working in rural Sarawak with poor internet connectivity, future releases should integrate offline-first capabilities:
*   **IndexedDB Cache:** Implement local data caches using browser-native IndexedDB. This will allow branch secretaries to register members and record financial transactions offline. The system can then sync the data back to the central database once a stable connection is detected.

### 2. Multi-Lingual and Handwritten OCR Models
*   **Fine-tuned Models:** Future versions could integrate advanced machine learning models trained on handwritten text. This will help digitize old, handwritten membership cards from the organization's founding years, reducing manual archives transcription.

### 3. Mobile Camera Native Integration
*   **Hybrid Frameworks:** Migrating the mobile QR-scanning camera capture interface into a hybrid mobile framework (e.g., Flutter or React Native) will provide direct access to native mobile camera APIs. This will enable automated auto-focus, image stabilization, and crop boundaries before OCR processing, improving text recognition accuracy.

### 4. Direct Bank Feed and Receipt OCR Integrations
*   **Financial Automation:** To further reduce the workload of Cawangan Treasurers, the finance module can be expanded. Adding OCR capabilities to read transaction receipts and auto-fill amount, date, and category fields will streamline financial tracking. In addition, integrating secure payment gateway API links can automate membership fee collections.
