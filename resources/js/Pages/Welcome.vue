<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
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
                                v-if="canLogin"
                                :href="route('login')"
                                class="text-lg text-[#FF2D20] hover:text-[#FF2D20]/70"
                            >
                                Log in
                            </Link>

                            <Link
                                v-if="canRegister"
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
                                The platform offers functionalities for:
                            </p>
                            <ul class="list-disc list-inside text-gray-600 mb-4">
                                <li>Create and manage cash vouchers.</li>
                                <li>Load credits to user wallets securely.</li>
                                <li>Allow voucher redemption through GCash.</li>
                                <li>Monitor voucher status and transaction history.</li>
                            </ul>
                            <p class="text-gray-700">
                                This system is ideal for businesses and organizations looking to streamline
                                cash disbursement processes and provide a seamless experience for voucher recipients.
                            </p>
                        </div>

                        <div class="p-8 bg-white shadow-lg rounded-lg">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-4">
                                Key Features
                            </h2>
                            <ul class="space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500">✓</span>
                                    <span>Create multiple vouchers in a single transaction.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500">✓</span>
                                    <span>Load credits directly to user wallets.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500">✓</span>
                                    <span>Allow bearers to redeem cash through GCash.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-green-500">✓</span>
                                    <span>View and track voucher redemption status.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-12 flex gap-6 justify-center">
                        <Link
                            :href="route('vouchers.create')"
                            class="px-6 py-3 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 transition"
                        >
                            Create Vouchers
                        </Link>

                        <Link
                            :href="route('wallet.create')"
                            class="px-6 py-3 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 transition"
                        >
                            Load Wallet
                        </Link>

                        <Link
                            :href="route('redeem.create')"
                            class="px-6 py-3 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 transition"
                        >
                            Redeem Voucher
                        </Link>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>
