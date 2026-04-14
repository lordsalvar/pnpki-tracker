<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ config('app.name') }} — {{ $submission->firstname }} {{ $submission->lastname }}</title>
@php
    $pdfStyles = rescue(
        static fn (): string => \Illuminate\Support\Facades\Vite::content('resources/css/pdf/submission-receipt.css'),
        ''
    );
@endphp
@if($pdfStyles !== '')
    <style>{!! $pdfStyles !!}</style>
@else
<style>
  /* Dompdf: no CSS Grid; remote fonts off by default. Use tables + DejaVu. */
  * { margin: 0; padding: 0; box-sizing: border-box; }

  /* Reserve bottom margin on every page for the fixed footer */
  @page {
    margin: 40px 20px 80px 20px;
  }

  body {
    font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    background: #f0f2f7;
    padding: 40px 20px 0;
    color: #0f1724;
  }

  .page {
    max-width: 720px;
    margin: 0 auto;
  }

  /* Header */
  .header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 32px;
    gap: 14px;
  }

  .logo-ring {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #ffffff;
    border: 1.5px solid #dde2ee;
    display: block;
    margin: 0 auto;
    overflow: hidden;
  }

  .header-app-name {
    font-size: 22px;
    font-weight: 600;
    color: #0f1724;
    text-align: center;
    letter-spacing: -0.02em;
    line-height: 1.25;
  }

  /* Filament AdminPanel brand: from-sky-400 via-blue-500 to-indigo-500 */
  .brand-gradient {
    font-weight: 900;
    letter-spacing: -0.02em;
    background: linear-gradient(to right, #38bdf8, #3b82f6, #6366f1);
    -webkit-background-clip: text;
    background-clip: text;
    color: #2563eb;
  }

  @supports ((-webkit-background-clip: text) or (background-clip: text)) {
    .brand-gradient {
      color: transparent;
    }
  }

  .header-text p {
    font-size: 13px;
    color: #6b7898;
    text-align: center;
    margin-top: 4px;
  }

  /* Receipt ID banner (table layout for Dompdf) */
  .receipt-banner-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #1a3a6e;
    border-radius: 14px;
    margin-bottom: 20px;
  }

  .receipt-banner-table td {
    vertical-align: middle;
    padding: 18px 24px;
  }

  .receipt-banner-table .label {
    font-size: 11px;
    font-weight: 500;
    color: #7fa3d4;
    letter-spacing: 0.1em;
    text-transform: uppercase;
  }

  .receipt-banner-table .value {
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    font-size: 15px;
    font-weight: 500;
    color: #e8f0fb;
    margin-top: 4px;
  }

  /* Section card */
  .section {
    background: #fff;
    border: 1px solid #e2e7f3;
    border-radius: 16px;
    margin-bottom: 16px;
    overflow: hidden;
  }

  .section-header {
    padding: 13px 20px;
    border-bottom: 1px solid #eef1f8;
  }

  .section-header-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
  }

  .section-header-inner .section-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
    padding: 0;
  }

  .section-header-inner .section-title-wrap {
    flex: 1;
    display: flex;
    align-items: center;
  }

  .section-title-wrap .section-title {
    display: inline-block;
  }

  .section-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #eef3fb;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1a3a6e;
  }

  .section-icon svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
  }

  .section-title {
    font-size: 11px;
    font-weight: 600;
    color: #1a3a6e;
    letter-spacing: 0.09em;
    text-transform: uppercase;
  }

  /* Fields: tables instead of grid (Dompdf) */
  .fields-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .fields-table td.field {
    padding: 16px 20px;
    border-right: 1px solid #eef1f8;
    border-bottom: 1px solid #eef1f8;
    vertical-align: top;
    width: 25%;
  }

  .fields-table.cols-2 td.field {
    width: 50%;
  }

  .fields-table td.field.no-border-right,
  .fields-table td.field:last-child {
    border-right: none;
  }

  .fields-table tr:last-child td.field {
    border-bottom: none;
  }

  .field-label {
    font-size: 10px;
    font-weight: 600;
    color: #9aa4be;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    margin-bottom: 5px;
  }

  .field-value {
    font-size: 14px;
    font-weight: 500;
    color: #0f1724;
    line-height: 1.35;
  }

  .field-value.mono {
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    font-size: 13px;
  }

  .fields-table td.field.full-row {
    border-right: none;
    width: auto;
  }

  /* Attachments (table row layout for Dompdf) */
  .attachment-table {
    width: 100%;
    border-collapse: collapse;
  }

  .attachment-table td {
    padding: 14px 20px;
    border-bottom: 1px solid #eef1f8;
    vertical-align: middle;
  }

  .attachment-table tr:last-child td {
    border-bottom: none;
  }

  .attachment-table .file-name {
    width: 99%;
  }

  .file-type-badge {
    font-size: 10px;
    font-weight: 600;
    color: #1a3a6e;
    background: #eef3fb;
    padding: 4px 10px;
    border-radius: 6px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .file-name {
    font-size: 13px;
    font-weight: 500;
    color: #0f1724;
  }

  /* Footer — fixed so Dompdf stamps it on every page */
  .footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    text-align: center;
    padding: 10px 0 6px;
    border-top: 2px dashed #b6c3e0;
    background: #f7faff;
    font-size: 12px;
    width: 100%;
  }

  .footer-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 0 10px;
  }

  .footer p {
    font-size: 11px;
    color: #7fa3d4;
    line-height: 1.5;
    margin: 0;
  }

  .footer strong {
    color: #1a3a6e;
    font-weight: 600;
    letter-spacing: 0.01em;
  }

  .footer-logo {
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    font-size: 10px;
    color: #b6c3e0;
    margin-bottom: 2px;
    letter-spacing: 0.12em;
  }

  /* Page break for Dompdf */
  .page-break {
    page-break-after: always;
    height: 0;
    margin: 0;
    border: none;
  }

  /* Address rows (tables for Dompdf) */
  .addr-grid-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    border-bottom: 1px solid #eef1f8;
  }

  .addr-grid-table td.field {
    padding: 16px 20px;
    border-right: 1px solid #eef1f8;
    vertical-align: top;
  }

  .addr-grid-table td.field:first-child {
    width: 22%;
  }

  .addr-grid-table td.field:last-child {
    border-right: none;
  }

  .addr-grid-2-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .addr-grid-2-table td.field {
    padding: 16px 20px;
    border-right: 1px solid #eef1f8;
    vertical-align: top;
    width: 25%;
  }

  .addr-grid-2-table td.field:last-child {
    border-right: none;
  }

  .addr-grid-2-table tr:last-child td.field {
    border-bottom: none;
  }
