<template>
  <Head title="Ρυθμίσεις" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <AdminLayout></AdminLayout>
      <h2
        class="font-semibold text-xl text-gray-800 leading-tight pt-2 sm:pt-6"
      >
        Ρυθμίσεις
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="handleSubmit">
            <div
              class="
                p-2
                sm:p-6
                bg-white
                border-b border-gray-200
                space-y-2
                sm:space-y-6
              "
            >
              <GthSuccess property="success">
                {{ this.printTime() }}
              </GthSuccess>

              <SettingsPanel title="Σύστημα" :button="true">
                <SettingsCheckboxContainer
                  label="Να επιτρέπεται η Εγγραφή νέων χρηστών"
                >
                  <BreezeCheckbox
                    v-model="settings.allowRegister"
                    :checked="initialSettings.allowRegister"
                  />
                </SettingsCheckboxContainer>
                <SettingsContainer label="Ζώνη ώρας">
                  <BreezeInput
                    class="gth"
                    v-model="settings.timeZone"
                    placeholder="Europe/Athens"
                  />
                </SettingsContainer>
                <SettingsContainer label="Αρχική σελίδα">
                  <select
                    class="
                      border-gray-300
                      focus:border-indigo-300
                      focus:ring
                      focus:ring-indigo-200
                      focus:ring-opacity-50
                      rounded-md
                      shadow-sm
                      gth
                    "
                    v-model="settings.landingPage"
                    :value="initialSettings.landingPage"
                  >
                    <option value="apousiologos">Απουσιολόγος</option>
                    <option value="exams">Διαγωνίσματα</option>
                    <option value="grades">Βαθμολογία</option>
                  </select>
                </SettingsContainer>
                <SettingsContainer label="Όνομα σχολείου">
                  <BreezeInput
                    class="gth"
                    v-model="settings.schoolName"
                    placeholder="Το σχολείο μου"
                  />
                </SettingsContainer>
              </SettingsPanel>
              <SettingsPanel title="Απουσιολόγος" :button="true">
                <SettingsCheckboxContainer label="Οι ώρες να είναι ξεκλείδωτες">
                  <BreezeCheckbox
                    v-model="settings.hoursUnlocked"
                    :checked="initialSettings.hoursUnlocked"
                  />
                </SettingsCheckboxContainer>
                <SettingsCheckboxContainer
                  label="Επιτρέπεται η εισαγωγή απουσιών εκτός ωραρίου"
                >
                  <BreezeCheckbox
                    v-model="settings.allowTeachersSaveAtNotActiveHour"
                    :checked="initialSettings.allowTeachersSaveAtNotActiveHour"
                  />
                </SettingsCheckboxContainer>
                <SettingsCheckboxContainer
                  label="Επιτρέπεται στους καθηγητές να ξεκλειδώνουν τις ώρες"
                >
                  <BreezeCheckbox
                    v-model="settings.letTeachersUnlockHours"
                    :checked="initialSettings.letTeachersUnlockHours"
                  />
                </SettingsCheckboxContainer>
                <SettingsCheckboxContainer
                  label="Να μη κρύβονται οι επόμενες ώρες"
                >
                  <BreezeCheckbox
                    v-model="settings.showFutureHours"
                    :checked="initialSettings.showFutureHours"
                  />
                </SettingsCheckboxContainer>
                <SettingsCheckboxContainer
                  label="Επιτρέπεται η εισαγωγή απουσιών Σαββατοκύριακο"
                >
                  <BreezeCheckbox
                    v-model="settings.allowWeekends"
                    :checked="initialSettings.allowWeekends"
                  />
                </SettingsCheckboxContainer>
                <SettingsContainer
                  label="Οι καθηγητές μπορούν να εισάγουν απουσίες ημέρες πίσω"
                >
                  <BreezeInput
                    class="gth"
                    v-model="settings.pastDaysInsertApousies"
                    placeholder=""
                  />
                </SettingsContainer>
                <SettingsContainer label=" Ορισμός Ημνιας εισαγωγής απουσιών">
                  <BreezeInput
                    class="gth"
                    v-model="settings.setCustomDate"
                    placeholder="ηη/μμ/εεεε"
                  />
                </SettingsContainer>
                <SettingsCheckboxContainer
                  label="Οι καθηγητές μπορούν να στέλνουν email"
                >
                  <BreezeCheckbox
                    v-model="settings.allowTeachersEmail"
                    :checked="initialSettings.allowTeachersEmail"
                  />
                </SettingsCheckboxContainer>
              </SettingsPanel>

              <SettingsPanel title="Διαγωνίσματα" :button="true">
                <SettingsCheckboxContainer
                  label="Επιτρέπεται η καταχώριση διαγωνισμάτων"
                >
                  <BreezeCheckbox
                    v-model="settings.allowExams"
                    :checked="initialSettings.allowExams"
                  />
                </SettingsCheckboxContainer>
                <SettingsContainer label="Επιτρεπόμενα διαγωνίσματα την ημέρα">
                  <BreezeInput
                    class="gth"
                    v-model="settings.maxDiagonismataForDay"
                    placeholder="1"
                  />
                </SettingsContainer>
                <SettingsContainer
                  label="Επιτρεπόμενα διαγωνίσματα την εβδομάδα"
                >
                  <BreezeInput
                    class="gth"
                    v-model="settings.maxDiagonismataForWeek"
                    placeholder="3"
                  />
                </SettingsContainer>
                <!--
                <SettingsContainer label='Αρχή σχολικού έτους "Μήνας-Ημέρα"'>
                  <BreezeInput
                    class="gth"
                    v-model="settings.totalStartMonthDay"
                    placeholder="09-01"
                  />
                </SettingsContainer>
                <SettingsContainer label='Τέλος σχολικού έτους "Μήνας-Ημέρα"'>
                  <BreezeInput
                    class="gth"
                    v-model="settings.totalEndMonthDay"
                    placeholder="06-30"
                  />
                </SettingsContainer>
                -->
              </SettingsPanel>

              <SettingsPanel title="Βαθμολογία" :button="true">
                <SettingsContainer label="Βαθμολογική περίοδος">
                  <select
                    class="
                      border-gray-300
                      focus:border-indigo-300
                      focus:ring
                      focus:ring-indigo-200
                      focus:ring-opacity-50
                      rounded-md
                      shadow-sm
                      gth
                    "
                    v-model="settings.activeGradePeriod"
                    :value="initialSettings.activeGradePeriod"
                  >
                    <option
                      v-for="(item, index) in periods"
                      :key="index"
                      :value="index"
                    >
                      {{ item }}
                    </option>
                  </select>
                </SettingsContainer>
                <SettingsCheckboxContainer
                  label="Εμφάνιση βαθμών άλλων μαθημάτων"
                >
                  <BreezeCheckbox
                    v-model="settings.showOtherGrades"
                    :checked="initialSettings.showOtherGrades"
                  />
                </SettingsCheckboxContainer>
                <SettingsContainer
                  label="Ειδοποίηση αν καταχωρίζονται βαθμοί κάτω από"
                >
                  <BreezeInput
                    class="gth"
                    v-model="settings.gradeBaseAlert"
                    placeholder="10"
                  />
                </SettingsContainer>
              </SettingsPanel>
            </div>
          </form>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
