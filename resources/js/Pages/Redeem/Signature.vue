<script setup lang="ts">
import { VueSignaturePad } from "@selemondev/vue3-signature-pad";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    payload: Object,
});

// Initialize the form with the payload and add a signature field
const form = useForm({
    ...props.payload,
    signature_data: '',
});

const state = ref({
    options: {
        penColor: 'rgb(0, 0, 0)',
        backgroundColor: 'rgb(255, 255, 255)'
    },
    disabled: false,
});

const signaturePad = ref();

const handleSave = () => {
    form.signature_data = signaturePad.value?.saveSignature() || '';
    if (form.signature_data) {
        submit();
    } else {
        alert("Please provide a signature.");
    }
};

const handleClear = () => {
    signaturePad.value?.clearCanvas();
};

const handleUndo = () => {
    signaturePad.value?.undo();
};

// Toggle the disabled state of the signature pad
const handleDisabled = () => {
    state.value.disabled = !state.value.disabled;
};

// Submit the form with the signature data
const submit = () => {
    form.post(route('signature.store'), {
        onFinish: () => {}
    });
};
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div class="w-full max-w-[600px] bg-white p-4 rounded-lg shadow-md flex flex-col items-center space-y-4">
            <div class="w-full aspect-square bg-gray-50 border border-gray-300 rounded-md overflow-hidden">
                <VueSignaturePad
                    ref="signaturePad"
                    class="w-full h-full"
                    :maxWidth="2"
                    :minWidth="2"
                    :disabled="state.disabled"
                    :options="{
                        penColor: state.options.penColor,
                        backgroundColor: state.options.backgroundColor
                    }"
                />
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap justify-center gap-2">
                <button
                    type="button"
                    @click="handleSave"
                    class="px-4 py-2 bg-green-500 text-white rounded-md w-full sm:w-auto"
                >
                    Save & Submit
                </button>
                <button
                    type="button"
                    @click="handleClear"
                    class="px-4 py-2 bg-red-500 text-white rounded-md w-full sm:w-auto"
                >
                    Clear
                </button>
                <button
                    type="button"
                    @click="handleUndo"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md w-full sm:w-auto"
                >
                    Undo
                </button>
                <button
                    type="button"
                    @click="handleDisabled"
                    class="px-4 py-2 bg-gray-500 text-white rounded-md w-full sm:w-auto"
                >
                    Toggle Disabled
                </button>
            </div>
        </div>
    </div>
</template>
