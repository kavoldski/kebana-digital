# LITERATURE REVIEW

## IEEE SMC / JCSI Formatting Instructions
- **Section Headers**: 14pt Bold, left justified, numbered.
- **Subsection Headers**: 12pt Bold, left justified.
- **Citations**: APA style. Author surname and year: e.g., (Nellson & John, 2004) or Krause et al. (2006).

---

## 2. LITERATURE REVIEW

### 2.1 Overview
*   This section reviews existing literature on digital strategies for non-governmental organizations (NGOs), the digital landscape in rural Malaysia and Sarawak, and compares existing association management software to establish the research gap.

### 2.2 Digital Transformation in NGOs

#### 2.2.1 The Role of Digital Strategy in NGO Efficiency
*   *Key concepts to write:* The shift from paper/manual storage to digital systems in non-profits. Benefits include reduced operational costs, faster response times, and improved data tracking. Cite general literature on non-profit information systems.

#### 2.2.2 Digital Transformation in the Malaysian Rural Context
*   *Key concepts to write:* Bridging the digital gap. Challenges of internet connectivity, digital literacy in indigenous groups, and remote administrative work. Importance of simplified user interfaces for non-technical community leaders.

#### 2.2.3 Post-COVID Development Strategy (PCDS 2030)
*   *Key concepts to write:* Sarawak’s government plan for digital economic growth and social inclusivity. Highlight how transforming rural community management organizations via technology directly aligns with the social inclusion goals of PCDS 2030.

### 2.3 Review and Comparison of Existing Systems
*   Review alternative approaches and systems used by non-profits and community associations.

#### 2.3.1 Commercial Association Management System: WildApricot
*   *Key features:* Cloud-based membership CRM, event registration, website builder.
*   *Limitations for KEBANA:* High monthly subscription fees, complex options built for Western organizations, lacks localized custom roles or native multi-branch (cawangan) structures without premium plans.

#### 2.3.2 Open-Source Solution: CiviCRM
*   *Key features:* Highly customizable open-source CRM for non-profits.
*   *Limitations for KEBANA:* Requires integration with CMS (Drupal, WordPress), heavy server requirements, high technical learning curve for non-IT administrators, complex setup.

#### 2.3.3 Current Practice: Microsoft Excel & Manual Records
*   *Key features:* Free, offline, low barrier of entry.
*   *Limitations for KEBANA:* Multi-user conflicts, data duplication, formula vulnerability, no document attachments, no role-based security, prone to data loss.

### 2.4 Existing Systems Comparison Table
*   *Insert table comparing WildApricot, CiviCRM, Excel, and KEBANA Digital Management System based on features like cost, customization, ease of use, RBAC, offline capability, and cawangan support.*

### 2.5 Technology Stack Selection Rationale
*   **PHP**: Chosen for its robust backend capabilities, standard integration with Apache, and widespread support on shared web servers.
*   **MySQL**: Relational structure is perfect for linking members to branches and events to uploaded documents.
*   **Vanilla JS/CSS**: Ensures high browser performance without bulky frameworks, which is critical for slower networks in rural areas.
*   **XAMPP**: Chosen to allow local server setup at headquarters for offline-first backup operations.

### 2.6 Summary & Identified Research Gap
*   While mature systems exist, they are either too expensive (WildApricot) or too complex (CiviCRM) for rural Sarawakian NGOs. A custom, lightweight, role-scoped, multi-branch enabled digital management platform satisfies a critical localized need.
