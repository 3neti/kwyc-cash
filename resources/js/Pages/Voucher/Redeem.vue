<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const form = useForm({
    voucher_code: '',
    mobile: '',
    country: 'PH',
});

const submit = () => {
    form.post(route('redeem.store'), {
        onFinish: () => form.reset('voucher_code', 'mobile', 'country'),
    });
};

// Access shared props from Inertia
const page = usePage();
</script>

<template>
    <GuestLayout>
        <Head title="Redeem Voucher" />

        <!-- Flash Message for Success -->
        <div v-if="page.props.flash.message" class="p-4 mb-4 text-green-700 bg-green-100 rounded-lg">
            {{ page.props.flash.message }}
        </div>

        <!-- Flash Message for Warning -->
        <div v-if="page.props.flash.warning" class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg">
            {{ page.props.flash.warning }}
        </div>

        <!-- Display Voucher Data if Available -->
        <div v-if="usePage().props.flash.data" class="p-4 mb-4 bg-blue-100 text-blue-700 rounded-lg">
            <p>Voucher Code: <strong>{{ usePage().props.flash.data.code }}</strong></p>
            <p>Amount Disbursed: <strong>₱{{ usePage().props.flash.data.amount }}</strong></p>
            <p v-if="usePage().props.flash.data.mobile">Disbursed To: <strong>{{ usePage().props.flash.data.mobile }}</strong></p>
        </div>

        {{ usePage().props.flash }}
        <form @submit.prevent="submit" class="space-y-4">
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
            </div>

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

            <div>
                <InputLabel for="country" value="Country Code" />

                <TextInput
                    id="country"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.country"
                    placeholder="e.g., PH"
                />

                <InputError class="mt-2" :message="form.errors.country" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Redeem Voucher
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
