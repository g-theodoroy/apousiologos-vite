<template>
  <!-- ΕΠΙΚΕΦΑΛΙΔΑ ΚΑΡΤΕΛΑΣ ΤΜΗΜΑΤΟΣ -->
  <div
    v-show="showTitleAndButtons"
    class="text-center flex flex-col sm:flex-row"
  >
    <!-- LABEL ΑΡΙΣΤΕΡΑ ΤΜΗΜΑ-->
    <div class="w-full text-center font-bold py-4">
      {{ selectedTmima == "0" ? "Ολα τα τμήματα" : "Τμημα " + selectedTmima }}
    </div>
    <!-- LABEL ΔΕΞΙΑ ΚΟΥΜΠΙΑ-->
    <!-- ΕΚΤΟΣ ΩΡΑΡΙΟΥ ΑΠΟΚΡΥΨΗ -->
    <div v-show="!checkIfInProgram" class="w-full text-center font-bold py-4">
      Εκτός ωραρίου
    </div>
    <!-- ΕΝΤΟΣ ΩΡΑΡΙΟΥ ΕΜΦΑΝΙΣΗ-->
    <div
      v-show="checkIfInProgram"
      class="
        text-center
        space-y-1
        py-2
        sm:space-y-0 sm:space-x-2
        md:pl-20
        w-full
      "
    >
      <button v-show="canUnlockHours" @click="$emit('unlockHours')" class="gth">
        Ξεκλείδωμα
      </button>
      <button v-show="canSave" @click="$emit('apouSubmit')" class="gth">
        Αποθήκευση
      </button>
    </div>
  </div>
  <!-- ΕΠΙΚΕΦΑΛΙΔΑ ΚΑΡΤΕΛΑΣ ΤΜΗΜΑΤΟΣ ΤΕΛΟΣ-->
</template>
<style lang="postcss" scoped>
.gth {
  @apply w-full sm:w-max bg-gray-100  hover:bg-gray-300  active:bg-gray-500  text-gray-700  hover:text-gray-900  active:text-gray-100
        text-sm  font-semibold  py-1 px-4 border border-gray-300  hover:border-transparent rounded-md self-center disabled:opacity-50;
}
</style>

<script>
import { ref } from "vue";
import { useForm } from "@inertiajs/inertia-vue3";
import { computed } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";

export default {
  emits: ["unlockHours", "apouSubmit"],
  props: {
    showTitleAndButtons: Boolean,
    selectedTmima: String,
    checkIfInProgram: Boolean,
    canUnlockHours: Boolean,
    canSave: Boolean,
  },
  setup(props) {
    return {};
  },
};
</script>
