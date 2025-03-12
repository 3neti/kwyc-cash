<script setup>
import { onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    voucher: Object
});

const countdown = ref(5); // Initialize countdown at 5 seconds

// Start countdown and redirect when it reaches 0
onMounted(() => {
    const interval = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(interval);
            router.get(route('rider', {voucher: props.voucher.code })); // Redirect after countdown
        }
    }, 1000);
});
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0">
        <div class="w-full max-w-[600px] bg-white p-4 rounded-lg shadow-md flex flex-col items-center space-y-4">
            <div class="text-center text-red-600 font-bold text-2xl">
                Voucher code is not assigned to your mobile number!
            </div>
            <p class="text-gray-500 text-sm">
                Redirecting in {{ countdown }} seconds...
            </p>
        </div>
    </div>
</template>
