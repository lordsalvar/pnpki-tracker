<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submission Receipt — {{ $submission->firstname }} {{ $submission->lastname }}</title>
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

  body {
    font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
    background: #f0f2f7;
    padding: 40px 20px 60px;
    color: #0f1724;
    min-height: 100vh;
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
    background: #fff;
    border: 1.5px solid #dde2ee;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    font-size: 13px;
    font-weight: 500;
    color: #1a3a6e;
    letter-spacing: 0.04em;
  }

  .header-text h1 {
    font-size: 22px;
    font-weight: 600;
    color: #0f1724;
    text-align: center;
    letter-spacing: -0.02em;
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

  .receipt-banner-table .status-pill {
    display: inline-block;
    background: #2ee89a22;
    border: 1px solid #2ee89a55;
    color: #2ee89a;
    padding: 6px 14px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.04em;
    white-space: nowrap;
  }

  .receipt-banner-table .cell-status {
    text-align: right;
    width: 1%;
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
    display: table;
    width: 100%;
  }

  .section-header-inner .section-icon,
  .section-header-inner .section-title-wrap {
    display: table-cell;
    vertical-align: middle;
  }

  .section-header-inner .section-icon {
    width: 38px;
    padding-right: 10px;
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

  .submitted-badge {
    font-size: 10px;
    font-weight: 600;
    color: #1a7a52;
    background: #e6f7f0;
    border: 1px solid #b3e8d0;
    padding: 4px 12px;
    border-radius: 100px;
    white-space: nowrap;
    letter-spacing: 0.03em;
  }

  /* Footer */
  .footer {
    text-align: center;
    padding: 28px 0 0;
    border-top: 1px solid #e2e7f3;
    margin-top: 8px;
  }

  .footer p {
    font-size: 11px;
    color: #9aa4be;
    line-height: 1.8;
  }

  .footer strong {
    color: #6b7898;
    font-weight: 500;
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
<div class="page">

  {{-- Header --}}
  <div class="header">
    <div class="logo-ring">PNPKI</div>
    <div class="header-text">
      <h1>Submission Receipt</h1>
      <p>Employee Registration Form</p>
    </div>
  </div>

  {{-- Receipt Banner --}}
  <table class="receipt-banner-table">
    <tr>
      <td>
        <div class="label">Reference No.</div>
        <div class="value">{{ $submission->reference_number ?? 'N/A' }}</div>
      </td>
      <td style="text-align: center;">
        <div class="label">Submitted</div>
        <div class="value">{{ $submission->created_at->format('F j, Y \a\t g:i A') }}</div>
      </td>
      <td class="cell-status">
        <span class="status-pill">Received</span>
      </td>
    </tr>
  </table>

  {{-- Personal Information --}}
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
      <tr>
        <td class="field full-row" colspan="4">
          <div class="field-label">Maiden Name</div>
          <div class="field-value">{{ $submission->maiden_name ?: '—' }}</div>
        </td>
      </tr>
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

  {{-- Contact Information --}}
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

  {{-- Address --}}
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

  {{-- Employment Details --}}
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

  {{-- Document Attachments --}}
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
            <td style="width: 1%; white-space: nowrap; text-align: right;">
              <span class="submitted-badge">Submitted</span>
            </td>
          </tr>
        @endforeach
      </table>
    </div>
  @endif

  {{-- Footer --}}
  <div class="footer">
    <p>Generated on <strong>{{ now()->format('F d, Y \a\t h:i A') }}</strong></p>
    <p>This is an official submission receipt. Please keep a copy for your records.</p>
  </div>

</div>
</body>
</html>