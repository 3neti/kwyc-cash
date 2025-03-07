<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { computed, ref } from 'vue';
import { saveAs } from 'file-saver';

// Define props to receive the vouchers data
const props = defineProps({
    vouchers: {
        type: Array,
        default: () => [],
    },
});

// Format JSON in a pretty way
const formatJson = (jsonData) => {
    try {
        return JSON.stringify(jsonData, null, 2);
    } catch (e) {
        return jsonData;
    }
};

// Download vouchers as a CSV file
const downloadCsv = () => {
    if (!props.vouchers.length) return;

    const csvHeaders = [
        'Voucher Code',
        'Mobile',
        'Metadata',
        'Cash Data',
        'Redeemed',
        'Expired',
        'Disbursed',
        'Created At',
    ];

    const csvContent = [
        csvHeaders.join(','), // Add headers as the first row
        ...props.vouchers.map((voucher) => {
            return [
                voucher.code,
                voucher.mobile ?? 'N/A',
                formatJson(voucher.metadata).replace(/"/g, '""'),
                formatJson(voucher.cash).replace(/"/g, '""'),
                voucher.redeemed ? 'Yes' : 'No',
                voucher.expired ? 'Yes' : 'No',
                voucher.disbursed ? 'Yes' : 'No',
                voucher.created_at,
            ]
                .map((field) => `"${field}"`) // Wrap fields in quotes for CSV compatibility
                .join(',');
        }),
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    saveAs(blob, 'vouchers.csv');
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                View Transactions
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Vouchers Table -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Vouchers List</h3>
                        <PrimaryButton @click="downloadCsv">Download CSV</PrimaryButton>
                    </div>

                    <table class="min-w-full bg-white border">
                        <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 border">Voucher Code</th>
                            <th class="px-4 py-2 border">Metadata</th>
                            <th class="px-4 py-2 border">Cash Data</th>
                            <th class="px-4 py-2 border">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="voucher in props.vouchers"
                            :key="voucher.code"
                            class="border-t"
                        >
                            <!-- Voucher Code & Mobile -->
                            <td class="px-4 py-2 border text-sm">
                                <div class="font-semibold">{{ voucher.code }}</div>
                                <div class="text-gray-500">
                                    Redeemer: {{ voucher.metadata.name ?? (voucher.mobile ?? 'N/A') }}
                                </div>
                            </td>

                            <!-- Metadata as Pretty JSON -->
                            <td class="px-4 py-2 border text-xs whitespace-pre-wrap">
                                    <pre class="bg-gray-50 p-2 rounded overflow-auto">
                                        {{ formatJson(voucher.metadata) }}
                                    </pre>
                            </td>

                            <!-- Cash Data as Pretty JSON -->
                            <td class="px-4 py-2 border text-xs whitespace-pre-wrap">
                                    <pre class="bg-gray-50 p-2 rounded overflow-auto">
                                        {{ formatJson(voucher.cash) }}
                                    </pre>
                            </td>

                            <!-- Status Information -->
                            <td class="px-4 py-2 border text-sm">
                                <div :class="voucher.redeemed ? 'text-red-600' : 'text-gray-500'">
                                    Redeemed: {{ voucher.redeemed ? 'Yes' : 'No' }}
                                </div>
<!--                                <div :class="voucher.expired ? 'text-orange-600' : 'text-gray-500'">-->
<!--                                    Expired: {{ voucher.expired ? 'Yes' : 'No' }}-->
<!--                                </div>-->
                                <div :class="voucher.disbursed ? 'text-green-600' : 'text-gray-500'">
                                    Disbursed: {{ voucher.disbursed ? 'Yes' : 'No' }}
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Created: {{ voucher.created_at }}
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <div v-if="!props.vouchers.length" class="mt-4 text-gray-500">
                        No vouchers available.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
pre {
    max-height: 150px;
    overflow-y: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
