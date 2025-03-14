<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {ref, computed, watch, onMounted} from 'vue';
import { debounce } from 'lodash';

// Define props with default values
const props = defineProps({
    country: {
        type: String,
        default: 'PH',
    },
    inputs: {
        type: String,
        default: '',
    },
    rider: {
        type: String,
        default: '',
    },
    referenceLabel: {
        type: String,
        default: 'Agent',
    },
});

// Extract URL search parameters
const params = new URLSearchParams(window.location.search);

// Parse the `inputs` parameter from the URL (expected to be a JSON string)
let parsedInputs = {};
try {
    parsedInputs = JSON.parse(params.get('inputs')) ?? {};
} catch (e) {
    console.warn('Invalid JSON format for inputs parameter');
}

// Fallback to props.inputs if parsedInputs is empty or invalid
let fallbackInputs = {};
try {
    fallbackInputs = JSON.parse(props.inputs) ?? {};
} catch (e) {
    console.warn('Invalid JSON format for props.inputs, using empty object');
}

// Initialize form with default values from URL parameters or props
const form = useForm({
    voucher_code: params.get('voucher_code') ?? '',
    mobile: params.get('mobile') ?? '',
    country: params.get('country') ?? props.country,
    inputs: Object.keys(parsedInputs).length ? parsedInputs : fallbackInputs, // Prioritize parsedInputs
    rider: params.get('rider') ?? props.rider,
    feedback: params.get('feedback')
});

// Dynamic labels with URL param precedence
const dynamicMetaLabel = computed(() => params.get('metaLabel') ?? props.metaLabel);
const dynamicReferenceLabel = computed(() => params.get('referenceLabel') ?? props.referenceLabel);

// Dynamic placeholder for the meta field
const dynamicMetaPlaceholder = computed(() => `Enter ${dynamicMetaLabel.value}`);

// Status and messages
const isCheckingStatus = ref(false);
const statusMessage = ref('');
const referenceMessage = computed(() => {
    const reference = form.inputs?.reference ?? '';
    return reference ? `${dynamicReferenceLabel.value}: ${reference}` : '';
});

// Voucher details message
const voucherDetailsMessage = ref('');

// Polling variables
let pollInterval = null;
const maxPollingTime = 60000; // Stop polling after 60 seconds

// Start polling for voucher redemption status
const startPolling = (voucherCode) => {
    isCheckingStatus.value = true;

    // Set a polling interval every 3 seconds
    pollInterval = setInterval(() => {
        axios.get(route('old-redeem.show', { voucher: voucherCode }))
            .then((response) => {
                if (response.data.status === 'completed') {
                    setStatusMessage('✅ Cash disbursed successfully!');
                    stopPolling(true);
                    // setTimeout(() => {
                    //     form.get(route('rider'));
                    // }, 1500); // Redirect after 3 seconds
                } else if (response.data.status === 'pending') {
                    setStatusMessage('🟢 Voucher redeemed. Waiting for disbursement...');
                } else {
                    setStatusMessage('⚠️ Unexpected status received. Stopping polling.');
                    stopPolling(true);
                }
            })
            .catch(() => {
                setStatusMessage('❌ Error checking voucher status. Please try again.');
                stopPolling(true);
            });
    }, 3000);

    // Automatically stop polling after a maximum duration
    setTimeout(() => {
        if (isCheckingStatus.value) {
            setStatusMessage('⏳ Polling timed out. Please try again.');
            stopPolling(true);
        }
    }, maxPollingTime);
};

// Stop polling and reset the form if needed
const stopPolling = (resetForm = false) => {
    isCheckingStatus.value = false;
    clearInterval(pollInterval);
    pollInterval = null;
    if (resetForm) {
        form.reset('voucher_code', 'mobile', 'country');
    }
};

// Submit the form
const submit = () => {
    form.get(route('redeem.show', {voucher: form.voucher_code}), {
        onFinish: () => {
            setStatusMessage('⏳ Processing... Please wait.');
            startPolling(form.voucher_code);
        },
    });
};

