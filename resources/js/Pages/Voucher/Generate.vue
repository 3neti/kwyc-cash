<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
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
    tariffAmount: {
        type: Number,
        default: 25, // Default tariff in major units
    },
});

// Form state with default values
const form = useForm({
    value: props.defaultVoucherValue,
    qty: 1,
    tag: '',
});

// **Form for assigning mobile numbers to vouchers**
const mobileForm = useForm({
    mobile: "",
    voucher_code: "",
    amount: 0,
    errors: {} // Store errors per voucher
});

const user = usePage().props.auth.user;
const voucherCodes = ref([]);
const statusMessage = ref('');

// Get current balance from props
const userBalance = computed(() => usePage().props.auth.user.balanceFloat ?? 0);

// Format balance as currency
const formatter = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
});
const formattedBalance = computed(() => formatter.format(userBalance.value));

// Calculate total voucher cost including tariff
const totalVoucherCost = computed(() => {
    const value = Number(form.value) || 0;
    const qty = Number(form.qty) || 0;
    const tariff = Number(props.tariffAmount) || 0;
    return (value + tariff) * qty;
});

// Button visibility based on balance check
const canGenerateVouchers = computed(() => {
    return totalVoucherCost.value <= userBalance.value;
});

// Clear status message after 3 seconds
watch(statusMessage, (newMessage) => {
    if (newMessage) {
        setTimeout(() => {
            statusMessage.value = '';
        }, 3000);
    }
});

const submit = () => {
    form.post(route('vouchers.store'), {
        replace: true, // Ensures partial update instead of full reload
        preserveScroll: true, // Keeps scroll position when updating UI
        onSuccess: (response) => {
            console.log("Generated vouchers:", response.props.flash.data); // Debugging

            if (response.props.flash.data?.length > 0) {
                voucherCodes.value = response.props.flash.data.map(voucher => ({
                    code: voucher.code,
                    amount: voucher.amount ?? form.value,
                    mobile: '',
                    status: 'unattached',
                }));
            }

            // voucherCodes.value = response.props.flash.data?.map(voucher => ({
            //     code: voucher.code ?? null,  // Ensure property exists
            //     amount: voucher.amount ?? form.value, // Ensure amount exists
            //     mobile: '',
            //     status: 'unattached',
            // })) ?? [];

            console.log("voucherCodes after update:", voucherCodes.value);
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

// Method to send mobile, voucher code, and amount to the controller
// **Handle action click using mobileForm**

const handleActionClick = (voucher) => {
    console.log("Voucher object before form submission:", voucher);

    if (!voucher.mobile) {
        console.warn("Mobile number is missing for voucher:", voucher);
        return;
    }

    if (!voucher.code || !voucher.amount) {
        console.error("Voucher is missing required data:", voucher);
        return;
    }

    mobileForm.mobile = voucher.mobile;
    mobileForm.voucher_code = voucher.code;
    mobileForm.amount = voucher.amount;

    console.log("Form Data before submit:", mobileForm); // Debugging

    mobileForm.post(route('voucher.action'), {
        preserveState: true, // Prevents unnecessary resets in Vue reactivity
        preserveScroll: true, // Keeps scroll stable after submission
        onSuccess: () => {
            statusMessage.value = "Voucher assigned successfully!";
            setTimeout(() => {
                statusMessage.value = '';
            }, 5000);
        },
        onError: (errors) => {
            console.error("Validation Errors:", errors);
            if (errors.mobile) {
                mobileForm.errors = {
                    ...mobileForm.errors,
                    [voucher.code]: errors.mobile // Assign error to specific voucher
                };
            }
        }
    });
};

// Redirect to Load Credits page
const redirectToLoadCredits = () => {
    router.get(route('wallet.create'));
};

watch(
    () => usePage().props.flash.event,
    (event) => {
        if (event?.name === 'contact_attached') {
            console.log('Event received:', event);

            const { voucher_code } = event.data;
            console.log('Voucher Code:', voucher_code);

            // Find the voucher in the list
            const index = voucherCodes.value.findIndex(v => v.code === voucher_code);
            if (index !== -1) {
                voucherCodes.value[index].status = 'attached';
                statusMessage.value = `✅ Contact attached to ${voucher_code}!`;

                // Clear message after 5 seconds
                setTimeout(() => {
                    statusMessage.value = '';
                }, 5000);
            }
        }
    }
);

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
                                <span v-else class="invisible">Placeholder</span> <!-- Invisible Placeholder -->
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

                            <!-- Display total voucher cost including tariff and validate against balance -->
                            <div class="flex justify-between items-center mb-4">
                                <div class="text-sm text-gray-600">
                                    Total Voucher Cost (Incl. Tariff): <span class="text-green-600">{{ formatter.format(totalVoucherCost) }}</span>
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

                        <div v-if="voucherCodes.length" class="mt-6 p-4 bg-green-100 rounded-md">
                            <p class="text-green-800 font-semibold mb-4">Generated Voucher Codes:</p>
                            <!-- Table of Voucher Codes with Status -->
                            <table class="min-w-full bg-white border mt-6">
                                <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-4 py-2 border">Voucher Code</th>
                                    <th class="px-4 py-2 border">Amount</th>
                                    <th class="px-4 py-2 border">Actions</th>
                                    <th class="px-4 py-2 border">Mobile</th>
                                    <th class="px-4 py-2 border">Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(voucher, index) in voucherCodes" :key="voucher.code" class="border-t">
                                    <td class="px-4 py-2 border text-sm">{{ voucher.code }}</td>
                                    <td class="px-4 py-2 border text-sm">{{ formatter.format(form.value) }}</td>
                                    <td class="px-4 py-2 border text-sm text-center">
                                        <PrimaryButton
                                            class="bg-blue-500 hover:bg-blue-600"
                                            @click="handleActionClick(voucher)"
                                        >
                                            Submit
                                        </PrimaryButton>
                                    </td>
                                    <td class="px-4 py-2 border text-sm">
                                        <input
                                            v-model="voucher.mobile"
                                            type="text"
                                            placeholder="Enter mobile number"
                                            class="w-full border border-gray-300 p-1 rounded"
                                        />
                                        <InputError class="mt-1 text-red-500" :message="mobileForm.errors[voucher.code]" />
                                    </td>

                                    <td class="px-4 py-2 border text-sm text-center">
                                        <span :class="voucher.status === 'attached' ? 'text-green-600' : 'text-red-600'">
                                            {{ voucher.status === 'attached' ? 'Assigned' : 'Unassigned' }}
                                        </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
