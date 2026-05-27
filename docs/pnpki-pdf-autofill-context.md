# PNPKI PDF Autofill — Implementation Context & Status

**Last updated:** May 2026  
**Status:** ⚠️ **Not working in production UI** — extraction logic tests pass against `RAMOS_PNPKI.pdf`, but the public registration wizard still does not autofill reliably for users.

This document captures what was built, what we learned, and what is still broken so a future session can continue without re-discovering the same context.

---

## Goal

Allow applicants on the **public employee registration wizard** (`/p/forms/{publicId}`) to:

1. Accept data privacy consent  
2. Select an **ID combination** (combo box)  
3. Upload the filled **PNPKI form PDF** (+ supporting ID PDFs per combo)  
4. Have the system **read the PNPKI PDF** and **pre-fill** later wizard steps (Personal, Address & contact, Employment) instead of manual entry  

Sample reference: `RAMOS_PNPKI.pdf` in the project root (DICT-PNPKI-FO-001 Revised 2025 fillable form).

---

## User flow (intended)

| Step | Wizard step | What happens |
|------|-------------|--------------|
| 1 | **Documents** | Privacy checkbox → ID combo → upload PNPKI + required ID PDFs |
| 2 | **Personal** | Name, sex, civil status, birth (pre-filled from PDF where possible) |
| 3 | **Address & contact** | PSGC address + email/phone (pre-filled) |
| 4 | **Employment** | Organization (office-prefilled), org unit, TIN |
| 5 | **Review & submit** | Turnstile CAPTCHA → submit |

After PNPKI PDF upload, a success notification should appear: *"PNPKI form read successfully"*, and later steps should show imported values with a review banner.

---

## What was implemented

### 1. Dependency

- **`smalot/pdfparser`** (`composer require smalot/pdfparser`) — reads PDF text and AcroForm widget values server-side.

### 2. Extraction service

**`app/Services/PnpkiFormExtractionService.php`**

- Reads AcroForm fields from the PDF via `Smalot\PdfParser\Parser`.
- **Primary path:** DICT-PNPKI-FO-001 field map (generic Adobe field names on the official form):

  | PDF field | Maps to |
  |-----------|---------|
  | `Text2` | `lastname` |
  | `Text3` | `firstname` |
  | `Text4` | `middlename` |
  | `Text5` | `suffix` |
  | `Text7` | `birth_date` |
  | `Text8` | `tin_number` |
  | `Text9` | `organization` |
  | `Text10` | `organizational_unit` |
  | `Text11` | `house_no` |
  | `Text12` | `barangay` |
  | `Text13` | `province` |
  | `Text14` | `phone_number` |
  | `Text15` | `street` |
  | `Text16` | `municipality` |
  | `Text17` | `zip_code` |
  | `Text18` | `email` |
  | `Check Box22` = Yes | `sex` = male |
  | `Check Box23` = Yes | `sex` = female |

- **Fallback path:** label matching in extracted PDF text (fragile; form labels are spaced oddly in text layer, e.g. `P N P K I I N D I V I D U A L...`).
- Normalizes TIN (9 digits), phone (`09xxxxxxxxx`), zip (4 digits), dates, suffix (`n/a` → `N/A`).
- **`toFormState()`** maps to wizard field names and resolves **PSGC** codes via `PsgcService` fuzzy name lookup.

**`app/DataTransferObjects/PnpkiExtractedData.php`** — DTO for extracted values.

### 3. PSGC lookup helpers

**`app/Services/PsgcService.php`** — added:

- `findProvinceCodeByName()`
- `findMunicipalityCodeByName()`
- `findBarangayCodeByName()`

Uses exact match first, then `similar_text` ≥ 85% for fuzzy match.

### 4. Public form UI changes

**`app/Filament/Public/Pages/PublicEmployeeForm.php`**

- Wizard reordered: **Documents** is step 1 (was: Personal first).
- Privacy + ID combo + uploads moved to Documents step.
- Turnstile moved to final **Review & submit** step.
- Property: `public bool $pnpkiAutofilled = false`.
- Livewire hook: `updatedEmployeeDataUploadPnpki()` → calls autofill when PNPKI file state changes.

**`app/Filament/Public/Pages/Concerns/InteractsWithPnpkiAutofill.php`**

- `documentsStepSchema()` — privacy, `id_combo`, all `FileUpload` fields.
- `autofillFromPnpkiPdf()` — resolves PDF path, extracts, merges into form state.
- `resolveUploadedPdfPath()` — handles string paths, arrays, temp files, `local` disk, `livewire-tmp`.
- `pnpkiAutofillBannerHtml()` — info banner on Personal / Address steps when autofill ran.
- Privacy copy updated to mention PDF reading for pre-fill.

### 5. Tests (passing)

| Test | What it proves |
|------|----------------|
| `tests/Unit/PnpkiFormExtractionServiceTest.php` | `toFormState()` mapping from DTO |
| `tests/Feature/PnpkiPdfExtractionTest.php` | Full extract from `RAMOS_PNPKI.pdf` (Ramos, Wendel Ray, TIN, email, etc.) |

CLI verification (during development) showed correct extraction from `RAMOS_PNPKI.pdf`:

