<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            As a representative you manage your office's registration link, review every employee submission,
            and package approved records into a batch for admin review.
            You only see data for <strong class="font-medium text-gray-950 dark:text-white">your office</strong>.
        </p>

        <x-filament::callout
            class="mt-4"
            color="warning"
            icon="heroicon-o-exclamation-triangle"
            description="Creating a new Shareable Form deactivates all previous links for your office. Share only the latest link."
        />
    </x-filament::section>

    <x-filament::section heading="Step-by-step guide">
        <ol class="list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                Go to <strong class="font-medium text-gray-950 dark:text-white">Forms → Shareable Forms</strong> and click <strong class="font-medium text-gray-950 dark:text-white">New Shareable Form</strong>.
                The form name comes from your office name automatically.
                Save, then open the record and copy the <strong class="font-medium text-gray-950 dark:text-white">Public Link</strong>.
                Share the link with employees via email, chat, or printed QR code.
            </li>
            <li>
                Go to <strong class="font-medium text-gray-950 dark:text-white">Forms → Form Submissions</strong>. The badge on the menu shows how many are waiting.
                Open each <strong class="font-medium text-gray-950 dark:text-white">Pending</strong> submission, check the data and uploaded PDFs,
                make any corrections, and click <strong class="font-medium text-gray-950 dark:text-white">Finalize</strong> when the record is ready.
                You can <em>Revert to pending</em> later as long as the submission is not inside a finalized batch.
            </li>
            <li>
                Go to <strong class="font-medium text-gray-950 dark:text-white">Batches</strong> and create a new batch.
                The system names it automatically (e.g. <code class="font-mono text-xs text-gray-800 dark:text-gray-200">PHRMO-1</code>).
                Open each finalized submission and use <strong class="font-medium text-gray-950 dark:text-white">Assign to Batch</strong> to add it.
                To remove a submission while the batch is still pending, use <strong class="font-medium text-gray-950 dark:text-white">Remove from Batch</strong>.
            </li>
            <li>
                Open the batch and click <strong class="font-medium text-gray-950 dark:text-white">Finalize Batch</strong>.
                Two requirements must be met first:
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                        <thead>
                            <tr class="border-b border-gray-200 text-start dark:border-white/10">
                                <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Requirement</th>
                                <th class="py-2 font-medium text-gray-950 dark:text-white">Why</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <tr>
                                <td class="py-2.5 pe-4 align-top">At least one submission assigned</td>
                                <td class="py-2.5 align-top">Empty batches cannot be finalized.</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 pe-4 align-top">No submission is Needs Revision</td>
                                <td class="py-2.5 align-top">Fix or unflag all items first.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3">
                    After finalize the batch shows
                    <x-filament::badge color="success">Finalized</x-filament::badge>
                    and application status
                    <x-filament::badge color="warning">Pending for Review</x-filament::badge>.
                    All admins are notified automatically.
                </p>
            </li>
        </ol>
    </x-filament::section>

    <x-filament::section heading="Requesting a modification after finalize">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            If you spot a problem after the batch was finalized — but before the admin marks it
            <x-filament::badge color="success">For Submission</x-filament::badge>
            — follow these steps:
        </p>
        <ol class="mt-4 list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                Open the problem submission and click <strong class="font-medium text-gray-950 dark:text-white">Flag Needs Revision</strong>.
                Enter remarks explaining what needs to change.
            </li>
            <li>
                On the batch page, click <strong class="font-medium text-gray-950 dark:text-white">Request Modification</strong>.
                The batch application status changes to
                <x-filament::badge color="info">Modification Requested</x-filament::badge>.
                All admins are notified.
            </li>
            <li>
                Wait for an admin to click <strong class="font-medium text-gray-950 dark:text-white">Accept Modification Request</strong>.
                The batch will move to
                <x-filament::badge color="danger">Needs Revision</x-filament::badge>.
                You will receive a notification.
            </li>
            <li>
                Edit the flagged submissions, <strong class="font-medium text-gray-950 dark:text-white">Finalize</strong> each one, then
                <strong class="font-medium text-gray-950 dark:text-white">Finalize Batch</strong> again to restart the admin review.
            </li>
        </ol>
    </x-filament::section>

    <x-filament::section heading="Quick checklist">
        <ul class="list-disc space-y-2 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>Shareable form is active and link has been shared with employees</li>
            <li>All pending submissions have been reviewed and finalized</li>
            <li>Finalized submissions are assigned to a batch</li>
            <li>Batch has been finalized and sent to admin</li>
            <li>Any Needs Revision flags from the admin are addressed promptly</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
