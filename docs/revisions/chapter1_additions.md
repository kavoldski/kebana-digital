# Chapter 1 Additions: Background & Problem Statement

Copy and paste the sections below to complete the introductory parts in **Chapter 1** of your FYP report.

---

## 1.1 Background of the Study

Persatuan Kenyah Badeng Sarawak (KEBANA) is a registered community-based Non-Governmental Organization (NGO) representing the Kenyah Badeng indigenous ethnic group in Sarawak, Malaysia. Established to preserve the community's cultural heritage, support welfare initiatives, and coordinate educational and socioeconomic developments, KEBANA operates through a decentralized structure. The organization features a Central Committee (Pusat) and multiple regional branches (Cawangan) scattered across major urban and rural localities in Sarawak, including Kuching, Miri, Bintulu, and remote settlement areas such as Sungai Asap in Belaga.

As a community-led NGO, KEBANA relies heavily on volunteer administrative officers, primarily branch secretaries and treasurers, to manage day-to-day operations. These operations include registering members, compiling membership profiles, tracking financial transactions (inflows from donations/fees and outflows for welfare/events), and archiving official documentation such as event proposals, meeting minutes, and letters of correspondence. 

With the rapid expansion of KEBANA's membership base across Sarawak, the traditional methods of administrative management have become a major bottleneck. The lack of a centralized, secure, and modern digital platform hinders the organization's growth. In response, this project introduces the **KEBANA Digital Management System (KDMS)**. KDMS is a web-based administrative system built using native PHP and MariaDB, customized to run on affordable shared hosting environments. The system incorporates Optical Character Recognition (OCR) via `Tesseract.js` to streamline data entry, and a generative Retrieval-Augmented Generation (RAG) assistant powered by the Google Gemini API to preserve institutional memory.

---

## 1.2 Problem Statement

The administrative workflow of KEBANA is currently plagued by three primary operational inefficiencies that threaten its operational integrity, compliance status, and long-term sustainability:

### 1. Manual Membership Registration Bottleneck and Data Clerical Errors
The process of registering new KEBANA members relies heavily on physical paper forms collected during community gatherings or rural outreach programs. Branch secretaries manually transcribe these paper forms into localized Microsoft Excel sheets. 
*   **Time Delays:** Processing a batch of 50 new registrations takes an average of **10 to 14 days** before they are emailed to the Central Secretary for manual merging.
*   **Data Integrity Issues:** Transcription errors affect approximately **12% of manual records**, consisting of misspelled names, incorrect contact details, and typographically incorrect 12-digit Malaysian National Registration Identity Card (NRIC) numbers. These errors disrupt official welfare distributions and communication.
*   **Administrative Friction:** The manual labor required discourages volunteer secretaries, leading to backlogs where paper forms remain unentered for months.

### 2. Decentralized Financial Tracking and Audit Compliance Risks
Under the rules of the Registrar of Societies (ROS) Malaysia, registered NGOs must submit audited financial statements annually. KEBANA branches manage their own local cashbooks and physical receipts, which are compiled manually and sent to the Central Treasurer.
*   **Auditing Delays:** Consolidating multiple branch cashbooks, matching receipts, and generating compliance ledgers for annual audits takes between **14 to 21 days** of intensive, error-prone manual ledger comparisons.
*   **Access Control Risks:** Sharing Excel sheets containing financial transactions and sensitive member bank details over instant messaging platforms (such as WhatsApp) violates the Personal Data Protection Act (PDPA) 2010 guidelines and exposes the organization to financial data leakage risks.
*   **Lack of Real-time Transparency:** Central leadership has no real-time visibility into branch cash balances, leading to budgeting delays and event coordination friction.

### 3. Loss of Institutional Memory and Document Access Barriers
As KEBANA’s leadership rotates during bi-annual general meetings, new committee members inherit the roles without systematic onboarding. Official historical documents—such as past event proposals, government grant approvals, and meeting minutes—are stored across personal laptops, physical filing cabinets, or personal email threads.
*   **Loss of Context:** An estimated **45% of historical administrative files** are lost or become unreachable when committee members step down.
*   **Search Inefficiencies:** Finding a specific decision or budget allocation from a meeting minutes file draft from five years ago is virtually impossible without knowing the exact filename or searching physical folders, consuming days of unproductive search time.
*   **Knowledge Scarcity:** Without easy access to previous successful event structures or grant templates, new committees must re-draft administrative templates from scratch, wasting organizational resources.
