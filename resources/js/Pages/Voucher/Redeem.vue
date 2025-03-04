<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

// Import lodash debounce
import { debounce } from 'lodash';

// Define props with default values, including metaLabel and reference message
const props = defineProps({
    metaLabel: {
        type: String,
        default: 'Additional Info (Optional)',
    },
    referenceLabel: {
        type: String,
        default: 'Agent',
    },
    defaultReference: {
        type: String,
        default: '',
    },
});

// Extract URL search parameters
const params = new URLSearchParams(window.location.search);

// Initialize form with default values from URL parameters or props
const form = useForm({
    voucher_code: params.get('voucher_code') ?? '',
    mobile: params.get('mobile') ?? '',
    country: params.get('country') ?? 'PH',
    meta: params.get('meta') ?? '',
    reference: params.get('reference') ?? props.defaultReference,
});

const isCheckingStatus = ref(false);
const statusMessage = ref('');
const referenceMessage = computed(() =>
    form.reference ? `${props.referenceLabel}: ${form.reference}` : ''
);

// Voucher details message
const voucherDetailsMessage = ref('');

// Start polling for voucher redemption status
const startPolling = (voucherCode) => {
    isCheckingStatus.value = true;
    axios.get(route('redeem.show', { voucher: voucherCode }))
        .then((response) => {
            if (response.data.status === 'completed') {
                setStatusMessage('Cash disbursed successfully!');
                stopPolling(true);
            } else if (response.data.status === 'pending') {
                setStatusMessage('Voucher redeemed. Waiting for disbursement...');
            }
        })
        .catch(() => {
            setStatusMessage('Error checking voucher status. Please try again.');
            stopPolling(true);
        });
};

// Stop polling and reset the form if needed
const stopPolling = (resetForm = false) => {
    isCheckingStatus.value = false;
    if (resetForm) {
        form.reset('voucher_code', 'mobile', 'country', 'meta', 'reference');
    }
};

// Submit the form
const submit = () => {
    form.post(route('redeem.store'), {
        onFinish: () => {
            setStatusMessage('Processing... Please wait.');
            startPolling(form.voucher_code);
        },
    });
};

// Set status message and auto-dismiss after 5 seconds
const setStatusMessage = (message) => {
    statusMessage.value = message;
    setTimeout(() => {
        statusMessage.value = '';
    }, 5000);
};

// Fetch voucher details with a debounce
const fetchVoucherDetails = debounce(() => {
    if (form.voucher_code.length < 4) {
        voucherDetailsMessage.value = '';
        return;
    }

    axios
        .get(route('api.vouchers.show', { voucherCode: form.voucher_code }))
        .then((response) => {
            if (response.data.status === 'success') {
                const { amount, mobile, disbursed } = response.data.data;
                voucherDetailsMessage.value = `
                    ₱${amount.toFixed(2)} -
                    ${disbursed ? '✅ Disbursed' : '🟢 Available'}
                    ${mobile ? `to ${mobile}` : ''}`.trim();
            } else {
                voucherDetailsMessage.value = 'Invalid or expired voucher code.';
            }
        })
        .catch(() => {
            voucherDetailsMessage.value = 'Error retrieving voucher details.';
        });
}, 500);

// Watch for changes to the voucher code input
watch(() => form.voucher_code, fetchVoucherDetails);
</script>

<template>
    <GuestLayout>
        <Head title="Redeem Voucher" />

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Voucher Code Input -->
            <div>
                <InputLabel for="voucher_code" value="Voucher Code" />
                <TextInput
                    id="voucher_code"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.voucher_code"
                    required
                    autofocus
                    placeholder="Enter voucher code"
                />
                <InputError class="mt-2" :message="form.errors.voucher_code" />
                <!-- Display Voucher Details under the Input -->
                <p v-if="voucherDetailsMessage" class="text-xs text-gray-500 mt-1">
                    {{ voucherDetailsMessage }}
                </p>
            </div>

            <!-- Mobile Number Input -->
            <div>
                <InputLabel for="mobile" value="Mobile Number" />
                <TextInput
                    id="mobile"
                    type="tel"
                    class="mt-1 block w-full"
                    v-model="form.mobile"
                    required
                    placeholder="e.g., 09171234567"
                />
                <InputError class="mt-2" :message="form.errors.mobile" />
            </div>

            <!-- Optional Meta Field with Dynamic Label -->
            <div>
                <InputLabel for="meta" :value="props.metaLabel" />
                <TextInput
                    id="meta"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.meta"
                    placeholder="Enter additional info"
                />
                <InputError class="mt-2" :message="form.errors.meta" />
            </div>

            <div class="mt-4 flex items-center justify-between gap-2">
                <!-- Reference Message aligned to the left -->
                <span
                    v-if="referenceMessage"
                    class="text-sm text-gray-700 font-semibold"
                >
                    {{ referenceMessage }}
                </span>

                <!-- Status Message aligned to the left of the button -->
                <span
                    v-if="statusMessage"
                    class="text-sm text-gray-500 transition-opacity duration-500 min-w-[200px] text-right"
                >
                    {{ statusMessage }}
                </span>

                <!-- Redeem Voucher Button -->
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing || isCheckingStatus }"
                    :disabled="form.processing || isCheckingStatus"
                >
                    Redeem Voucher
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
