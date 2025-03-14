<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from "@/Components/Checkbox.vue";

const page = usePage();
const user = page.props.auth.user;
const campaign = user.current_campaign;

const form = useForm({
    name: campaign.name,
    disabled: campaign.disabled,
    errors: {},
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Current Campaign
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your current campaign information.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('campaign.update', { campaign: campaign.id }))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="disabled" value="Disabled" />

                <Checkbox
                    id="disabled"
                    v-model:checked="form.disabled"
                />

                <InputError class="mt-2" :message="form.errors.disabled" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing || isSaveDisabled">
                    Save
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