</style>
@endif
</head>
<body>

  {{-- Single fixed footer — Dompdf stamps this on every page automatically --}}
  <div class="footer">
    <div class="footer-content">
      <div class="footer-logo">PNPKI</div>
      <p>Generated on <strong>{{ now('Asia/Manila')->format('F d, Y \a\t h:i A') }}</strong></p>
      <p>This is an official submission receipt. Please keep a copy for your records.</p>
    </div>
  </div>

  <!-- PAGE 1 -->
  <div class="page">
    <!-- Header -->
    <div class="header">
      <div class="logo-ring">
        @if(($receiptLogoSrc ?? '') !== '')
          <img src="{{ $receiptLogoSrc }}"
               style="width:100%; height:100%; object-fit:cover;"
               alt="" />
        @endif
      </div>
      <div class="header-text">
        <h1 class="header-app-name">
          @php
              $appName = (string) config('app.name');
              $brandToken = 'PNPKI-TRACKER';
          @endphp
          @if(str_contains($appName, $brandToken))
            {{ Str::before($appName, $brandToken) }}<span class="brand-gradient">{{ $brandToken }}</span>{{ Str::after($appName, $brandToken) }}
          @else
            <span class="brand-gradient">{{ $appName }}</span>
          @endif
        </h1>
        <p>Employee Registration Form</p>
      </div>
    </div>
    <!-- Receipt Banner -->
    <table class="receipt-banner-table">
      <tr>
        <td>
          <div class="label">Reference No.</div>
          <div class="value">{{ $submission->reference_number ?? 'N/A' }}</div>
        </td>
        <td style="text-align: center;">
          <div class="label">Submitted</div>
          <div class="value">{{ $submission->created_at->setTimezone('Asia/Manila')->format('F j, Y \a\t g:i A') }}</div>
        </td>
      </tr>
    </table>
    <!-- Personal Information -->
    <div class="section">
      <div class="section-header">
        <div class="section-header-inner">
          <div class="section-icon">
            <x-heroicon-o-user aria-hidden="true" />
          </div>
          <div class="section-title-wrap">
            <span class="section-title">Personal Information</span>
          </div>
        </div>
      </div>
      <table class="fields-table">
        <tr>
          <td class="field">
            <div class="field-label">First Name</div>
            <div class="field-value">{{ $submission->firstname }}</div>
          </td>
          <td class="field">
            <div class="field-label">Last Name</div>
            <div class="field-value">{{ $submission->lastname }}</div>
          </td>
          <td class="field">
            <div class="field-label">Middle Name</div>
            <div class="field-value">{{ $submission->middlename }}</div>
          </td>
          <td class="field no-border-right">
            <div class="field-label">Suffix</div>
            <div class="field-value">{{ $submission->suffix ?: '—' }}</div>
          </td>
        </tr>
        @if(filled($submission->maiden_name) && $submission->maiden_name !== 'N/A')
        <tr>
          <td class="field full-row" colspan="4">
            <div class="field-label">Maiden Name</div>
            <div class="field-value">{{ $submission->maiden_name }}</div>
          </td>
        </tr>
        @endif
        <tr>
          <td class="field" colspan="2">
            <div class="field-label">Sex</div>
            <div class="field-value">{{ $sexLabel }}</div>
          </td>
          <td class="field no-border-right" colspan="2">
            <div class="field-label">Civil Status</div>
            <div class="field-value">{{ $submission->civil_status?->getLabel() ?? '—' }}</div>
          </td>
        </tr>
        <tr>
          <td class="field" colspan="2">
            <div class="field-label">Date of Birth</div>
            <div class="field-value">{{ $submission->birth_date?->format('F j, Y') ?? '—' }}</div>
          </td>
          <td class="field no-border-right" colspan="2">
            <div class="field-label">Country of Birth</div>
            <div class="field-value">{{ $submission->birth_place_country ?: '—' }}</div>
          </td>
        </tr>
        <tr>
          <td class="field full-row" colspan="4">
            <div class="field-label">Province / State of Birth</div>
            <div class="field-value">{{ $submission->birth_place_province ?: '—' }}</div>
          </td>
        </tr>
      </table>
    </div>
    <!-- Contact Information -->
    <div class="section">
      <div class="section-header">
        <div class="section-header-inner">
          <div class="section-icon">
            <x-heroicon-o-envelope aria-hidden="true" />
          </div>
          <div class="section-title-wrap">
            <span class="section-title">Contact Information</span>
          </div>
        </div>
      </div>
      <table class="fields-table cols-2">
        <tr>
          <td class="field">
            <div class="field-label">Email Address</div>
            <div class="field-value">{{ $submission->email }}</div>
          </td>
          <td class="field no-border-right">
            <div class="field-label">Phone Number</div>
            <div class="field-value mono">{{ $submission->phone_number }}</div>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="page-break"></div>

  <!-- PAGE 2 -->
  <div class="page">
    <!-- Address -->
    <div class="section">
      <div class="section-header">
        <div class="section-header-inner">
          <div class="section-icon">
            <x-heroicon-o-map-pin aria-hidden="true" />
          </div>
          <div class="section-title-wrap">
            <span class="section-title">Address</span>
          </div>
        </div>
      </div>
      <table class="addr-grid-table">
        <tr>
          <td class="field">
            <div class="field-label">House No.</div>
            <div class="field-value mono">{{ $submission->address->house_no }}</div>
          </td>
          <td class="field">
            <div class="field-label">Street</div>
            <div class="field-value">{{ $submission->address->street }}</div>
          </td>
        </tr>
      </table>
      <table class="addr-grid-2-table">
        <tr>
          <td class="field">
            <div class="field-label">Barangay</div>
            <div class="field-value">{{ $barangayName }}</div>
          </td>
          <td class="field">
            <div class="field-label">City / Municipality</div>
            <div class="field-value">{{ $municipalityName }}</div>
          </td>
          <td class="field">
            <div class="field-label">Province</div>
            <div class="field-value">{{ $provinceName }}</div>
          </td>
          <td class="field">
            <div class="field-label">ZIP Code</div>
            <div class="field-value mono">{{ $submission->address->zip_code }}</div>
          </td>
        </tr>
      </table>
    </div>
    <!-- Employment Details -->
    <div class="section">
      <div class="section-header">
        <div class="section-header-inner">
          <div class="section-icon">
            <x-heroicon-o-briefcase aria-hidden="true" />
          </div>
          <div class="section-title-wrap">
            <span class="section-title">Employment Details</span>
          </div>
        </div>
      </div>
      <table class="fields-table cols-2">
        <tr>
          <td class="field full-row" colspan="2">
            <div class="field-label">Organization</div>
            <div class="field-value">{{ $submission->organization ?: '—' }}</div>
          </td>
        </tr>
        <tr>
          <td class="field">
            <div class="field-label">Organizational Unit</div>
            <div class="field-value">{{ $submission->organizational_unit }}</div>
          </td>
          <td class="field no-border-right">
            <div class="field-label">TIN Number</div>
            <div class="field-value mono">{{ $submission->tin_number }}</div>
          </td>
        </tr>
      </table>
    </div>
    <!-- Document Attachments -->
    @if($submission->attachments && $submission->attachments->count() > 0)
      <div class="section">
        <div class="section-header">
          <div class="section-header-inner">
            <div class="section-icon">
              <x-heroicon-o-document-arrow-up aria-hidden="true" />
            </div>
            <div class="section-title-wrap">
              <span class="section-title">Document Attachments</span>
            </div>
          </div>
        </div>
        <table class="attachment-table">
          @foreach($submission->attachments as $attachment)
            <tr>
              <td style="width: 1%; white-space: nowrap;">
                <span class="file-type-badge">{{ $attachment->file_type }}</span>
              </td>
              <td class="file-name">{{ $attachment->file_name }}</td>
            </tr>
          @endforeach
        </table>
      </div>
    @endif
  </div>

</body>
</html>