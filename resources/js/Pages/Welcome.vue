<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Default deposit amount for unauthenticated users
const defaultAmount = 50;
const defaultAccount = '09178251991';

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

// Listen for deposit confirmation for known users
Echo.channel(`mobile`)
    .listen('.deposit.confirmed', (event) => {
        console.log('Deposit confirmed for known user:', event);

        axios.post(route('auth.login-by-mobile'), { mobile: event.mobile })
            .then(response => {
                console.log('Logged in successfully');
                window.location.href = response.data.redirect ?? route('dashboard');
            })
            .catch(error => {
                console.error('Login by mobile failed', error);
            });
    });

// Listen for deposit confirmation from unknown mobile numbers
Echo.channel(`mobile`)
    .listen('.deposit.confirmed-from-unknown-mobile', (event) => {
        console.log('Deposit confirmed for unknown mobile:', event);

        axios.post(route('auth.register-by-mobile'), {
            mobile: event.mobile,
            amount: event.amount,
            name: event.name
        })
            .then(response => {
                console.log('User registered and logged in successfully');
                window.location.href = response.data.redirect ?? route('dashboard');
            })
            .catch(error => {
                console.error('Registration by mobile failed', error);
            });
    });
</script>

<template>
    <Head title="Welcome" />
    <div class="flex min-h-screen flex-col items-center bg-gray-50 pt-6 sm:justify-center sm:pt-0">
        <div class="w-full max-w-[600px] bg-white p-6 rounded-lg shadow-md flex flex-col items-center space-y-6">
            <!-- App Name -->
            <h1 class="text-3xl font-bold text-gray-800 mb-4 text-center">
                {{ usePage().props.app.name }}
            </h1>

            <!-- Page Title -->
            <h2 class="text-2xl font-semibold text-gray-900 mb-4 text-center">
                Scan GCash To Enter
            </h2>

            <div class="flex flex-col items-center space-y-2">
                <!-- QR Code (20% smaller) -->
                <div class="w-full flex justify-center">
                    <img
                        v-if="qrCode"
                        :src="qrCode"
                        alt="QR Code"
                        class="w-[288px] h-[288px] max-w-none"
                    />
                </div>

                <!-- Formatted Amount - Smaller & Right-Aligned -->
                <p v-if="qrCode" class="text-sm font-medium text-blue-600 text-center">
                    {{ formattedAmount }}
                </p>
            </div>

<!--            &lt;!&ndash; Amount Display &ndash;&gt;-->
<!--            <p v-if="qrCode" class="text-2xl font-bold text-blue-600 mb-4 text-center">-->
<!--                {{ formattedAmount }}-->
<!--            </p>-->

<!--            &lt;!&ndash; QR Code Display &ndash;&gt;-->
<!--            <div v-if="qrCode" class="w-full aspect-square bg-gray-50 border border-gray-300 rounded-md flex items-center justify-center">-->
<!--                <img-->
<!--                    :src="qrCode"-->
<!--                    alt="Wallet Load QR Code"-->
<!--                    class="max-w-full max-h-full p-2 bg-white rounded-lg"-->
<!--                />-->
<!--            </div>-->

            <!-- Download QR Code Button -->
            <PrimaryButton
                v-if="qrCode"
                class="bg-green-500 hover:bg-green-600 w-full flex justify-center"
                @click="downloadQRCode"
            >
                Download QR Code
            </PrimaryButton>

            <!-- Status Message -->
            <p v-else class="text-sm text-gray-500 text-center">
                Generating QR Code...
            </p>
        </div>

        <!-- Footer -->
        <footer class="py-16 text-center text-sm text-black dark:text-white/70">
            {{ usePage().props.footer.message }}
        </footer>
    </div>
</template>
