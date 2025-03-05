<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import { ref, watch, onMounted } from 'vue';
import QRCode from 'qrcode';

// Form state with default values
const form = ref({
    voucher_code: '',
    mobile: '',
    country: 'PH',
    meta: '',
    reference: '',
    metaLabel: '',
    referenceLabel: '',
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
    if (form.value.meta) params.append('meta', form.value.meta);
    if (form.value.reference) params.append('reference', form.value.reference);
    if (form.value.metaLabel) params.append('metaLabel', form.value.metaLabel);
    if (form.value.referenceLabel) params.append('referenceLabel', form.value.referenceLabel);

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
                        <h3 class="text-lg font-semibold mb-2">Shareable Campaign Link</h3>
                        <p class="text-blue-500 font-medium mb-4 break-all">
                            <a :href="generatedLink" target="_blank">{{ generatedLink }}</a>
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
                        <!-- Meta Label Field -->
                        <div>
                            <InputLabel for="metaLabel" value="Meta Label" />
                            <TextInput
                                id="metaLabel"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.metaLabel"
                                placeholder="e.g., Name"
                            />
                            <InputError class="mt-2" />
                        </div>

                        <!-- Meta Field -->
                        <div>
                            <InputLabel for="meta" value="Meta Default Value" />
                            <TextInput
                                id="meta"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.meta"
                                required
                                placeholder="Enter default meta value"
                            />
                            <InputError class="mt-2" />
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

                        <!-- Reference Field -->
                        <div>
                            <InputLabel for="reference" value="Reference Default Value" />
                            <TextInput
                                id="reference"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.reference"
                                required
                                placeholder="Enter default reference value"
                            />
                            <InputError class="mt-2" />
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
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
