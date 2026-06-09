# KEBANA Digital Management System - Technology Stack

This document details the technology stack, architecture, and libraries used in the development of the **KEBANA Digital Management System** (Persatuan Kenyah Badeng Sarawak).

---

## 1. Core Framework & Architecture

The application is built on a custom lightweight Model-View-Controller (MVC) and modular architecture in PHP:

- **Backend Language**: PHP (Native/OOP hybrid)
- **Bootstrap & Routing**: Custom-built `bootstrap.php` mapping environments (localhost vs. live production hosting), managing secure filesystem uploads, loading database credentials, and registering a custom autoloader for the `App\` namespace (`app/` directory).
- **Dependency Management**: Composer for PHP packages.
  - **`smalot/pdfparser` (v2.12)**: Used to extract raw, native text from uploaded PDF documents.
  - **`dompdf/dompdf` (v3.1)**: Used to compile and generate structured PDF reports (e.g., annual general reports, financial summaries).

---

## 2. Artificial Intelligence & RAG System

The KEBANA Digital Management System integrates advanced AI capabilities using the Google Gemini API, wrapped in dedicated service classes:

- **AI Services (`app/Services/`)**:
  - **`AIService`**: Connects to `gemini-2.5-flash` for:
    - Synthesizing official announcement drafts from brief user notes/keywords with dynamic tone configurations.
    - Multimodal receipt parsing (OCR) to extract amounts, transaction dates, and expense categories from uploaded images (PNG/JPG).
  - **`EmbeddingService`**: Interacts with `gemini-embedding-001` to generate 768-dimensional text embeddings, and provides a utility function to compute the **Cosine Similarity** between vectors.
  - **`TextExtractorService`**: Automatically parses content from uploaded `.pdf`, `.docx`, `.txt`, `.jpg`, `.jpeg`, and `.png` files (using Gemini's multimodal features as a fallback for scanned documents). Implements a **Recursive Character Text Splitter** (`chunkText`) that maintains semantic boundaries (e.g. paragraphs, table rows) within a target chunk size of 1200 characters and 200 character overlap.
  - **`RAGService`**: Implements a complete **Retrieval-Augmented Generation (RAG)** pipeline.
    1. **Index**: Extracts text, splits it into chunks, generates vector embeddings, and stores serialized vectors in the database.
    2. **Search**: Converts user queries into vector embeddings, performs semantic searches over stored chunks, applies a **lexical keyword-boosting** algorithm, and filters top candidates.
    3. **Synthesize**: Sends the top-matching contexts to `gemini-2.5-flash` to generate bilingual answers citing source documents `[1]`, `[2]`, `[3]`.

---

## 3. Database Layer

The persistence layer uses a relational database optimized for fast queries, structured reports, and audit logging:

- **Engine**: MySQL (via standard PHP `mysqli` extensions)
- **Primary Schema (`database.sql`)**:
  - **`tbl_cawangan`**: Configures central or branch offices (Bintulu, Sibu, Miri, Kuching).
  - **`tbl_user`**: User credentials with role-based access control (Super Admin, Pusat, and Cawangan roles).
  - **`tbl_member`**: Personal data profiles for members (IC numbers, village origins, contact numbers, active status).
  - **`tbl_event`**: Event information, budgets, and workflow-based approvals.
  - **`tbl_document`**: Physical uploaded document metadata linked to events.
  - **`tbl_document_chunks`**: Stores raw text chunks and their serialized vector embeddings for the semantic search (RAG) feature.
  - **`tbl_transaction`**: Tracks financial entries (income/expense categories, payment modes, receipts, associated events).
  - **`tbl_attendance`**: Tracks program attendance logs for members.

---

## 4. Frontend & Presentation Layer

The user interface follows a modern web design structure optimized for dashboard navigation:

- **Styling**: Tailwind CSS (v3 loaded via CDN).
- **Design Language**: Custom-configured theme tailored around **MYDS (Malaysian Government Design System)** aesthetics (employing `#003366` as `kebana-blue` and `#FFCC00` as `kebana-yellow`).
- **Typography & Icons**:
  - **Google Fonts**: Inter (weights 400-800) for clean typography.
  - **Font Awesome 6**: Used for dashboard and navigation iconography.
- **UI Components**:
  - Custom sidebar navigation with interactive collapsible views and unread badges.
  - Dynamic topbar displaying current local system time and notification dropdowns.
  - Responsive layout with touch-friendly navigation overlays for mobile screens.
  - Real-time communication/chat widget overlay.
