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
        {{-- Run `npm run build` so PDF styles are available from the Vite manifest. --}}
        <style>
            body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; background: #f8fafc; padding: 36px 40px; }
        </style>
    @endif
</head>
<body class="bg-slate-50">
    <div class="mx-auto max-w-[210mm] px-10 py-9">
        {{-- Brand --}}
        <div class="mb-6 text-center">
            <img
                src="images/Pnpki.png"
                alt="PNPKI"
                class="mx-auto h-24 object-contain"
            >
        </div>

        {{-- Hero card --}}
        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600 px-6 py-5 text-center text-white">
                <h1 class="text-2xl font-bold tracking-tight">Submission Receipt</h1>
                <p class="mt-1 text-sm text-white/90">Employee Registration Form</p>
            </div>
        </div>

        {{-- Personal Information --}}
        <div class="mb-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-sky-50 px-4 py-3">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.12em] text-blue-700">Personal Information</h2>
            </div>
            <div class="p-0">
                <table class="w-full border-collapse">
                    <tr class="border-b border-slate-100">
                        <td class="w-1/4 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">First Name</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->firstname }}</div>
                        </td>
                        <td class="w-1/4 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Last Name</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->lastname }}</div>
                        </td>
                        <td class="w-1/4 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Middle Name</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->middlename }}</div>
                        </td>
                        <td class="w-1/4 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Suffix</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->suffix }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-3 align-top" colspan="4">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Gender</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $genderLabel }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Contact Information --}}
        <div class="mb-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-sky-50 px-4 py-3">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.12em] text-blue-700">Contact Information</h2>
            </div>
            <div class="p-0">
                <table class="w-full border-collapse">
                    <tr>
                        <td class="w-1/2 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Email Address</div>
                            <div class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $submission->email }}</div>
                        </td>
                        <td class="w-1/2 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Phone Number</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->phone_number }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Address --}}
        <div class="mb-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-sky-50 px-4 py-3">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.12em] text-blue-700">Address</h2>
            </div>
            <div class="p-0">
                <table class="w-full border-collapse">
                    <tr class="border-b border-slate-100">
                        <td class="w-1/4 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">House No.</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->address->house_no }}</div>
                        </td>
                        <td class="w-3/4 p-3 align-top" colspan="3">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Street</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->address->street }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Barangay</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $barangayName }}</div>
                        </td>
                        <td class="p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">City / Municipality</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $municipalityName }}</div>
                        </td>
                        <td class="p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Province</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $provinceName }}</div>
                        </td>
                        <td class="p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">ZIP Code</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->address->zip_code }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Employment Details --}}
        <div class="mb-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-sky-50 px-4 py-3">
                <h2 class="text-[10px] font-bold uppercase tracking-[0.12em] text-blue-700">Employment Details</h2>
            </div>
            <div class="p-0">
                <table class="w-full border-collapse">
                    <tr>
                        <td class="w-1/2 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Organizational Unit</div>
                            <div class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $submission->organizational_unit }}</div>
                        </td>
                        <td class="w-1/2 p-3 align-top">
                            <div class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">TIN Number</div>
                            <div class="mt-1 text-sm font-semibold text-slate-900">{{ $submission->tin_number }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Document Attachments --}}
        @if($submission->attachments && $submission->attachments->count() > 0)
            <div class="mb-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-sky-50 px-4 py-3">
                    <h2 class="text-[10px] font-bold uppercase tracking-[0.12em] text-blue-700">Document Attachments</h2>
                </div>
                <div class="p-0">
                    <table class="w-full border-collapse">
                        @foreach($submission->attachments as $attachment)
                            <tr @class(['border-b border-slate-100' => ! $loop->last])>
                                <td class="w-[28%] p-3 align-middle">
                                    <span class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">{{ $attachment->file_type }}</span>
                                </td>
                                <td class="p-3 align-middle">
                                    <span class="text-sm font-semibold text-slate-900">{{ $attachment->file_name }}</span>
                                </td>
                                <td class="w-[22%] p-3 text-right align-middle">
                                    <span class="inline-block rounded-full bg-sky-100 px-3 py-1 text-[9px] font-bold text-blue-700 ring-1 ring-sky-200/80">
                                        Submitted
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-8 border-t border-slate-200 pt-4 text-center text-[9px] leading-relaxed text-slate-500">
            Generated on {{ now()->format('F d, Y \a\t h:i A') }}<br>
            This is an official submission receipt. Please keep a copy for your records.
        </div>
    </div>
</body>
</html>
