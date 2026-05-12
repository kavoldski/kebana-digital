<skill>
<name>Security Architect</name>
<description>Ensure that every application is "Forensic-Grade" in its security, handling multiple user groups and sensitive data with zero leakage.</description>
<instructions>
# 🛡️ Generic Security & IAM Architect Protocol (v1.0)

## Objective
Ensure that every application is "Forensic-Grade" in its security, handling multiple user groups and sensitive data with zero leakage.

## 🎯 Primary Objectives
1.  **RBAC Enforcement**: Implement strict Role-Based Access Control using the system's middleware/edge logic.
2.  **Route Guarding**: Ensure that internal routes (Admin/Staff) are physically and logically inaccessible to unauthorized roles.
3.  **Data Scoping**: Standardize patterns for database-level security and server-side filtering.
4.  **Audit Readiness**: Implement patterns for logging sensitive actions.

## 🛠 Technical Standards

### 1. The Route Grouping Convention
Organize the application structure into protected role groups:
- **Admin**: High-level system settings and user management.
- **App/Staff**: The primary workspace for standard users.
- **Portal/Client**: The customer-facing dashboard.
- **Auth**: Publicly accessible login/register/reset flows.

### 2. Edge Logic/Middleware Branching
The middleware is your primary weapon. It must:
- Detect the user's role from the session/token.
- Match the path against the user's role permissions.
- Redirect unauthorized attempts to the appropriate page.

### 3. Server-Side Protection
Never trust the client.
- **Server Actions/APIs**: Every server-side entry point must re-validate the session and user role before executing.
- **Wrappers**: Implement reusable role-validation wrappers for all handlers.

## 🎨 Aesthetic of Security
Security should feel "Premium" and "Reassuring":
- **Stateful Feedback**: Show clear "Access Denied" or "Verification Pending" states that match the product's archetype.
- **Trust Elements**: Use appropriate security iconography to denote protected areas.

## 🚫 Anti-Patterns (Failure States)
- Hardcoding user IDs or roles in the UI.
- Relying on client-side logic for route protection.
- Allowing public access to internal API routes without session validation.
- Missing custom error pages for restricted zones.
</instructions>
</skill>
