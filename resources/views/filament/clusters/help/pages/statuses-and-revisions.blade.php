<x-filament-panels::page>
    <x-filament::section>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Three separate status fields track where a record sits in the workflow:
            the <strong class="font-medium text-gray-950 dark:text-white">submission status</strong> (per employee record),
            the <strong class="font-medium text-gray-950 dark:text-white">batch status</strong> (for the group), and the
            <strong class="font-medium text-gray-950 dark:text-white">application status</strong> (the admin workflow on a finalized batch).
            Flagging a submission always requires remarks.
        </p>
    </x-filament::section>

    <x-filament::section heading="Submission status — per employee record">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Status</th>
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Meaning</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Who sets it</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="warning">Pending</x-filament::badge></td>
                        <td class="py-2.5 pe-4 align-top">New or reverted; representative can edit freely.</td>
                        <td class="py-2.5 align-top">System (on submit or revert)</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="info">Finalized</x-filament::badge></td>
                        <td class="py-2.5 pe-4 align-top">Representative approved the data.</td>
                        <td class="py-2.5 align-top">Representative</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="danger">Needs Revision</x-filament::badge></td>
                        <td class="py-2.5 pe-4 align-top">Flagged for corrections with remarks.</td>
                        <td class="py-2.5 align-top">Admin or Representative</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="success">For Submission</x-filament::badge></td>
                        <td class="py-2.5 pe-4 align-top">Cleared by admin for the DICT package. Flags removed.</td>
                        <td class="py-2.5 align-top">Admin</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Batch status — for the group">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Status</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Meaning</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="warning">Pending</x-filament::badge></td>
                        <td class="py-2.5 align-top">Being assembled; representative can add or remove submissions.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="success">Finalized</x-filament::badge></td>
                        <td class="py-2.5 align-top">Sent to admin for review. Locks the batch.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="danger">Needs Revision</x-filament::badge></td>
                        <td class="py-2.5 align-top">Returned for representative corrections.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Application status — admin workflow on a finalized batch">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-600 dark:text-gray-400">
                <thead>
                    <tr class="border-b border-gray-200 text-start dark:border-white/10">
                        <th class="py-2 pe-4 font-medium text-gray-950 dark:text-white">Status</th>
                        <th class="py-2 font-medium text-gray-950 dark:text-white">Meaning</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="warning">Pending for Review</x-filament::badge></td>
                        <td class="py-2.5 align-top">Batch finalized; waiting for admin to start reviewing.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="info">Modification Requested</x-filament::badge></td>
                        <td class="py-2.5 align-top">Representative flagged issues and asked to reopen the batch.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="danger">Needs Revision</x-filament::badge></td>
                        <td class="py-2.5 align-top">Admin accepted the mod request or returned the batch; rep must fix.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="success">For Submission</x-filament::badge></td>
                        <td class="py-2.5 align-top">All submissions cleared. Export and ZIP are available.</td>
                    </tr>
                    <tr>
                        <td class="py-2.5 pe-4 align-top"><x-filament::badge color="primary">Approved Submission</x-filament::badge></td>
                        <td class="py-2.5 align-top">Admin approved the complete package.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Revision loop A — admin returns the batch">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Use this when the admin spots problems after the batch was finalized.</p>
        <ol class="list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                Admin opens a problem submission → <strong class="font-medium text-gray-950 dark:text-white">Flag Needs Revision</strong> (remarks required).
            </li>
            <li>
                Admin opens the batch → <strong class="font-medium text-gray-950 dark:text-white">Return Batch for Revision</strong>.
                Batch becomes <x-filament::badge color="danger">Needs Revision</x-filament::badge>; rep is notified.
            </li>
            <li>
                Representative edits each flagged submission → <strong class="font-medium text-gray-950 dark:text-white">Finalize</strong>.
            </li>
            <li>
                Representative clicks <strong class="font-medium text-gray-950 dark:text-white">Finalize Batch</strong>.
                Application status returns to <x-filament::badge color="warning">Pending for Review</x-filament::badge>.
            </li>
        </ol>
    </x-filament::section>

    <x-filament::section heading="Revision loop B — representative requests reopening">
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Use this when the rep finds a problem after the batch was finalized but before admin approves.</p>
        <ol class="list-decimal space-y-3 ps-5 text-sm text-gray-600 dark:text-gray-400">
            <li>
                Representative opens a problem submission → <strong class="font-medium text-gray-950 dark:text-white">Flag Needs Revision</strong> (remarks required).
            </li>
            <li>
                Representative opens the batch → <strong class="font-medium text-gray-950 dark:text-white">Request Modification</strong>.
                Application status becomes <x-filament::badge color="info">Modification Requested</x-filament::badge>; admins are notified.
            </li>
            <li>
                Admin opens the batch → <strong class="font-medium text-gray-950 dark:text-white">Accept Modification Request</strong>.
                Batch becomes <x-filament::badge color="danger">Needs Revision</x-filament::badge>; rep is notified.
            </li>
            <li>
                Representative edits → <strong class="font-medium text-gray-950 dark:text-white">Finalize</strong> submissions → <strong class="font-medium text-gray-950 dark:text-white">Finalize Batch</strong>.
            </li>
        </ol>
    </x-filament::section>
</x-filament-panels::page>