- Last name: Ramos  
- First name: Wendel Ray  
- TIN: 723920009  
- Province code resolved (e.g. `1124` for Davao del Sur)  

---

## Bugs found & fixes attempted (UI still broken)

### Issue A — Wrong data from text parsing (fixed in tests, not verified in UI)

**Symptom:** Autofill appeared empty or filled with label text like `"Middle Name"`, `"First Name Middle Name"`.

**Cause:** Official PDF uses AcroForm names `Text2`–`Text18`, not semantic names. Original code matched **labels in PDF text**, not AcroForm values.

**Fix applied:** `extractFromDictPnpkiAcroForm()` with `DICT_PNPKI_ACRO_MAP` (see table above). Tests pass.

---

### Issue B — Validation errors shown as “could not read” (fix attempted, UI still broken)

**Symptom:** Notification: *"Could not read PNPKI form automatically. The Philippine National ID field is required. (and 20 more errors)"*

**Cause:** Autofill used `$this->form->getState()` and `$this->form->fill()`, which run **full wizard validation**. On step 1, other required uploads (National ID, etc.) and later-step fields are still empty → `ValidationException` → caught and displayed as extraction failure.

**Fix attempted:**

```php
// Before (validates entire form):
$current = $this->form->getState();
$this->form->fill(array_merge($current, $mapped));

// After (intended — no validation):
$current = $this->employeeData ?? [];
$this->employeeData = array_merge($current, $mapped);
$this->resetValidation();
```

Also:

- Removed `afterStateUpdated` on PNPKI `FileUpload` (duplicate trigger).
- Kept `updatedEmployeeDataUploadPnpki()` on `PublicEmployeeForm`.
- `ValidationException` caught silently in autofill (no misleading notification).

**Current status:** User reports **it still does not work** after this change. Possible remaining causes (not investigated in last session):

- `updatedEmployeeDataUploadPnpki` not firing or firing before file is on disk.
- `resolveUploadedPdfPath()` returning `null` for Filament/Livewire upload state shape in browser.
- `$this->employeeData` merge not syncing back to Filament Schema / wizard fields (state path binding).
- Wizard step not re-rendering filled values until navigation or refresh.
- Double property path: form uses `statePath('employeeData')` but Schema may need explicit `fill()` without validation API.
- Livewire temporary upload vs final `attachments/` path timing.

---

## Key files (quick index)

| Path | Role |
|------|------|
| `app/Services/PnpkiFormExtractionService.php` | PDF → DTO → form state |
| `app/DataTransferObjects/PnpkiExtractedData.php` | Extraction result |
| `app/Services/PsgcService.php` | Province/municipality/barangay code lookup |
| `app/Filament/Public/Pages/PublicEmployeeForm.php` | Public wizard page |
| `app/Filament/Public/Pages/Concerns/InteractsWithPnpkiAutofill.php` | Documents step + autofill hooks |
| `RAMOS_PNPKI.pdf` | Sample fillable PNPKI form for testing |
| `tests/Feature/PnpkiPdfExtractionTest.php` | Feature test against sample PDF |

---

## Fields NOT on PNPKI individual form (always manual)

- `civil_status`
- `birth_place_country`
- `birth_place_province`
- `maiden_name` (when applicable)

Autofill notification may list these as “please complete”.

---

## Scanned PDFs

Image-only / scanned PDFs **without a text layer or AcroForm values** will fail with:

> No readable text was found in this PDF…

OCR was **not** implemented.

---

## Suggested next debugging steps (when resuming)

1. **Log in `autofillFromPnpkiPdf()`** (temporary): upload state type, resolved path, `file_exists`, extracted field count, merged keys — do not log full PDF content.
2. **Confirm hook fires:** log inside `updatedEmployeeDataUploadPnpki`.
3. **If path is null:** dump Livewire `employeeData['upload_pnpki']` shape after upload in browser (array vs string vs object).
4. **If extraction works but UI empty:** try Filament v5 API for filling without validation (search docs: `fill`, `statePath`, `getStateSnapshot`) or dispatch `$refresh` after merge.
5. **Optional UX:** explicit **“Read PNPKI form”** button after upload instead of automatic hook — easier to debug and avoids race with other required fields.
6. **Browser test** with `RAMOS_PNPKI.pdf` on Herd URL `/p/forms/{publicId}`.

---

## Privacy / compliance note

- Consent checkbox mentions local PDF reading for pre-fill.
- Consent is still `dehydrated(false)` — not stored in DB (pre-existing pattern).
- Processing is server-side only; no third-party OCR API in current code.

---

## Summary

| Layer | Status |
|-------|--------|
| PDF parsing (`RAMOS_PNPKI.pdf`) | ✅ Works in PHP tests / CLI |
| DICT AcroForm field mapping | ✅ Implemented |
| PSGC code resolution | ✅ Partial (province often works; barangay fuzzy match may fail) |
| Public wizard autofill UI | ❌ **Still not working for user** |
| Validation vs fill race | ⚠️ Addressed in code; user says no improvement |

Do **not** assume autofill works in the UI until verified end-to-end on the public form with a real upload.
