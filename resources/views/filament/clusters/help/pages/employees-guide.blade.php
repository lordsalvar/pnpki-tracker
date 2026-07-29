<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            This page is a reference for staff who need to explain the registration process to employees.
            Employees do <strong class="font-medium text-gray-950 dark:text-white">not</strong> log into the admin panel — they use only the public link shared by their office representative.
        </p>

        <x-filament::callout
            class="mt-4"
            color="gray"
            icon="heroicon-o-link"
        >
            <x-slot name="footer">
                Public link format:
                <code class="font-mono text-xs text-gray-800 dark:text-gray-200">/p/forms/{public-id}</code>
                — get this from your office's Shareable Form.
            </x-slot>
        </x-filament::callout>
    </x-filament::section>

    <x-filament::section heading="Registration wizard — 5 steps">
        <ol class="list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Documents</strong> — accept the privacy notice, choose an ID combination, then upload the PNPKI form PDF and the required supporting IDs (all PDF, max 5 MB each).
                If an ID has a back side, include both pages in the same PDF.
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Personal</strong> — first name, last name, middle name, suffix, sex, civil status, date of birth, country and province of birth.
                Female + married employees must also enter their maiden name.
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Address &amp; contact</strong> — house number, street, province, city/municipality, barangay (use the dropdowns), ZIP code, email address, and Philippine mobile number
                (e.g. <code class="font-mono text-xs text-gray-800 dark:text-gray-200">09171234567</code>).
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Employment</strong> — organization is pre-filled and cannot be changed.
                Enter the organizational unit (department/division) and TIN (9 digits, must be unique).
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Review &amp; submit</strong> — check everything, complete the CAPTCHA security check, then click <strong class="font-medium text-gray-950 dark:text-white">Submit registration</strong>.
            </li>
        </ol>
    </x-filament::section>

    <x-filament::section heading="Accepted ID combinations">
        <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">Choose one combination. The form will show exactly which files to upload.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Choice</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Files to upload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top">PhilID only</td>
                        <td class="py-2.5 align-top">PNPKI form + Philippine National ID</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Passport only</td>
                        <td class="py-2.5 align-top">PNPKI form + Passport (bio-data page)</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">UMID only</td>
                        <td class="py-2.5 align-top">PNPKI form + SSS UMID</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Driver's License only</td>
                        <td class="py-2.5 align-top">PNPKI form + LTO Driver's License</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">PRC only</td>
                        <td class="py-2.5 align-top">PNPKI form + PRC ID</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Postal ID only</td>
                        <td class="py-2.5 align-top">PNPKI form + Postal ID</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Birth Cert + 2 Valid IDs</td>
                        <td class="py-2.5 align-top">PNPKI form + Birth Certificate + Valid ID #1 + Valid ID #2</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Passport + 2 Valid IDs</td>
                        <td class="py-2.5 align-top">PNPKI form + Passport + Valid ID #1 + Valid ID #2</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="After submitting">
        <ul class="list-disc space-y-2 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>A confirmation screen appears with a <strong class="font-medium text-gray-950 dark:text-white">reference number</strong> (e.g. <code class="font-mono text-xs text-gray-800 dark:text-gray-200">PNPKI-2026-0000001</code>). Keep it.</li>
            <li>A <strong class="font-medium text-gray-950 dark:text-white">PDF receipt</strong> download link is shown — it expires in about 5 minutes, so save it immediately.</li>
            <li>The office representative is notified and will review the application.</li>
        </ul>

        <x-filament::callout
            class="mt-4"
            color="warning"
            icon="heroicon-o-exclamation-triangle"
            description="Submitting twice with the same first name and date of birth for the same office is blocked. Contact your representative if you need to correct a submission."
        />
    </x-filament::section>
</x-filament-panels::page>
