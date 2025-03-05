<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

// Props including the updated vouchers data with CashData
const props = defineProps({
    vouchers: Array,
});

// Voucher table headers with new 'Redeemed' and 'Expired' columns
const headers = [
    { text: 'Code', value: 'code' },
    { text: 'Amount', value: 'cash.value' },
    { text: 'Tag', value: 'cash.tag' },
    { text: 'Mobile', value: 'mobile' },
    { text: 'Status', value: 'status' },
    { text: 'Redeemed', value: 'redeemed' },
    { text: 'Expired', value: 'expired' },
];

// Filter state
const filters = ref({
    status: '',
    tag: '',
    minAmount: '',
    maxAmount: '',
    redeemed: '',
    expired: '',
});

// Voucher tags for filter dropdown
const availableTags = computed(() => {
    const tags = props.vouchers.map(voucher => voucher.cash?.tag).filter(tag => tag);
    return Array.from(new Set(tags));
});

// Computed property to map vouchers into a display-friendly format
const formattedVouchers = computed(() => {
    return props.vouchers.map((voucher) => ({
        ...voucher,
        amount: voucher.cash?.value ?? 0,
        formattedAmount: new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(voucher.cash?.value ?? 0),
        status: voucher.disbursed ? '✅ Disbursed' : '🕒 Undisbursed',
        mobile: voucher.mobile ?? 'N/A',
        tag: voucher.cash?.tag ?? 'N/A',
        redeemed: voucher.redeemed ? '✅ Redeemed' : '🕒 Not Redeemed',
        expired: voucher.expired ? '❌ Expired' : '🟢 Active',
    }));
});

// Apply filters to vouchers
const filteredVouchers = computed(() => {
    return formattedVouchers.value.filter((voucher) => {
        const matchesStatus =
            !filters.value.status ||
            (filters.value.status === 'Disbursed' && voucher.disbursed) ||
            (filters.value.status === 'Undisbursed' && !voucher.disbursed);

        const matchesTag = !filters.value.tag || voucher.tag === filters.value.tag;

        const matchesMinAmount =
            !filters.value.minAmount ||
            voucher.amount >= parseFloat(filters.value.minAmount);

        const matchesMaxAmount =
            !filters.value.maxAmount ||
            voucher.amount <= parseFloat(filters.value.maxAmount);

        const matchesRedeemed =
            !filters.value.redeemed ||
            (filters.value.redeemed === 'Redeemed' && voucher.redeemed) ||
            (filters.value.redeemed === 'Not Redeemed' && !voucher.redeemed);

        const matchesExpired =
            !filters.value.expired ||
            (filters.value.expired === 'Expired' && voucher.expired) ||
            (filters.value.expired === 'Active' && !voucher.expired);

        return (
            matchesStatus &&
            matchesTag &&
            matchesMinAmount &&
            matchesMaxAmount &&
            matchesRedeemed &&
            matchesExpired
        );
    });
});

// Sorting feature
const sortKey = ref('code');
const sortOrder = ref(1);

const sortedVouchers = computed(() => {
    return [...filteredVouchers.value].sort((a, b) => {
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

// Clear all filters
const clearFilters = () => {
    filters.value = {
        status: '',
        tag: '',
        minAmount: '',
        maxAmount: '',
        redeemed: '',
        expired: '',
    };
};

// Download filtered vouchers as CSV
const downloadCSV = () => {
    const csvContent = [
        ['Code', 'Amount', 'Tag', 'Mobile', 'Status', 'Redeemed', 'Expired'],
        ...sortedVouchers.value.map(voucher => [
            voucher.code,
            voucher.amount,
            voucher.tag,
            voucher.mobile,
            voucher.status,
            voucher.redeemed,
            voucher.expired,
        ]),
    ]
        .map(row => row.map(String).map(value => `"${value}"`).join(','))
        .join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'vouchers.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <!-- Filters Section -->
                <div class="bg-white p-4 rounded-md shadow-sm space-y-2">
                    <div class="flex space-x-4">
                        <select v-model="filters.status" class="p-2 border rounded">
                            <option value="">All Status</option>
                            <option value="Disbursed">Disbursed</option>
                            <option value="Undisbursed">Undisbursed</option>
                        </select>

                        <select v-model="filters.redeemed" class="p-2 border rounded">
                            <option value="">All Redeemed</option>
                            <option value="Redeemed">Redeemed</option>
                            <option value="Not Redeemed">Not Redeemed</option>
                        </select>

                        <select v-model="filters.expired" class="p-2 border rounded">
                            <option value="">All Expired</option>
                            <option value="Expired">Expired</option>
                            <option value="Active">Active</option>
                        </select>

                        <input v-model="filters.minAmount" type="number" placeholder="Min Amount"
                               class="p-2 border rounded" />
                        <input v-model="filters.maxAmount" type="number" placeholder="Max Amount"
                               class="p-2 border rounded" />

                        <button @click="clearFilters" class="px-4 py-2 bg-gray-200 rounded">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Vouchers Table -->
                <div class="bg-white p-4 rounded-md shadow-sm">
                    <table class="min-w-full border-collapse">
                        <thead>
                        <tr>
                            <th v-for="header in headers" :key="header.value"
                                @click="sortBy(header.value)"
                                class="px-4 py-2 cursor-pointer">
                                {{ header.text }}
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="voucher in sortedVouchers" :key="voucher.code">
                            <td>{{ voucher.code }}</td>
                            <td>{{ voucher.formattedAmount }}</td>
                            <td>{{ voucher.tag }}</td>
                            <td>{{ voucher.mobile }}</td>
                            <td>{{ voucher.status }}</td>
                            <td>{{ voucher.redeemed }}</td>
                            <td>{{ voucher.expired }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Download CSV Button -->
                <div class="flex justify-end">
                    <button @click="downloadCSV" class="px-4 py-2 bg-blue-500 text-white rounded">
                        Download CSV
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