</template>

<style lang="postcss" scoped>
.gth {
  @apply p-1 sm:px-2 w-full border text-center;
}
</style>

<script>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated.vue";
import AdminLayout from "@/Layouts/AdminMenu.vue";
import { Head } from "@inertiajs/inertia-vue3";
import BreezeCheckbox from "@/Components/Checkbox.vue";
import BreezeInput from "@/Components/Input.vue";
import SettingsPanel from "@/Components/SettingsPanel.vue";
import SettingsCheckboxContainer from "@/Components/SettingsCheckboxContainer.vue";
import SettingsContainer from "@/Components/SettingsContainer.vue";
import { Inertia } from "@inertiajs/inertia";
import { toRef } from "vue";
import GthSuccess from "@/Components/GthSuccess.vue";

export default {
  components: {
    BreezeAuthenticatedLayout,
    AdminLayout,
    Head,
    BreezeCheckbox,
    BreezeInput,
    SettingsPanel,
    SettingsCheckboxContainer,
    SettingsContainer,
    GthSuccess,
  },
  props: {
    periods: Array,
    initialSettings: Object,
  },
  setup(props) {
    const settings = toRef(props, "initialSettings");

    function handleSubmit() {
      Inertia.post(route("settings.store"), settings.value, {
        preserveScroll: true,
      });
    }

    function printTime() {
      return new Date().toLocaleTimeString();
    }

    return {
      settings,
      handleSubmit,
      printTime,
    };
  },
  /*
  data: function () {
    return {
      settings: this.initialSettings,
    };
  },
  props: {
    periods: Array,
    initialSettings: Object,
  },
  components: {
    BreezeAuthenticatedLayout,
    AdminLayout,
    Head,
    BreezeCheckbox,
    BreezeInput,
    SettingsPanel,
    SettingsCheckboxContainer,
    SettingsContainer,
  },
  methods: {
    handleSubmit() {
      Inertia.post(route("settings.store"), this.settings);
    },
    printTime: function () {
      return new Date().toLocaleTimeString();
    },
  },
  */
};
</script>
