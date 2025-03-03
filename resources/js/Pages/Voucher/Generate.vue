<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    value: '',
    qty: '',
    tag: '',
});

const voucherCodes = ref('');
const statusMessage = ref('');

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
            form.reset();
            setTimeout(() => {
                statusMessage.value = '';
            }, 5000);
        },
    });
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
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="value" value="Voucher Value" />
                                <TextInput
                                    id="value"
                                    type="number"
                                    class="mt-1 block w-full"
                                    v-model="form.value"
                                    required
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

                            <div class="flex items-center">
                                <PrimaryButton
                                    class="me-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Generate Vouchers
                                </PrimaryButton>

                                <span v-if="statusMessage" class="text-sm text-gray-600 ms-2">
                                    {{ statusMessage }}
                                </span>
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
