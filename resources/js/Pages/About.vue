<template>
  <Head title="Πληροφορίες" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Πληροφορίες
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div
            class="p-6 bg-white border-b border-gray-200 space-y-6 text-justify"
          >
            <p>
              Ο Ηλεκτρονικός Απουσιολόγος δημιουργήθηκε με σκοπό<br />
              <b>α)</b> την καταγραφή των απουσιών των μαθητών
              <b>κάθε ώρα σε πραγματικό χρόνο</b><br />
              <b>β)</b> την παροχή
              <b>άμεσης εποπτείας των απόντων μαθητών</b> από την 1η ώρα και
              κάθε ώρα<br />
              <b>γ)</b> την <b>εισαγωγή των απουσιών στο myschool</b> άμα τη
              λήξη των μαθημάτων (εξαγωγή αρχείου xls)
            </p>
            <p>
              Ακολούθησε ο εμπλουτισμός του με τα ακόλουθα:<br />
              α) <b>Προγραμματιστής Διαγωνισμάτων</b>. Οι καθηγητές σε ένα
              εύχρηστο Ημερολόγιο δηλώνουν τα διαγωνίσματά τους.<br />
              β) <b>Καταχώριση Βαθμολογίας</b>. Καταχωρίζεται η βαθμολογία ανά
              τμήμα - μάθημα. Εξάγεται αρχείο 187.xls για εισαγωγή στο myschool.
            </p>
            <p>
              Περισσοτερες πληροφορίες για τη ρύθμιση και χρήση
              <a class="underline" :href="this.infoUrl" target="_blank">εδώ </a
              >.
            </p>
            <p>
              Χρησιμοποιεί τα δημοφιλή framework
              <a class="underline" href="https://laravel.com/" target="_blank"
                >Laravel</a
              >
              και
              <a
                class="underline"
                href="https://tailwindcss.com/"
                target="_blank"
                >Tailwindcss</a
              >.
            </p>
            <p>
              Δημιουργήθηκε από τον
              <a
                class="underline"
                href="mailto:g.theodoroy@gmail.com?subject=Ηλεκτρονικός Απουσιολόγος"
                >Γεώργιο Θεοδώρου</a
              >. Παρέχεται ως έχει σύμφωνα με την άδεια λογισμικού
              <a
                class="underline"
                href="https://opensource.org/licenses/MIT"
                target="_blank"
                >MIT</a
              >. Ο δημιουργός δεν φέρει ευθύνη για οτιδήποτε συμβεί κατά τη
              χρήση του.
            </p>
            <p>
              Διατίθεται δωρεάν. Αν σας διευκολύνει στην καθημερινότητά σας, σας
              τέρπει και επιθυμείτε, μπορείτε να κάνετε μια δωρεά πατώντας το
              επόμενο κουμπί.
            </p>
            <div class="text-center">
              <form
                action="https://www.paypal.com/cgi-bin/webscr"
                method="post"
                target="_blank"
              >
                <input type="hidden" name="cmd" value="_s-xclick" />
                <input
                  type="hidden"
                  name="hosted_button_id"
                  value="LJ75HQPSHBK2Q"
                />
                <input
                  type="image"
                  src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
                  border="0"
                  name="submit"
                  title="PayPal - The safer, easier way to pay online!"
                  alt="Donate with PayPal button"
                />
                <img
                  alt=""
                  border="0"
                  src="https://www.paypal.com/en_GR/i/scr/pixel.gif"
                  width="1"
                  height="1"
                />
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Head } from "@inertiajs/inertia-vue3";

export default {
  components: {
    BreezeAuthenticatedLayout,
    Head,
  },
  props: {
    infoUrl: String,
  },
};
</script>
