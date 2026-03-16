## User Flows – Overview

### 1. Representative: Create shareable employee form

- Representative logs into the **admin Filament panel**.
- Opens **FormResource** and clicks “Create form”.
- System automatically sets `user_id = auth()->id()` and generates a unique `public_id` (UUID) for the form.
- Representative fills in basic form details (name, description, etc.) and saves.
- After saving, the system displays a read‑only **public URL** like `/p/forms/{public_id}` that the representative can copy and share.

### 2. Representative: Share the public form link

- Representative copies the public URL from the Form detail page.
- Representative shares the link with non‑user employees via email, chat, printed QR code, etc.
- The shared URL points to the **public Filament panel**, which does not require authentication.

### 3. Non‑user employee: Open public form

- Non‑user employee clicks the public link `/p/forms/{public_id}`.
- Request is routed to the **public Filament panel** (guest panel with no auth middleware).
- The `PublicEmployeeForm` page loads the `Form` by `public_id`.
- Page renders a clean, public Filament form with fields for employee data (e.g., name, contact information, address, gender, TIN, etc.).
- The employee sees only the public form UI, not the admin panel navigation.

### 4. Non‑user employee: Submit employee data

- Non‑user employee fills in required fields and submits the form.
- Filament validates the input on the server side.
- On successful validation:
  - System finds the representative via `form.user`.
  - System reads the representative’s `office_id`.
  - System creates an `Address` record using the submitted address fields.
  - System creates an `Employee` record with:
    - all submitted employee fields,
    - `address_id` set to the newly created address,
    - `office_id` set to the representative’s `office_id` (auto‑assignment based on who owns the form).
- The form clears or shows a **success / thank‑you** message to the employee.

