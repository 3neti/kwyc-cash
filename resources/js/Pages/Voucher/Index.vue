<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {computed, nextTick, onMounted, ref, watch} from 'vue';
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
        'Signature',
        'Redeemed',
        'Expired',
        'Disbursed',
        'Created At',
    ];

    const csvContent = [
        csvHeaders.join(','),
        ...props.vouchers.map((voucher) => {
            return [
                voucher.code,
                voucher.mobile ?? 'N/A',
                formatJson({ ...voucher.metadata, signature: undefined }).replace(/"/g, '""'),
                formatJson(voucher.cash).replace(/"/g, '""'),
                voucher.metadata.signature ? 'Has Signature' : 'No Signature',
                voucher.redeemed ? 'Yes' : 'No',
                voucher.expired ? 'Yes' : 'No',
                voucher.disbursed ? 'Yes' : 'No',
                voucher.created_at,
            ]
                .map((field) => `"${field}"`)
                .join(',');
        }),
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    saveAs(blob, 'vouchers.csv');
};

// Filter dropdown states
const redeemedFilter = ref('');
const disbursedFilter = ref('');
const fieldFilter = ref('');
const fieldFilterValue = ref('');

const filteredVouchers = computed(() => {
    return props.vouchers.filter((voucher) => {
        // Redeemed filter
        const matchesRedeemed =
            !redeemedFilter.value || String(voucher.redeemed) === redeemedFilter.value;

        // Disbursed filter
        const matchesDisbursed =
            !disbursedFilter.value || String(voucher.disbursed) === disbursedFilter.value;

        // Field-based filtering
        const matchesFieldFilter = (() => {
            const value = fieldFilterValue.value.toLowerCase();

            if (!fieldFilter.value || !value) return true;

            switch (fieldFilter.value) {
                case 'voucher_code':
                    return (
                        value.length >= 4 &&
                        voucher.code.toLowerCase().includes(value)
                    );
                case 'mobile':
                    return (
                        value.length >= 11 &&
                        (voucher.mobile ?? '').toLowerCase().includes(value)
                    );
                case 'name':
                    return (
                        value.length >= 1 &&
                        (voucher.metadata.name ?? '').toLowerCase().includes(value)
                    );
                default:
                    return true;
            }
        })();

        return matchesRedeemed && matchesDisbursed && matchesFieldFilter;
    });
});

// Watcher to clear the filter value when the filter field changes
watch(fieldFilter, () => {
    fieldFilterValue.value = '';
});
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
                    <div class="flex gap-4 mb-4 items-center">
                        <!-- Existing Filter Dropdowns -->
                        <div>
                            <label for="redeemedFilter" class="block text-sm font-medium text-gray-700">Redeemed</label>
                            <select
                                id="redeemedFilter"
                                v-model="redeemedFilter"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="">All</option>
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                        </div>

                        <div>
                            <label for="disbursedFilter" class="block text-sm font-medium text-gray-700">Disbursed</label>
                            <select
                                id="disbursedFilter"
                                v-model="disbursedFilter"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="">All</option>
                                <option value="true">Yes</option>
                                <option value="false">No</option>
                            </select>
                        </div>

                        <!-- New Field Filter Dropdown -->
                        <div>
                            <label for="fieldFilter" class="block text-sm font-medium text-gray-700">Filter By</label>
                            <select
                                id="fieldFilter"
                                v-model="fieldFilter"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                <option value="">Select Field</option>
                                <option value="voucher_code">Voucher Code</option>
                                <option value="mobile">Mobile</option>
                                <option value="name">Name</option>
                            </select>
                        </div>

                        <!-- Filter Value Input -->
                        <div class="flex-1">
                            <label for="fieldFilterValue" class="block text-sm font-medium text-gray-700">Filter Value</label>
                            <input
                                id="fieldFilterValue"
                                type="text"
                                v-model="fieldFilterValue"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter filter value"
                            />
                        </div>
                    </div>

                    <table class="min-w-full bg-white border">
                        <thead>
                        <tr class="bg-gray-200">
                            <th class="px-4 py-2 border">Voucher Code</th>
                            <th class="px-4 py-2 border">Metadata</th>
                            <th class="px-4 py-2 border">Cash Data</th>
                            <th class="px-4 py-2 border">Assigned To</th> <!-- New column for contact -->
                            <th class="px-4 py-2 border">Status</th>
                            <th class="px-4 py-2 border">Signed</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="voucher in filteredVouchers" :key="voucher.code" class="border-t">
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
            {{ formatJson({ ...voucher.metadata, signature: undefined }) }}
        </pre>
                            </td>

                            <!-- Cash Data as Pretty JSON -->
                            <td class="px-4 py-2 border text-xs whitespace-pre-wrap">
        <pre class="bg-gray-50 p-2 rounded overflow-auto">
            {{ formatJson(voucher.cash) }}
        </pre>
                            </td>

                            <!-- Contact Information Column -->
                            <td class="px-4 py-2 border text-sm">
                                <div v-if="voucher.contact">
                                    <div class="font-semibold">Mobile: {{ voucher.contact.mobile }}</div>
                                    <div class="text-gray-500">Country: {{ voucher.contact.country }}</div>
                                </div>
                                <div v-else class="text-gray-500">No Contact</div>
                            </td>

                            <!-- Status Information -->
                            <td class="px-4 py-2 border text-sm">
                                <div :class="voucher.redeemed ? 'text-red-600' : 'text-gray-500'">
                                    Redeemed: {{ voucher.redeemed ? 'Yes' : 'No' }}
                                </div>
                                <div :class="voucher.disbursed ? 'text-green-600' : 'text-gray-500'">
                                    Disbursed: {{ voucher.disbursed ? 'Yes' : 'No' }}
                                </div>
                                <div class="text-gray-500 text-xs mt-1">
                                    Created: {{ voucher.created_at }}
                                </div>
                            </td>

                            <!-- Display Signature Separately if Available -->
                            <td class="px-4 py-2 border text-center">
                                <div v-if="voucher.metadata.signature" class="signature-box">
                                    <img :src="voucher.metadata.signature" alt="Signature" class="max-h-20 mx-auto border rounded" />
                                </div>
                                <div v-else class="text-gray-500">No Signature</div>
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
.signature-box img {
    max-height: 80px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 2px;
}
</style>
