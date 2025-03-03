<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-2xl font-semibold mb-4">Welcome to Your Dashboard!</h3>
                        <p class="text-gray-700 mb-6">
                            As an authorized user, you have access to all the features needed to manage cash vouchers, load credits, and facilitate seamless voucher redemptions through GCash.
                        </p>

                        <h4 class="text-xl font-semibold mb-3">Available Actions:</h4>
                        <ul class="list-disc list-inside mb-6 text-gray-700">
                            <li>
                                <strong>Create Vouchers:</strong> Generate cash vouchers that can be distributed to recipients.
                            </li>
                            <li>
                                <strong>Load Wallet:</strong> Add credits to your wallet to facilitate voucher creation and disbursement.
                            </li>
                            <li>
                                <strong>Redeem Vouchers:</strong> Enable voucher bearers to redeem cash through GCash.
                            </li>
                            <li>
                                <strong>Monitor Transactions:</strong> Track the status of voucher redemptions and wallet activities.
                            </li>
                        </ul>

                        <div class="flex flex-wrap gap-4">
                            <Link
                                :href="route('vouchers.create')"
                                class="px-6 py-3 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 transition"
                            >
                                Create Vouchers
                            </Link>

                            <Link
                                :href="route('wallet.create')"
                                class="px-6 py-3 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 transition"
                            >
                                Load Wallet
                            </Link>

                            <Link
                                :href="route('redeem.create')"
                                class="px-6 py-3 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 transition"
                            >
                                Redeem Voucher
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-xl font-semibold mb-2 text-gray-800">
                            How to Use This Dashboard
                        </h4>
                        <p class="text-gray-700 mb-4">
                            Follow these steps to efficiently manage your cash vouchers and wallet:
                        </p>
                        <ol class="list-decimal list-inside text-gray-700 space-y-2">
                            <li>
                                Start by <strong>loading credits</strong> to your wallet to ensure you have enough balance for voucher creation.
                            </li>
                            <li>
                                Navigate to the <strong>Create Vouchers</strong> section to generate vouchers in bulk.
                            </li>
                            <li>
                                Share the voucher codes with authorized bearers, who can then <strong>redeem</strong> them via GCash.
                            </li>
                            <li>
                                Monitor the status of voucher redemptions and ensure successful cash disbursement.
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
