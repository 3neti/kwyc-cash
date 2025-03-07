<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Default deposit amount for unauthenticated users
const defaultAmount = 50;
const defaultAccount = '09468251991';

const qrCode = ref(null);
const statusMessage = ref('');

// Generate the QR code for guest wallet loading
const generateQRCode = () => {
    axios
        .get(route('wallet.qr-code'), {
            params: { amount: defaultAmount, account: defaultAccount },
        })
        .then(({ data }) => {
            if (data.success) {
                qrCode.value = data.qr_code;
                statusMessage.value = 'QR code generated successfully.';
                setTimeout(() => {
                    statusMessage.value = '';
                }, 3000);
            } else {
                statusMessage.value = data.message || 'Failed to generate QR code.';
            }
        })
        .catch(() => {
            statusMessage.value = 'Error occurred while generating QR code.';
        });
};

// Auto-generate the QR code on page load
onMounted(() => {
    generateQRCode();
});

// Computed property for formatting the amount
const formattedAmount = computed(() =>
    new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(defaultAmount)
);

// Download the QR code as an image
const downloadQRCode = () => {
    if (!qrCode.value) return;

    const link = document.createElement('a');
    link.href = qrCode.value;
    link.download = `QR_Code_Deposit_${formattedAmount.value}.png`;
    link.click();
};

Echo.channel(`mobile`)
    .listen('.deposit.confirmed', (event) => {
        console.log(event);

        // Automatically trigger login by sending a request to the mobile login endpoint
        axios.post(route('auth.login-by-mobile'), { mobile: event.mobile })
            .then(response => {
                console.log('Logged in successfully');
                console.log(response.data);

                // Redirect to the specified route from the server response
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    window.location.href = route('dashboard');
                }
            })
            .catch(error => {
                console.error('Login by mobile failed', error);
            });
    });
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="flex justify-between items-center py-10">
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
                        Cash Voucher Management
                    </h1>
                    <nav class="flex gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="text-lg text-[#FF2D20] hover:text-[#FF2D20]/70"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="text-lg text-[#FF2D20] hover:text-[#FF2D20]/70"
                            >
                                Log in
                            </Link>
                            <Link
                                :href="route('register')"
                                class="text-lg text-[#FF2D20] hover:text-[#FF2D20]/70"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                </header>

                <main class="mt-12">
                    <div class="grid gap-8 lg:grid-cols-2">
                        <div class="p-8 bg-white shadow-lg rounded-lg">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-4">
                                What is Cash Voucher Management?
                            </h2>
                            <p class="text-gray-700 mb-4">
                                Our application allows organizations to create and manage cash vouchers
                                that can be redeemed by authorized bearers through GCash, a leading money transfer service in the Philippines.
                            </p>
                        </div>

                        <div class="p-8 bg-white shadow-lg rounded-lg">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-4">
                                Load Wallet to Sign In
                            </h2>

                            <div v-if="qrCode" class="text-center">
                                <h3 class="text-lg font-semibold mb-2">
                                    Scan QR Code to Load Wallet
                                </h3>
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ formattedAmount }}
                                </p>
                                <img
                                    :src="qrCode"
                                    alt="Wallet Load QR Code"
                                    class="mx-auto mt-4"
                                />
                                <div class="flex justify-center mt-4">
                                    <PrimaryButton
                                        class="bg-green-500 hover:bg-green-600"
                                        @click="downloadQRCode"
                                    >
                                        Download QR Code
                                    </PrimaryButton>
                                </div>
                            </div>

                            <p v-else class="text-sm text-gray-500">
                                Generating QR Code...
                            </p>
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Laravel v{{ $page.props.laravelVersion }} (PHP v{{ $page.props.phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>
