<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

import { useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

// Define props with default values
const props = defineProps({
    defaultVoucherValue: {
        type: Number,
        default: 50, // Default voucher value
    },
    minAmount: {
        type: Number,
        default: 50, // Minimum voucher value
    },
    stepAmount: {
        type: Number,
        default: 50, // Step increment for the voucher value
    },
});

// Form state with default values
const form = useForm({
    value: props.defaultVoucherValue,
    qty: 1,
    tag: '',
});

const voucherCodes = ref('');
const statusMessage = ref('');

// Get current balance from props
const userBalance = computed(() => usePage().props.auth.user.balanceFloat ?? 0);

// Format balance as currency
const formatter = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
});
const formattedBalance = computed(() => formatter.format(userBalance.value));

// Calculate total voucher value
const totalVoucherValue = computed(() => {
    const value = parseFloat(form.value) || 0;
    const qty = parseInt(form.qty) || 0;
    return value * qty;
});

// Button visibility based on balance check
const canGenerateVouchers = computed(() => {
    return totalVoucherValue.value <= userBalance.value;
});

// Clear status message after 3 seconds
watch(statusMessage, (newMessage) => {
    if (newMessage) {
        setTimeout(() => {
            statusMessage.value = '';
        }, 3000);
    }
});

// Form submission handler
const submit = () => {
    form.post(route('vouchers.store'), {
        onSuccess: (response) => {
            const vouchers = response.props.flash.data?.map(voucher => voucher.code) ?? [];
            voucherCodes.value = vouchers.join(', ');
            statusMessage.value = 'Vouchers generated successfully!';
        },
        onError: () => {
            statusMessage.value = 'Failed to generate vouchers. Please try again.';
        },
        onFinish: () => {
            form.reset({
                value: props.defaultVoucherValue,
                qty: 1,
                tag: '',
            });
        },
    });
};

// Redirect to Load Credits page
const redirectToLoadCredits = () => {
    router.get(route('wallet.create'));
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Generate Cash Vouchers
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white text-gray-900">
                        <!-- Status Message and Current Balance -->
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex-1">
                                <span v-if="statusMessage" class="text-sm text-gray-600">
                                    {{ statusMessage }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 font-semibold text-right">
                                Current Balance: <span class="text-blue-600">{{ formattedBalance }}</span>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="value" value="Voucher Value" />
                                <TextInput
                                    id="value"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.value"
                                    required
                                    :min="props.minAmount"
                                    :step="props.stepAmount"
                                    placeholder="Enter voucher value"
                                />
                                <InputError class="mt-2" :message="form.errors.value" />
                            </div>

                            <div>
                                <InputLabel for="qty" value="Quantity" />
                                <TextInput
                                    id="qty"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.qty"
                                    required
                                    min="1"
                                    placeholder="Enter quantity of vouchers"
                                />
                                <InputError class="mt-2" :message="form.errors.qty" />
                            </div>

                            <div>
                                <InputLabel for="tag" value="Tag (Optional)" />
                                <TextInput
                                    id="tag"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.tag"
                                    placeholder="Enter an optional tag"
                                />
                                <InputError class="mt-2" :message="form.errors.tag" />
                            </div>

                            <!-- Display total voucher value and validate against balance -->
                            <div class="flex justify-between items-center mb-4">
                                <div class="text-sm text-gray-600">
                                    Total Voucher Value: <span class="text-green-600">{{ formatter.format(totalVoucherValue) }}</span>
                                </div>
                                <div class="text-sm text-red-600" v-if="!canGenerateVouchers">
                                    Insufficient balance for this transaction.
                                </div>
                            </div>

                            <div class="flex justify-center mt-6">
                                <template v-if="canGenerateVouchers">
                                    <PrimaryButton
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing"
                                    >
                                        Generate Vouchers
                                    </PrimaryButton>
                                </template>
                                <template v-else>
                                    <PrimaryButton
                                        class="bg-yellow-500 hover:bg-yellow-600"
                                        @click.prevent="redirectToLoadCredits"
                                    >
                                        Load Credits
                                    </PrimaryButton>
                                </template>
                            </div>
                        </form>

                        <div v-if="voucherCodes" class="mt-6 p-4 bg-green-100 rounded-md">
                            <p class="text-green-800 font-semibold mb-2">Generated Voucher Codes:</p>
                            <p class="text-sm text-gray-700 break-all">{{ voucherCodes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
