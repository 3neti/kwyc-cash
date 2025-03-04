<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    vouchers: Array,
});

// Voucher table headers
const headers = [
    { text: 'Code', value: 'code' },
    { text: 'Amount', value: 'amount' },
    { text: 'Mobile', value: 'mobile' },
    { text: 'Status', value: 'status' },
];

// Computed property to map vouchers into a display-friendly format
const formattedVouchers = computed(() => {
    return props.vouchers.map((voucher) => ({
        ...voucher,
        amount: new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(voucher.amount),
        status: voucher.disbursed ? '✅ Disbursed' : '🕒 Available',
        mobile: voucher.mobile ?? 'N/A',
    }));
});

// Table sorting feature (optional, prepared for future enhancements)
const sortKey = ref('code');
const sortOrder = ref(1); // 1 for ascending, -1 for descending

const sortedVouchers = computed(() => {
    return [...formattedVouchers.value].sort((a, b) => {
        if (a[sortKey.value] < b[sortKey.value]) return -1 * sortOrder.value;
        if (a[sortKey.value] > b[sortKey.value]) return 1 * sortOrder.value;
        return 0;
    });
});

// Method to toggle sorting order
const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = -sortOrder.value;
    } else {
        sortKey.value = key;
        sortOrder.value = 1;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                My Cash Vouchers
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white text-gray-900">
                        <!-- Vouchers Table -->
                        <div v-if="sortedVouchers.length" class="overflow-x-auto">
                            <table class="min-w-full border-collapse table-auto">
                                <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        v-for="header in headers"
                                        :key="header.value"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                        @click="sortBy(header.value)"
                                    >
                                        {{ header.text }}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-for="voucher in sortedVouchers"
                                    :key="voucher.code"
                                    class="border-b hover:bg-gray-100 transition-colors"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ voucher.code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ voucher.amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ voucher.mobile }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ voucher.status }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- No Vouchers Fallback Message -->
                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 text-lg">
                                You do not have any vouchers yet.
                            </p>
                            <p class="mt-2 text-sm text-gray-400">
                                Generate vouchers from the Generate Cash Vouchers page.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
