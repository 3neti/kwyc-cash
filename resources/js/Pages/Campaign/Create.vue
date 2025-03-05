<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import { computed, ref, watch, onMounted } from 'vue';
import QRCode from 'qrcode';

// Define props with default values
const props = defineProps({
    inputs: {
        type: String,
        default: '',
    }
});

// Form state with default values
const form = ref({
    voucher_code: '',
    mobile: '',
    country: 'PH',
    referenceLabel: '',
    inputs: props.inputs
});

// Show optional fields toggle
const showOptionalFields = ref(false);
const generatedLink = ref('');
const qrCodeDataUrl = ref('');

// Generate the link based on form inputs
const generateLink = () => {
    const baseUrl = `${window.location.origin}${route('redeem.create', {}, false)}`;
    const params = new URLSearchParams();

    if (form.value.voucher_code) params.append('voucher_code', form.value.voucher_code);
    if (form.value.mobile) params.append('mobile', form.value.mobile);
    if (form.value.country) params.append('country', form.value.country);
    if (form.value.referenceLabel) params.append('referenceLabel', form.value.referenceLabel);

    // Properly append the inputs as a JSON string if it's a valid object-like string
    if (form.value.inputs) {
        try {
            // Validate and stringify the inputs safely
            const inputsObject = JSON.parse(form.value.inputs.replace(/'/g, '"'));
            params.append('inputs', JSON.stringify(inputsObject));
        } catch (e) {
            console.error('Invalid inputs JSON format', e);
        }
    }

    generatedLink.value = `${baseUrl}?${params.toString()}`;
    generateQRCode();
};

// Generate the QR Code from the generated link
const generateQRCode = async () => {
    try {
        qrCodeDataUrl.value = await QRCode.toDataURL(generatedLink.value, {
            width: 200,
            margin: 2,
        });
    } catch (error) {
        console.error('Error generating QR code:', error);
    }
};

// Automatically generate the link and QR code when form inputs change
watch(form, generateLink, { deep: true });

// Automatically generate the QR code on page load
onMounted(() => {
    generateLink();
});

// Toggle visibility of optional fields
const toggleOptionalFields = () => {
    showOptionalFields.value = !showOptionalFields.value;
};

// Function to download the QR code image
const downloadQRCode = () => {
    if (!qrCodeDataUrl.value) return;

    const link = document.createElement('a');
    link.href = qrCodeDataUrl.value;
    link.download = 'campaign-qr-code.png';
    link.click();
};

// Reactive state to track JSON validation
const isJsonValid = ref(true);
const parsedInputs = ref({});

// Watcher to validate and parse the inputs as JSON
watch(() => form.value.inputs, (newVal) => {
    if (!newVal) {
        isJsonValid.value = true;
        parsedInputs.value = {};
        return;
    }

    try {
        // Attempt to parse as JSON and validate
        parsedInputs.value = JSON.parse(newVal.replace(/'/g, '"'));
        isJsonValid.value = true;
    } catch (e) {
        isJsonValid.value = false;
        parsedInputs.value = {};
        console.error('Invalid JSON format:', e);
    }
});

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Campaign
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- QR Code and Link Display -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div class="text-center">
<!--                        <h3 class="text-lg font-semibold mb-2">Campaign</h3>-->
                        <p class="text-blue-500 font-medium mb-4 break-all">
                            <a :href="generatedLink" target="_blank" class="underline" :title="generatedLink">
                                Claim Here
                            </a>
                        </p>
                        <div v-if="qrCodeDataUrl" class="mt-4">
                            <img
                                :src="qrCodeDataUrl"
                                alt="Campaign QR Code"
                                class="mx-auto"
                            />
                            <PrimaryButton class="mt-4" @click="downloadQRCode">
                                Download QR Code
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form class="space-y-6">
                        <!-- Inputs Field -->
                        <div>
                            <InputLabel for="inputs" value="Inputs" />
                            <TextInput
                                id="inputs"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.inputs"
                                placeholder="Enter inputs as JSON"
                                :class="{ 'border-red-500': !isJsonValid }"
                            />
                            <InputError class="mt-2" :message="!isJsonValid ? 'Invalid JSON format' : ''" />
                        </div>

                        <!-- Optional Fields Toggle -->
                        <div class="flex items-center space-x-2 mt-4">
                            <input
                                type="checkbox"
                                id="toggleOptionalFields"
                                v-model="showOptionalFields"
                                class="form-checkbox"
                            />
                            <label for="toggleOptionalFields" class="text-sm text-gray-700 cursor-pointer">
                                Show Optional Fields
                            </label>
                        </div>

                        <!-- Optional Fields -->
                        <div v-if="showOptionalFields" class="space-y-6 mt-4">
                            <div>
                                <InputLabel for="voucher_code" value="Voucher Code" />
                                <TextInput
                                    id="voucher_code"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.voucher_code"
                                    placeholder="Enter voucher code"
                                />
                            </div>

                            <div>
                                <InputLabel for="mobile" value="Mobile Number" />
                                <TextInput
                                    id="mobile"
                                    type="tel"
                                    class="mt-1 block w-full"
                                    v-model="form.mobile"
                                    placeholder="e.g., 09171234567"
                                />
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
                            </div>

                            <!-- Reference Label Field -->
                            <div>
                                <InputLabel for="referenceLabel" value="Reference Label" />
                                <TextInput
                                    id="referenceLabel"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.referenceLabel"
                                    placeholder="e.g., Agent Name"
                                />
                                <InputError class="mt-2" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
