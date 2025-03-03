<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

// Define props to receive the initial balance
const props = defineProps({
    balance: Number,
});

// Setup form and state
const form = useForm({
    amount: '',
});

const statusMessage = ref('');
const userBalance = ref(props.balance ?? 0);

// Watch for flash events to update balance and show status messages
watch(
    () => usePage().props.flash.event,
    (event) => {
        if (event?.name === 'walletUpdated') {
            userBalance.value = event?.data.balance ?? userBalance.value;
            statusMessage.value = event?.data.message ?? 'Wallet updated successfully!';
            form.reset();

            // Auto-hide status message after 5 seconds
            setTimeout(() => {
                statusMessage.value = '';
            }, 5000);
        }
    },
    { immediate: true }
);

// Submit form to load wallet credits
const submit = () => {
    form.post(route('wallet.store'), {
        onError: () => {
            statusMessage.value = 'Failed to load credits. Please try again.';
        },
        onFinish: () => {
            form.reset();
            setTimeout(() => {
                statusMessage.value = '';
            }, 5000);
        },
    });
};

// Format the balance as currency
const formattedBalance = computed(() => {
    return `₱${parseFloat(userBalance.value).toFixed(2)}`;
});
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
                    <div class="p-6 bg-white text-gray-900">
                        <!-- Display current wallet balance -->
                        <div class="mb-4 text-lg font-semibold">
                            Current Balance:
                            <span class="text-green-500">{{ formattedBalance }}</span>
                        </div>

                        <!-- Form for loading wallet credits -->
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="amount" value="Amount to Load" />
                                <TextInput
                                    id="amount"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.amount"
                                    required
                                    placeholder="Enter amount to load"
                                    autofocus
                                    min="1"
                                />
                                <InputError class="mt-2" :message="form.errors.amount" />
                            </div>

                            <div class="flex items-center">
                                <PrimaryButton
                                    class="me-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Load Credits
                                </PrimaryButton>

                                <span v-if="statusMessage" class="text-sm text-gray-600 ms-2">
                                    {{ statusMessage }}
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
