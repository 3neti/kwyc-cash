<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Props
const props = defineProps({
    balance: Number,
});

// Form and state setup
const form = useForm({
    amount: '',
});

const statusMessage = ref('');
const userBalance = ref(props.balance ?? 0);

// Format the balance as currency
const formatter = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
});

const formattedBalance = computed(() => formatter.format(userBalance.value));

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

// Listen for deposit confirmation and balance updates via Laravel Echo
Echo.private(`user.${usePage().props.auth.user.id}`)
    .listen('.deposit.confirmed', (event) => {
        console.log(event);
        statusMessage.value = event.message;

        // Show deposit confirmation for 15 seconds
        setTimeout(() => {
            statusMessage.value = '';
        }, 3000);
    })
    .listen('.balance.updated', (event) => {
        console.log(event);
        userBalance.value = event.balanceFloat;

        // Do not change the status message when the balance is updated
        // statusMessage.value = event.message; // Commented out to prevent override
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
