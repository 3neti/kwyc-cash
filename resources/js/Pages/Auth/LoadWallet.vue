<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Props
const props = defineProps({
    defaultAmount: {
        type: Number,
        default: 50,
    },
    stepAmount: {
        type: Number,
        default: 50,
    },
});

// Form and state setup
const form = useForm({
    amount: props.defaultAmount,
    account: usePage().props.auth.user.mobile ?? '',
});

const statusMessage = ref('');
const qrCode = ref(null);
const showGenerateButton = ref(true);

// Get and format the wallet balance
const userBalance = computed(() => usePage().props.auth.user.balanceFloat);
const formattedBalance = computed(() =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(userBalance.value)
);

// Computed property for the dynamic QR code label
const qrCodeLabel = computed(() => `Scan QR Code to deposit`);
const formattedAmount = computed(() =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(form.amount)
);

// Generate the QR code
const generateQRCode = () => {
    axios
        .get(route('wallet.qr-code'), { params: form })
        .then(({ data }) => {
            if (data.success) {
                qrCode.value = data.qr_code;
                showGenerateButton.value = false;
                statusMessage.value = 'QR code generated successfully.';

                // Clear status message after 3 seconds
                setTimeout(() => {
                    statusMessage.value = '';
                }, 3000);
            } else {
                statusMessage.value = data.message || 'Failed to generate QR code.';
            }
        })
        .catch(() => {
            statusMessage.value = 'Error occurred while generating QR code.';
        });
};

// Clear the QR code when the amount is changed
const clearQRCode = () => {
    qrCode.value = null;
    showGenerateButton.value = true;
    statusMessage.value = '';
};

// Auto-generate the QR code on page load
generateQRCode();

// Download the QR code as an image
const downloadQRCode = () => {
    if (!qrCode.value) return;

    const link = document.createElement('a');
    link.href = qrCode.value;
    link.download = `QR_Code_Deposit_${formattedAmount.value}.png`;
    link.click();
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Load Wallet Credits
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white text-gray-900 space-y-6">
                        <!-- Status Message and Current Balance in one row -->
                        <div class="flex items-center justify-between mb-4">
                            <!-- Status message container with fixed width -->
                            <div class="flex-1">
                                <span v-if="statusMessage" class="text-sm text-gray-600">
                                    {{ statusMessage }}
                                </span>
                            </div>
                            <!-- Current Balance aligned to the right -->
                            <div class="text-right flex-none">
                                <span class="block text-sm font-medium text-gray-700">
                                    Current Balance:
                                </span>
                                <span class="text-lg font-semibold text-green-500">
                                    {{ formattedBalance }}
                                </span>
                            </div>
                        </div>

                        <!-- Form for generating deposit QR code -->
                        <div class="space-y-6">
                            <div>
                                <InputLabel for="amount" value="Amount to Deposit" />
                                <TextInput
                                    id="amount"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.amount"
                                    :min="props.defaultAmount"
                                    :step="props.stepAmount"
                                    @input="clearQRCode"
                                    required
                                    placeholder="Enter amount to load"
                                    autofocus
                                />
                                <InputError class="mt-2" :message="form.errors.amount" />
                            </div>

                            <div>
                                <InputLabel for="account" value="Account Number" />
                                <TextInput
                                    id="account"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.account"
                                    :readonly="!!form.account"
                                    placeholder="Enter account number"
                                />
                                <InputError class="mt-2" :message="form.errors.account" />
                            </div>

                            <div class="flex justify-center space-x-4">
                                <PrimaryButton
                                    v-if="showGenerateButton"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    @click="generateQRCode"
                                >
                                    Generate Deposit QR Code
                                </PrimaryButton>
                            </div>

                            <!-- Display the QR Code if available -->
                            <div v-if="qrCode" class="text-center mt-4">
                                <h3 class="text-lg font-semibold mb-2">
                                    {{ qrCodeLabel }}
                                </h3>
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ formattedAmount }}
                                </p>
                                <img
                                    :src="qrCode"
                                    alt="Deposit QR Code"
                                    class="mx-auto mt-4"
                                />
                                <!-- Download Button for QR Code -->
                                <div class="flex justify-center mt-4">
                                    <PrimaryButton
                                        class="bg-green-500 hover:bg-green-600"
                                        @click="downloadQRCode"
                                    >
                                        Download QR Code
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
