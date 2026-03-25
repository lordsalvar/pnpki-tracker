<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            padding: 36px 40px;
            background: #ffffff;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            padding-bottom: 18px;
            margin-bottom: 24px;
            border-bottom: 3px solid #1d4ed8;
        }
        .header h1 {
            font-size: 22px;
            font-weight: bold;
            color: #1d4ed8;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }
        .badge {
            display: inline-block;
            background: #dcfce7;
            color: #16a34a;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 14px;
            border-radius: 20px;
            margin-top: 10px;
            border: 1px solid #bbf7d0;
        }

        /* ── Section ── */
        .section {
            margin-bottom: 18px;
        }
        .section-title {
            font-size: 9.5px;
            font-weight: bold;
            color: #ffffff;
            background: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 6px 10px;
            margin-bottom: 0;
        }
        .section-body {
            border: 1px solid #e5e7eb;
            border-top: none;
        }

        /* ── Grid rows using table layout ── */
        table.grid {
            width: 100%;
            border-collapse: collapse;
        }
        table.grid td {
            width: 25%;
            padding: 8px 10px;
            vertical-align: top;
            border-bottom: 1px solid #f3f4f6;
            border-right: 1px solid #f3f4f6;
        }
        table.grid td:last-child {
            border-right: none;
        }
        table.grid tr:last-child td {
            border-bottom: none;
        }
        .label {
            font-size: 9px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 3px;
        }
        .value {
            font-size: 11.5px;
            font-weight: 600;
            color: #111827;
            word-break: break-word;
        }

        /* ── Attachments ── */
        table.attachments {
            width: 100%;
            border-collapse: collapse;
        }
        table.attachments td {
            padding: 7px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }
        table.attachments tr:last-child td {
            border-bottom: none;
        }
        .attachment-type {
            font-size: 9px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            width: 30%;
        }
        .attachment-name {
            font-size: 11px;
            font-weight: 600;
            color: #111827;
        }
        .submitted-tag {
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 10px;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            line-height: 1.6;
        }
        
    </style>
</head>
<body>
    <div style= "text-align: center; margin-bottom: 20px;">
        <img src="{{ public_path('images/Pnpki.png') }}" alt="PNPKI Logo" style="height: 100px;">

    {{-- Header --}}
    <div class="header">
        <h1>Submission Receipt</h1>
        <div class="subtitle">Employee Registration Form</div>
        <div class="badge">&#10003; Successfully Submitted</div>
    </div>

    {{-- Personal Information --}}
    <div class="section">
        <div class="section-title">Personal Information</div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <td>
                        <div class="label">First Name</div>
                        <div class="value">{{ $submission->firstname }}</div>
                    </td>
                    <td>
                        <div class="label">Last Name</div>
                        <div class="value">{{ $submission->lastname }}</div>
                    </td>
                    <td>
                        <div class="label">Middle Name</div>
                        <div class="value">{{ $submission->middlename }}</div>
                    </td>
                    <td>
                        <div class="label">Suffix</div>
                        <div class="value">{{ $submission->suffix }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">Gender</div>
                        <div class="value">{{ ucfirst($submission->gender instanceof \BackedEnum ? $submission->gender->value : $submission->gender) }}</div>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Contact Information --}}
    <div class="section">
        <div class="section-title">Contact Information</div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <td colspan="2">
                        <div class="label">Email Address</div>
                        <div class="value">{{ $submission->email }}</div>
                    </td>
                    <td colspan="2">
                        <div class="label">Phone Number</div>
                        <div class="value">{{ $submission->phone_number }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Address --}}
    <div class="section">
        <div class="section-title">Address</div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <td>
                        <div class="label">House No.</div>
                        <div class="value">{{ $submission->address->house_no }}</div>
                    </td>
                    <td colspan="3">
                        <div class="label">Street</div>
                        <div class="value">{{ $submission->address->street }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">Barangay</div>
                        <div class="value">{{ $barangayName }}</div>
                    </td>
                    <td>
                        <div class="label">City / Municipality</div>
                        <div class="value">{{ $municipalityName }}</div>
                    </td>
                    <td>
                        <div class="label">Province</div>
                        <div class="value">{{ $provinceName }}</div>
                    </td>
                    <td>
                        <div class="label">ZIP Code</div>
                        <div class="value">{{ $submission->address->zip_code }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Employment Details --}}
    <div class="section">
        <div class="section-title">Employment Details</div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <td colspan="2">
                        <div class="label">Organizational Unit</div>
                        <div class="value">{{ $submission->organizational_unit }}</div>
                    </td>
                    <td colspan="2">
                        <div class="label">TIN Number</div>
                        <div class="value">{{ $submission->tin_number }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Document Attachments --}}
    @if($submission->attachments && $submission->attachments->count() > 0)
    <div class="section">
        <div class="section-title">Document Attachments</div>
        <div class="section-body">
            <table class="attachments">
                @foreach($submission->attachments as $attachment)
                <tr>
                    <td class="attachment-type">{{ $attachment->file_type }}</td>
                    <td class="attachment-name">{{ $attachment->file_name }}</td>
                    <td style="text-align:right;"><span class="submitted-tag">Submitted</span></td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Generated on {{ now()->format('F d, Y \a\t h:i A') }}<br>
        This is an official submission receipt. Please keep a copy for your records.
    </div>

</body>
</html>