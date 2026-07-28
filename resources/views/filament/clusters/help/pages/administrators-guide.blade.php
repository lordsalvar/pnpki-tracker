<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            As an admin you review finalized batches from all offices, return items for correction, approve clean packages,
            and export the final CSV and ZIP for DICT submission. You can also manage offices, users, and manually create submissions.
        </p>
    </x-filament::section>

    <x-filament::section heading="Reviewing a finalized batch">
        <ol class="list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                Open <strong class="font-medium text-gray-950 dark:text-white">Batches</strong>. Filter by the <strong class="font-medium text-gray-950 dark:text-white">Batches</strong> tab
                or look for batches with application status
                <x-filament::badge color="warning">Pending for Review</x-filament::badge>.
            </li>
            <li>
                Open each submission inside the batch. Check personal data, address, TIN, and every PDF attachment.
                If a record is clean, click <strong class="font-medium text-gray-950 dark:text-white">Mark as For Submission</strong>.
                If it needs work, click <strong class="font-medium text-gray-950 dark:text-white">Flag Needs Revision</strong> and enter remarks.
            </li>
            <li>
                When <em>every</em> submission in the batch shows
                <x-filament::badge color="success">For Submission</x-filament::badge>
                and none are flagged,
                open the batch and click <strong class="font-medium text-gray-950 dark:text-white">Mark as For Submission</strong>.
            </li>
            <li>
                Click <strong class="font-medium text-gray-950 dark:text-white">Approve Submission</strong> to mark the package as
                <x-filament::badge color="primary">Approved Submission</x-filament::badge>.
            </li>
            <li>
                While the batch status is
                <x-filament::badge color="success">For Submission</x-filament::badge>,
                use <strong class="font-medium text-gray-950 dark:text-white">Export CSV</strong> and <strong class="font-medium text-gray-950 dark:text-white">Download Attachments</strong>
                to generate the DICT submission files.
            </li>
        </ol>
    </x-filament::section>

    <x-filament::section heading="Returning a batch for revision">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            Use this when you find problems and need the representative to fix them.
        </p>
        <ol class="list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                Open each problem submission and click <strong class="font-medium text-gray-950 dark:text-white">Flag Needs Revision</strong>.
                Enter remarks so the representative knows what to fix.
            </li>
            <li>
                On the batch page, click <strong class="font-medium text-gray-950 dark:text-white">Return Batch for Revision</strong>.
                The representative is notified and the batch moves to
                <x-filament::badge color="danger">Needs Revision</x-filament::badge>.
            </li>
            <li>
                Wait for the representative to fix the flagged submissions and re-finalize the batch.
                It will re-appear as
                <x-filament::badge color="warning">Pending for Review</x-filament::badge>.
            </li>
        </ol>
    </x-filament::section>

    <x-filament::section heading="Accepting a modification request">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            When a representative flags submissions and clicks <strong class="font-medium text-gray-950 dark:text-white">Request Modification</strong>,
            the batch application status becomes
            <x-filament::badge color="info">Modification Requested</x-filament::badge>.
        </p>
        <ol class="mt-4 list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                Open the batch and click <strong class="font-medium text-gray-950 dark:text-white">Accept Modification Request</strong>.
                The batch returns to
                <x-filament::badge color="danger">Needs Revision</x-filament::badge>
                for the representative.
            </li>
            <li>
                The representative will fix the flagged submissions and re-finalize. Resume your review from step 1.
            </li>
        </ol>
    </x-filament::section>

    <x-filament::section heading="Other admin actions">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Action</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">When to use</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Revert to Pending (batch)</td>
                        <td class="py-2.5 align-top">Undo a finalize and clear application status so the batch can be rebuilt from scratch.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Create Form Submission</td>
                        <td class="py-2.5 align-top">Manually encode an employee application on behalf of an office.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Offices</td>
                        <td class="py-2.5 align-top">Add, rename, or update office records and employee headcounts.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top font-medium text-gray-950 dark:text-white">Users</td>
                        <td class="py-2.5 align-top">Create or deactivate staff accounts; assign roles and offices.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Quick checklist">
        <ul class="list-disc space-y-2 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>Open the finalized batch (Pending for Review)</li>
            <li>Mark each clean submission as For Submission</li>
            <li>Flag and return any problem submissions with remarks</li>
            <li>Mark the batch For Submission once all items are cleared</li>
            <li>Approve the batch</li>
            <li>Export CSV and download the attachment ZIP</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
