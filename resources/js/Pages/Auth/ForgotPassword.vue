<template>
    <Head title="Ξέχασα το συνθηματικό" />

    <div class="mb-4 text-sm text-gray-600">
        Ξεχάσατε το συνθηματικό σας; ΔΕν υπάρχει πρόβλημα. Πληκτρολογείστε τη διεύθυνση Ηλ. Ταχυδρομείου σας και θα σας στείλουμε ένα σύνδεσμο για να δημιουργήσετε ένα νέο.
    </div>

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
        {{ status }}
    </div>

    <BreezeValidationErrors class="mb-4" />

    <form @submit.prevent="submit">
        <div>
            <BreezeLabel for="email" value="Ηλ. Ταχυδρομείο" />
            <BreezeInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <BreezeButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Αποστολή συνδέσμου επαναφοράς
            </BreezeButton>
        </div>
    </form>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeGuestLayout from '@/Layouts/Guest.vue'
import BreezeInput from '@/Components/Input.vue'
import BreezeLabel from '@/Components/Label.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { Head } from '@inertiajs/inertia-vue3';

export default {
    layout: BreezeGuestLayout,

    components: {
        BreezeButton,
        BreezeInput,
        BreezeLabel,
        BreezeValidationErrors,
        Head,
    },

    props: {
        status: String,
    },

    data() {
        return {
            form: this.$inertia.form({
                email: ''
            })
        }
    },

    methods: {
        submit() {
            this.form.post(this.route('password.email'))
        }
    }
}
</script>
