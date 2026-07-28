<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-400">Quick fixes for the most common issues staff and employees run into.</p>
    </x-filament::section>

    <x-filament::section heading="Errors and blocked actions">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Problem</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Fix</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Public form link returns an error or stops accepting submissions</td>
                        <td class="py-2.5 align-top">The shareable form was probably replaced by a new one. Go to <strong class="font-medium text-gray-950 dark:text-white">Forms → Shareable Forms</strong>, confirm the active form, and share the current link.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">"Duplicate" error when an employee tries to submit</td>
                        <td class="py-2.5 align-top">Someone with the same first name and date of birth has already submitted for that office. The representative must review the existing record.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">CAPTCHA fails or the submit button does nothing</td>
                        <td class="py-2.5 align-top">Reload the page and complete the security check again. This can happen if the page was left open too long.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Cannot finalize a batch</td>
                        <td class="py-2.5 align-top">The batch must have at least one submission and none of them can be <x-filament::badge color="danger">Needs Revision</x-filament::badge>. Finalize or unflag all submissions first.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Cannot mark the batch For Submission</td>
                        <td class="py-2.5 align-top">Every submission must already be <x-filament::badge color="success">For Submission</x-filament::badge> with no active flags before the batch-level button appears.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Export CSV or Download Attachments buttons are missing</td>
                        <td class="py-2.5 align-top">These are only available when the batch application status is <x-filament::badge color="success">For Submission</x-filament::badge>. Approve the batch first.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Document and file issues">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Problem</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Fix</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top">PDF receipt download link is expired</td>
                        <td class="py-2.5 align-top">The signed link lasts about 5 minutes after submit. Ask the representative for your reference number — the record still exists in the system.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">PNPKI PDF autofill fields look wrong or empty</td>
                        <td class="py-2.5 align-top">Autofill from the uploaded PNPKI PDF is best-effort. Always review every personal and address field before moving to the next step.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">File upload fails or exceeds size limit</td>
                        <td class="py-2.5 align-top">All uploads must be PDF format, maximum 5 MB per file. Compress or re-scan the document if it is too large.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Account issues">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Problem</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Fix</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Forgot password</td>
                        <td class="py-2.5 align-top">Self-serve password reset is not available. Ask an Admin to update your account under <strong class="font-medium text-gray-950 dark:text-white">Users</strong>.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Cannot access the admin panel</td>
                        <td class="py-2.5 align-top">Only Admin and Representative accounts can log in. Ask an Admin to check your role and office assignment.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="In-app notifications">
        <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">
            The system sends database notifications (bell icon, top right). There are no emails.
            Check the bell regularly, especially after batch actions.
        </p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Event</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Who is notified</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top">New submission received (public or manual)</td>
                        <td class="py-2.5 align-top">Form owner / office representatives</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Batch finalized by representative</td>
                        <td class="py-2.5 align-top">All admins</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Modification requested by representative</td>
                        <td class="py-2.5 align-top">All admins</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top">Batch returned for revision by admin</td>
                        <td class="py-2.5 align-top">The batch's representative</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
