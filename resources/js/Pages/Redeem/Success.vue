<template>
    <div class="voucher-success">
        <h1>Voucher Redeemed Successfully!</h1>
        <p><strong>Code:</strong> {{ voucher.code }}</p>
        <p><strong>Name:</strong> {{ voucher.metadata.name }}</p>
        <p><strong>Mobile:</strong> {{ voucher.metadata.mobile }}</p>
        <p><strong>Amount Disbursed:</strong> {{ formatCurrency(voucher.cash.value) }}</p>
        <p><strong>Date Disbursed:</strong> {{ formatDate(voucher.created_at) }}</p>

        <div v-if="voucher.metadata.signature" class="signature-box">
            <h2>Signature:</h2>
            <img :src="voucher.metadata.signature" alt="Signature" />
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { router } from "@inertiajs/vue3";

const props = defineProps({
    voucher: {
        type: Object,
        required: true,
    },
    redirectTimeout: {
        type: Number,
        default: 3000, // Default to 3 seconds
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};

const formatDate = (datetime) => {
    const date = new Date(datetime);
    return date.toLocaleString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        second: 'numeric',
        hour12: true,
    });
};

// Automatically redirect to the rider page after the configured timeout
onMounted(() => {
    setTimeout(() => {
        router.get(route('rider'));
    }, props.redirectTimeout);
});
</script>

<style>
.voucher-success {
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background-color: #f9f9f9;
    max-width: 600px;
    margin: 20px auto;
    text-align: center;
}

.signature-box {
    margin-top: 20px;
}

img {
    max-width: 100%;
    border: 1px solid #ccc;
    border-radius: 4px;
}
</style>
