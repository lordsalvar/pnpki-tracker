<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm font-medium text-gray-950 dark:text-white">PNPKI Submission Tracker</p>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            This system tracks employee applications for Philippine National Public Key Infrastructure (PNPKI) digital certificates —
            from public registration through office review to DICT-ready export.
            Use the sub-pages in the Help menu for role-specific guides.
        </p>
    </x-filament::section>

    <x-filament::section heading="Who uses this system">
        <dl class="space-y-4 text-sm">
            <div>
                <dt class="font-medium text-gray-950 dark:text-white">Employee / applicant</dt>
                <dd class="mt-1 text-gray-600 dark:text-gray-400">
                    Fills out a public form via a shared link. No account needed. Uploads the PNPKI form and supporting IDs.
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-950 dark:text-white">Representative</dt>
                <dd class="mt-1 text-gray-600 dark:text-gray-400">
                    Manages one office's shareable form, reviews and finalizes submissions, groups them into batches, and sends to admin.
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-950 dark:text-white">Administrator</dt>
                <dd class="mt-1 text-gray-600 dark:text-gray-400">
                    Reviews finalized batches, returns for revision or approves, marks for DICT submission, and exports files.
                </dd>
            </div>
        </dl>
    </x-filament::section>

    <x-filament::section heading="How the process works" description="From registration to DICT export — the six main steps.">
        <ol class="list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Representative</strong> publishes an active Shareable Form and shares the public link with employees.
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Employees</strong> fill in the online registration wizard — personal info, address, employment, and PDF document uploads.
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Representative</strong> opens each pending submission, checks the data, makes corrections if needed, and clicks <strong class="font-medium text-gray-950 dark:text-white">Finalize</strong>.
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Representative</strong> creates a Batch, assigns finalized submissions to it, and clicks <strong class="font-medium text-gray-950 dark:text-white">Finalize Batch</strong> to send for admin review.
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Admin</strong> reviews each submission, marks them <strong class="font-medium text-gray-950 dark:text-white">For Submission</strong>, then marks the batch <strong class="font-medium text-gray-950 dark:text-white">For Submission</strong> and <strong class="font-medium text-gray-950 dark:text-white">Approves</strong> it.
            </li>
            <li>
                <strong class="font-medium text-gray-950 dark:text-white">Admin</strong> clicks <strong class="font-medium text-gray-950 dark:text-white">Export CSV</strong> and <strong class="font-medium text-gray-950 dark:text-white">Download Attachments</strong> to create the DICT submission package.
            </li>
        </ol>

        @if (auth()->user()?->role === \App\Enums\UserRole::ADMIN->value)
            <x-filament::callout
                class="mt-4"
                color="gray"
                icon="heroicon-o-information-circle"
                description="If corrections are needed at step 5, the admin can return the batch. See Statuses & revisions in the menu for revision loops."
            />
        @endif
    </x-filament::section>

    <x-filament::section heading="Help pages in this section">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Page</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Who should read it</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Representatives</td>
                        <td class="py-2.5 align-top">How to publish forms, review submissions, and finalize batches</td>
                    </tr>
                    @if (auth()->user()?->role === \App\Enums\UserRole::ADMIN->value)
                        <tr>
                            <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Administrators</td>
                            <td class="py-2.5 align-top">How to review, approve, flag, and export</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Employees</td>
                            <td class="py-2.5 align-top">Registration wizard steps, ID combinations, what happens after submit</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Statuses &amp; revisions</td>
                            <td class="py-2.5 align-top">Every status value explained; revision loop diagrams</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Troubleshooting</td>
                            <td class="py-2.5 align-top">Common problems and their fixes</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
