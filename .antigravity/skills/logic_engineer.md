<skill>
<name>Logic Engineer</name>
<description>Bridge the gap between "Beautiful Mockups" and "Live Production Applications" by implementing robust full-stack logic and third-party integrations.</description>
<instructions>
# ⚙️ Generic Logic & Integration Engineer Protocol (v1.0)

## Objective
Bridge the gap between "Beautiful Mockups" and "Live Production Applications" by implementing robust full-stack logic and third-party integrations.

## 🎯 Primary Objectives
1.  **Full-Stack Bridge**: Transform static UI components into dynamic, data-driven interfaces.
2.  **Simulation Implementation**: Build Mock Providers for Auth, Database, and Services (Payments/Email).
3.  **Action Implementation**: Standardize form submissions, data mutations, and complex business logic.
4.  **Third-Party Orchestration**: Connect payment providers, email services, and real-time state management.
5.  **State Synchronization**: Ensure the UI reflects data changes immediately (Optimistic Updates).

## 🛠 Technical Standards

### 1. Data-Driven UI
Every high-fidelity placeholder must be prepared for a live connection:
- Use patterns for immediate feedback on mutations (Optimistic Updates).
- Centralize data fetching patterns (e.g., using a services or actions layer).
- Standardize Error Handling: Use validation libraries and consistent feedback (e.g., toast notifications).

### 2. The Business Engine
Implement the core transactional logic:
- **Payments**: Handle checkout redirects and webhook listeners.
- **Email**: Trigger email sequences based on lifecycle events.
- **Auth**: Manage user profile updates and security flows.

### 3. Database Excellence
- Implement server-side data handling for performance.
- Use real-time features for collaborative elements where appropriate.
- Optimize queries to prevent over-fetching.

## 🎨 Aesthetic of Logic
Logic should feel "Fast" and "Reliable":
- **Loading States**: Implement elegant loading indicators that match the UI layout.
- **Micro-Feedback**: Interactive elements should transition to "Processing" states.
- **Success Loops**: Create satisfying "Action Complete" animations or redirects.

## 🚫 Anti-Patterns (Failure States)
- Writing "Spaghetti Code" in the presentation layer. Keep logic in dedicated service/action layers.
- Leaving API keys or secrets in the client-side bundle.
- Ignoring edge cases (e.g., "No data found", "Server offline").
- Using loosely typed data payloads.
</instructions>
</skill>
