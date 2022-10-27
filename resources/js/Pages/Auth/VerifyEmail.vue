<template>
    <Head title="Επιβεβαίωση διεύθυνσης Ηλ. Ταχυδρομείου" />

    <div class="mb-4 text-sm text-gray-600">
        Ευχαριστούμε για την εγγραφή σας! Για την ενεργοποίηση της εγγραφής κάντε κλικ στον σύνδεσμο που σας έχει αποσταλλεί με Ηλ. Ταχυδρομέιο. Αν δεν λάβατε το μήνυμα ευχαρίστως θα σας στείλουμε ένα νέο.
    </div>

    <div class="mb-4 font-medium text-sm text-green-600" v-if="verificationLinkSent" >
        Ένας νέος σύνδεσμος επιβεβαίωσης στάλθηκε στη διεύθυνση Ηλ. Ταχυδρομείου που καταχωρίσατε κατά την εγγραφή σας.
    </div>

    <form @submit.prevent="submit">
        <div class="mt-4 flex items-center justify-between">
            <BreezeButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Επαναποστολή Ηλ. Μηνύματος επιβεβαίωσης
            </BreezeButton>

            <Link :href="route('logout')" method="post" as="button" class="underline text-sm text-gray-600 hover:text-gray-900">Log Out</Link>
        </div>
    </form>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeGuestLayout from '@/Layouts/Guest.vue'
import { Head, Link } from '@inertiajs/inertia-vue3';

export default {
    layout: BreezeGuestLayout,

    components: {
        BreezeButton,
        Head,
        Link,
    },

    props: {
        status: String,
    },

    data() {
        return {
            form: this.$inertia.form()
        }
    },

    methods: {
        submit() {
            this.form.post(this.route('verification.send'))
        },
    },

    computed: {
        verificationLinkSent() {
            return this.status === 'verification-link-sent';
        }
    }
}
</script>