// Set status message and maintain visibility
const setStatusMessage = (message) => {
    statusMessage.value = message;
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
                voucherDetailsMessage.value = '⚠️ Invalid or expired voucher code.';
            }
        })
        .catch(() => {
            voucherDetailsMessage.value = '❌ Error retrieving voucher details.';
        });
}, 500);

// Watch for changes to the voucher code input
watch(() => form.voucher_code, fetchVoucherDetails);

const toTitleCase = (str) => {
    if (!str || typeof str !== 'string') return ''; // Ensure str is a non-empty string
    return str.replace(/([a-z])([A-Z])/g, '$1 $2') // camelCase to spaced
        .replace(/_/g, ' ') // snake_case to spaced
        .replace(/\b\w/g, (char) => char.toUpperCase()); // Capitalize each word
};

// Function to get the current GPS coordinates
const getLocation = () => {
    if (!navigator.geolocation) {
        console.warn('Geolocation is not supported by this browser.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            const coordinates = `${latitude},${longitude}`;
            let locationName = 'Unknown Location';

            try {
                // Reverse geocode using the OpenCage Geocoding API (or any preferred API)
                const response = await axios.get('https://api.opencagedata.com/geocode/v1/json', {
                    params: {
                        q: coordinates,
                        key: 'd25e833a933c446d8964746b531d53f4', // Replace with your actual API key
                    },
                });

                if (response.data.results.length > 0) {
                    locationName = response.data.results[0].formatted;
                }
            } catch (error) {
                console.error('Error getting location name:', error);
            }

            // Concatenate the name with the coordinates and update the form
            form.inputs.location = `${locationName} (${coordinates})`;
        },
        (error) => {
            console.error('Error getting location:', error.message);
        }
    );
};

onMounted(() => {
    if ('location' in form.inputs) {
        getLocation();
    }

    // Initialize 'signature' as an empty string only if the search param exists
    if (params.has('signature') && !('signature' in form.inputs)) {
        form.inputs.signature = '';
    }
});

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

            <hr class="bevel-line" />

            <!-- Dynamic Fields -->
            <div v-for="(value, key) in form.inputs" :key="key">
                <template v-if="key === 'location'">
                    <InputLabel :for="key" :value="toTitleCase(key)" />
                    <TextInput
                        :id="key"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.inputs[key]"
                        placeholder="Fetching your location..."
                        readonly
                    />
                    <InputError class="mt-2" />
                </template>

                <!-- Render only non-signature and non-reference fields -->
                <template v-else-if="key !== 'reference' && key !== 'signature'">
                    <InputLabel :for="key" :value="toTitleCase(key)" />
                    <TextInput
                        :id="key"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.inputs[key]"
                        required
                        :placeholder="`Enter ${toTitleCase(key)}`"
                    />
                    <InputError class="mt-2" />
                </template>

<!--                <template v-else-if="key !== 'reference'">-->
<!--                    <InputLabel :for="key" :value="toTitleCase(key)" />-->
<!--                    <TextInput-->
<!--                        :id="key"-->
<!--                        type="text"-->
<!--                        class="mt-1 block w-full"-->
<!--                        v-model="form.inputs[key]"-->
<!--                        required-->
<!--                        :placeholder="`Enter ${toTitleCase(key)}`"-->
<!--                    />-->
<!--                    <InputError class="mt-2" />-->
<!--                </template>-->
            </div>

            <div class="mt-4 flex items-center justify-between gap-2">
                <!-- Reference Message aligned to the left -->
                <span class="text-sm text-gray-700 font-semibold min-w-[200px]">{{ referenceMessage || '\u00A0' }}</span>
                <!-- Redeem Voucher Button -->
                <PrimaryButton
                    class="ms-auto"
                    :class="{ 'opacity-25': form.processing || isCheckingStatus }"
                    :disabled="form.processing || isCheckingStatus"
                >
                    Redeem Voucher
                </PrimaryButton>
            </div>

            <!-- Permanent Status Message Area -->
            <div class="mt-4 min-h-[40px]">
                <p class="text-sm text-gray-500">
                    {{ statusMessage }}
                </p>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.bevel-line {
    border: 0;
    height: 1px;
    background: linear-gradient(to right, #d1d5db, #f9fafb, #d1d5db);
    margin: 24px 0;
}
</style>
