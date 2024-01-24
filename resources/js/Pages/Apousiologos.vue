<template>
  <Head title="Απουσιολόγος" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Απουσιολόγος
      </h2>
    </template>
    <!-- ΕΞΩΤΕΡΙΚΟ CONTAINER -->
    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- CONTAINER ΣΕΛΙΔΑΣ -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200 space-y-4">
            <!-- ΣΥΝΔΕΣΜΟΙ ΤΜΗΜΑΤΩΝ -->
            <div
              class="flex flex-wrap border border-gray-200 rounded-md p-4"
            >
              <span class="pr-4 font-medium">Επιλογή τμήματος:</span>
              <Link
                v-for="anathesi in anatheseis"
                :key="anathesi"
                :href="route('apousiologos') + `/${anathesi}/${date}`"
                class="mr-2"
                :class="{
                  'bg-gray-500 text-gray-100 font-bold px-2 rounded-md':
                    selectedTmima === anathesi,
                }"
                :title="'Επιλογή ' + anathesi"
                >{{ anathesi }}</Link
              >
              <Link
                v-show="$page.props.auth.user.permissions.admin"
                :href="route('apousiologos') + `/0/${date}`"
                class="text-xl"
                :class="{
                  'bg-gray-500 text-gray-100 px-2 rounded-md font-bold':
                    selectedTmima === '0',
                }"
                title="Αποεπιλογή τμήματος"
                >&#9746;</Link
              >
            </div>
            <!-- ΣΥΝΔΕΣΜΟΙ ΤΜΗΜΑΤΩΝ ΤΕΛΟΣ-->
            <GthSuccess property="saveSuccess">
              {{ this.printTime() }}
            </GthSuccess>
            <GthError property="saveError" />
            <GthInfo property="dateOutOfRange" />
            <GthInfo property="checkUpdates" :html="true" />
            <!-- HMEROMHNIA -->
            <div class="text-center space-x-2">
              <BreezeInput
                type="date"
                v-model="apouForm.date"
                :disabled="disableDatePicker()"
                @change="
                  this.$inertia.get(
                    route('apousiologos') + `/${selectedTmima}/${apouForm.date}`
                  )
                "
                class="p-1"
              />
              <a
                v-show="$page.props.auth.user.permissions.admin"
                :href="
                  route('exportApouxls', {
                    apoDate: apouForm.date,
                    eosDate: apouForm.date,
                  })
                "
                title="Εξαγωγή xls"
                class="text-2xl"
              >
                &#128229;
              </a>
            </div>
            <div
              v-show="!checkIfAllowWeekends"
              class="text-center font-semibold text-3xl text-gray-700 py-2"
            >
              Όχι απουσίες το Σαββατοκύριακο!
            </div>
            <!-- ΚΑΡΤΕΛΑ ΤΜΗΜΑΤΟΣ -->
            <div
               class="flex flex-col rounded-md max-w-3xl mx-auto"
            >
              <!-- ΕΠΙΚΕΦΑΛΙΔΑ ΚΑΡΤΕΛΑΣ ΤΜΗΜΑΤΟΣ -->
              <Actions
                :canSave="canSave"
                :canUnlockHours="canUnlockHours"
                :checkIfInProgram="checkIfInProgram"
                :selectedTmima="selectedTmima"
                :showTitleAndButtons="showTitleAndButtons"
                :checkIfAllowWeekends = "checkIfAllowWeekends"
                @unlockHours="varHoursUnlocked = 1"
                @apouSubmit="apouSubmit"
                class="pt-4"
              />
              <!-- ΕΠΙΚΕΦΑΛΙΔΑ ΚΑΡΤΕΛΑΣ ΤΜΗΜΑΤΟΣ ΤΕΛΟΣ-->
              <!-- ΕΠΙΚΕΦΑΛΙΔΕΣ ΠΙΝΑΚΑ -->
              <div
                v-show="
                  !checkIfSelectedTmima &&
                  !$page.props.auth.user.permissions.admin
                "
                class="text-center font-semibold text-3xl text-gray-700 py-24"
              >
                Επιλέξτε ένα τμήμα.
              </div>
              <div
                v-show="
                  !checkIfSelectedTmima &&
                  $page.props.auth.user.permissions.admin
                "
                class="text-center font-semibold text-3xl text-gray-700 py-24"
              >
                Τι ωραία! Ούτε μία απουσία!
              </div>

              <div
                v-show="checkIfSelectedTmima"
                class="pt-1 flex flex-col sm:flex-row sm:space-x-2"
              >
                <!-- ΕΠΙΚΕΦΑΛΙΔΕΣ ΠΙΝΑΚΑ ΑΡΙΣΤΕΡΑ -->
                <div class="flex space-x-2 sm:w-3/5 pt-3 font-semibold">
                  <div class="w-6 text-right"></div>
                  <div v-show="showTmima" class="w-6 text-center">ΤΜ</div>
                  <div class="w-10 text-center">ΑΜ</div>
                  <div>ΟΝΟΜΑΤΕΠΩΝΥΜΟ</div>
                </div>
                <!-- ΕΠΙΚΕΦΑΛΙΔΕΣ ΠΙΝΑΚΑ ΔΕΞΙΑ -->
                <div
                  v-show="checkIfInProgram && checkIfAllowWeekends"
                  class="
                    flex
                    w-max
                    justify-evenly
                    border-2 border-gray-300
                    p-1
                    rounded-md
                    self-center
                  "
                >
                  <div class="w-8 text-center pb-0.5 bg-gray-200 font-semibold">
                    &#8721;
                  </div>
                  <div
                    v-for="index in totalHours"
                    :key="index"
                    v-show="index <= activeHour || showApousiesCheckBoxes"
                    @click="checkCol(index)"
                    class="w-8 text-center pb-0.5 bg-gray-100 font-semibold"
                    :class="{
                      'bg-gray-500 text-gray-100': index === activeHour,
                      'cursor-pointer': varHoursUnlocked || index === activeHour,
                    }"
                    :title="
                      varHoursUnlocked || index == activeHour
                        ? 'Επιλογή όλων'
                        : ''
                    "
                  >
                    {{ index }}η
                  </div>
                  <div
                    v-show="$page.props.auth.user.permissions.teacherOrAdmin"
                    :class="$page.props.auth.user.permissions.admin?
                    'w-14 bg-gray-200 text-center' :
                    'w-8 bg-gray-200 text-center'
                    "
                  >
                    <Link
                      :href="
                        route('emailParent') + `/all/${date}/${selectedTmima}` + createQuery()
                      "
                      v-show="canEmailAll"
                      class="w-8 pb-0.5 font-semibold"
                      title="Email σε όλους"
                      onclick="return confirm('Να σταλεί email στους επιλεγμένους κηδεμόνες?\n\nΑν δεν επιλεγεί κανείς στέλνεται email σε όλους.\n\nΕπιβεβαιώστε παρακαλώ.');"
                    >
                      <span class="bg-blue-500 px-1 rounded"> &#128231;</span>
                    </Link>
                    <BreezeCheckbox
                      v-show="canEmailAll"
                      @click="checkEmailChks($event.target)"
                      class="ml-1 mb-0.5"
                      title="Επιλογή όλων"
                    />
                  </div>
                </div>
              </div>
              <!-- ΕΠΙΚΕΦΑΛΙΔΕΣ ΠΙΝΑΚΑ ΤΕΛΟΣ -->
              <!-- ΣΕΙΡΑ ΠΙΝΑΚΑ -->
              <div
                v-show="checkIfSelectedTmima"
                v-for="(student, index) in arrStudents"
                :key="student.id"
                class="pt-1 flex flex-col sm:flex-row sm:space-x-2"
              >
                <!-- ΣΕΙΡΑ ΠΙΝΑΚΑ ΑΡΙΣΤΕΡΑ (ΜΑΘΗΤΕΣ) -->
                <div class="flex space-x-2 sm:w-3/5 pt-3">
                  <div class="w-6 text-right">{{ index + 1 }}.</div>
                  <div
                    v-show="showTmima"
                    class="w-6 text-center overflow-hidden"
                  >
                    {{ student.tmima }}
                  </div>
                  <div class="w-10">{{ student.id }}</div>
                  <div>
                    {{ student.eponimo + " " + student.onoma }}
                  </div>
                </div>
                <!-- ΣΕΙΡΑ ΠΙΝΑΚΑ ΔΕΞΙΑ (ΚΟΥΤΙΑ)-->
                <div
                  v-show="checkIfInProgram && checkIfAllowWeekends"
                  class="
                    flex
                    w-max
                    justify-evenly
                    border-2 border-gray-300
                    p-1
                    rounded-md
                    self-center
                  "
                >
                  <div
                    @click.self="checkRow(student.id)"
                    class="w-8 text-center pb-0.5 bg-gray-200 font-semibold"
                    :class="{
                      'cursor-pointer': $page.props.auth.user.permissions.admin,
                    }"
                    :title="
                      $page.props.auth.user.permissions.admin
                        ? 'Επιλογή όλων'
                        : ''
                    "
                  >
                    {{ student.apousies || "&nbsp;" }}
                    <!-- κουμπί αποβολής -->
                    <div 
                      class="h-2 bg-gray-300 ml-1 mr-1 cursor-pointer rounded" 
                      title="Αποβολή" 
                      @click="checkApovoli(student.id)"
                    />

                  </div>
                  <div
                    v-for="index in totalHours"
                    :key="index"
                    v-show="index <= activeHour || showApousiesCheckBoxes"
                    class="w-8 text-center pb-0.5 bg-gray-100"
                    :class="{
                      'bg-gray-500': index === activeHour,
                      'bg-red-300': apouForm[student.id]['apov'][index],
                    }"
                  >
                    <BreezeCheckbox
                      v-model="apouForm[student.id]['apou'][index]"
                      :checked="apouForm[student.id]['apou'][index]"
                      :disabled="checkDisabled(student.id, index)"
                      :title="arrNames[apouForm[student.id]['teach'][index]] !== undefined 
                      ? arrNames[apouForm[student.id]['teach'][index]]
                      : apouForm[student.id]['teach'][index] ?? 
                      'teacher_id ' + apouForm[student.id]['teach'][index]
                      "
                      @click="toggleApousia(student.id, index)"
                    />
                    <!-- κουμπί αποβολής -->
                    <div 
                      class="h-2 bg-gray-300 ml-1 mr-1 cursor-pointer rounded" 
                      :class="{
                        'bg-red-400': apouForm[student.id]['apov'][index],
                        'bg-gray-700': index === activeHour,
                      }"
                      title="Αποβολή" 
                      @click="toggleApovoli(student.id, index)"
                    >
                    </div>
                  </div>
                  <div
                    v-show="$page.props.auth.user.permissions.teacherOrAdmin"
                    :class="$page.props.auth.user.permissions.admin?
                    'w-14 bg-gray-200 text-center' :
                    'w-8 bg-gray-200 text-center'
                    "
                  >
                    <Link
                      :href="route('emailParent') + `/${student.id}/${date}`"
                      v-show="
                        canEmail &&
                        student.apousies &&
                        student.email
                      "
                      class="w-8 pb-0.5 font-semibold"
                      :title="
                        'email σε ' + student.eponimo + ' ' + student.onoma  + ' ' + student.email
                      "
                      onclick="return confirm('Αποστολή email?');"
                    >
                      <span class="bg-blue-500 px-1 rounded">&#128231;</span>
                    </Link>
                    <BreezeCheckbox
                      v-model="varSendEmail[student.id]"
                      :checked="varSendEmail[student.id]"
                      v-show="
                        $page.props.auth.user.permissions.admin &&
                        student.apousies &&
                        student.email
                      "
                      class="ml-1 mb-0.5"
                    />

                  </div>
                </div>
              </div>
              <!-- ΣΕΙΡΑ ΠΙΝΑΚΑ ΤΕΛΟΣ -->
              <!-- ΕΠΙΚΕΦΑΛΙΔΑ ΚΑΡΤΕΛΑΣ ΤΜΗΜΑΤΟΣ ΕΠΑΝΑΛΗΨΗ-->
              <Actions
                :canSave="canSave"
                :canUnlockHours="canUnlockHours"
                :checkIfInProgram="checkIfInProgram"
                :selectedTmima="selectedTmima"
                :showTitleAndButtons="showTitleAndButtons"
                :checkIfAllowWeekends = "checkIfAllowWeekends"
                @unlockHours="varHoursUnlocked = 1"
                @apouSubmit="apouSubmit"
                class="pt-4"
              />
              <!-- ΕΠΙΚΕΦΑΛΙΔΑ ΚΑΡΤΕΛΑΣ ΤΜΗΜΑΤΟΣ ΕΠΑΝΑΛΗΨΗ ΤΕΛΟΣ-->
            </div>
            <!-- ΚΑΡΤΕΛΑ ΤΜΗΜΑΤΟΣ ΤΕΛΟΣ-->
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
</template>
<style lang="postcss" scoped>
.gth {
  @apply w-full sm:w-max bg-gray-100  hover:bg-gray-300  active:bg-gray-500  text-gray-700  hover:text-gray-900  active:text-gray-100
        text-sm  font-semibold  py-1 px-4 border border-gray-300  hover:border-transparent rounded-md self-center disabled:opacity-50;
}
</style>

<script>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated.vue";
import Actions from "@/Layouts/ApousiologosActions.vue";
import { Head } from "@inertiajs/inertia-vue3";
import { Link } from "@inertiajs/inertia-vue3";
import BreezeCheckbox from "@/Components/Checkbox.vue";
import BreezeInput from "@/Components/Input.vue";
import { ref, reactive } from "vue";
import { useForm } from "@inertiajs/inertia-vue3";
import { computed } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";
import GthSuccess from "@/Components/GthSuccess.vue";
import GthError from "@/Components/GthError.vue";
import GthInfo from "@/Components/GthInfo.vue";

export default {
  components: {
    BreezeAuthenticatedLayout,
    Head,
    Link,
    BreezeCheckbox,
    BreezeInput,
    GthSuccess,
    GthError,
    GthInfo,
    Actions,
  },
  props: {
    anatheseis: Object,
    date: String,
    selectedTmima: String,
    setCustomDate: String,
    arrStudents: Array,
    arrSendEmail: Object,
    activeHour: Number,
    totalHours: Number,
    hoursUnlocked: Number,
    letTeachersUnlockHours: Number,
    allowTeachersEditOthersApousies: Number,
    showFutureHours: Number,
    isWeekend: Number,
    allowWeekends: Number,
    allowTeachersSaveAtNotActiveHour: Number,
    allowTeachersEmail: Number,
    arrApousies: Object,
    arrNames: Object,
    allowPastDays: Boolean,
    isToday: Boolean,
  },
  setup(props) {

    const varSendEmail = reactive(props.arrSendEmail);

    const varHoursUnlocked = ref(props.hoursUnlocked);
    
    const apouForm = useForm(props.arrApousies);

    const checkIfAllowWeekends = computed(function () {
      //return false;
      if (usePage().props.value.auth.user.permissions.admin) return true;
      if (props.isWeekend !== 1) return true;
      if (props.isWeekend == 1 && props.allowWeekends == 1) return true;
      return false;
    });

    const checkIfSelectedTmima = computed(function () {
      if (
        usePage().props.value.auth.user.permissions.admin &&
        props.arrStudents.length
      )
        return true;
      if (props.selectedTmima !== "0") return true;
      return false;
    });

    const checkIfInProgram = computed(function () {
      if (usePage().props.value.auth.user.permissions.admin) return true;
      if (props.activeHour) return true;
      if (props.allowTeachersSaveAtNotActiveHour == 1) return true;
      if (
        props.allowPastDays &&
        apouForm.date !== new Date().toISOString().split("T")[0]
      ) {
        return true;
      }
      return false;
    });

    const canUnlockHours = computed(function () {
      if (props.letTeachersUnlockHours && !varHoursUnlocked.value) return true;
      if (
        props.allowPastDays &&
        apouForm.date !== new Date().toISOString().split("T")[0] &&
        !varHoursUnlocked.value
      ) {
        return true;
      }
      return false;
    });

    const canSave = computed(function () {
      if (usePage().props.value.auth.user.permissions.admin) return true;
      if (props.activeHour) return true;
      if (props.allowTeachersSaveAtNotActiveHour && varHoursUnlocked.value) {
        return true;
      }
      if (
        props.allowPastDays &&
        apouForm.date !== new Date().toISOString().split("T")[0] &&
        varHoursUnlocked.value
      ) {
        return true;
      }
      return false;
    });

    const showTitleAndButtons = computed(function () {
      if (props.selectedTmima !== "0") return true;
      if (
        usePage().props.value.auth.user.permissions.admin &&
        props.arrStudents.length
      )
        return true;
      return false;
    });

    const showTmima = computed(function () {
      if (
        props.selectedTmima == "0" &&
        usePage().props.value.auth.user.permissions.admin
      )
        return true;
      return false;
    });

    const showApousiesCheckBoxes = computed(function () {
      if (usePage().props.value.auth.user.permissions.admin) return true;
      if (props.showFutureHours == 1) return true;
      if (!props.activeHour) return true;
      return false;
    });

    const canEmailAll = computed(function () {
      let sum = 0;
      let haveEmail = false;
      props.arrStudents.forEach((student) => {
        sum += student.apousies;
        if (student.apousies && student.email) haveEmail = true;
      });
      if (usePage().props.value.auth.user.permissions.admin && sum && haveEmail)
        return true;
      return false;
    });

    const canEmail = computed(function () {
      if (usePage().props.value.auth.user.permissions.admin) return true;
      if (usePage().props.value.auth.user.permissions.teacher && props.allowTeachersEmail == 1 && props.isToday ) return true;
      return false;
    });

    function apouSubmit() {
      apouForm.post(
        route("apousiologos.store") + `/${props.selectedTmima}/${props.date}`,
        { preserveScroll: true }
      );
    }

    function checkCol(index) {
      // αν μπορεί να αποθηκεύσει
      if (!this.canSave) return;
      // οι ώρες κλειδωμένες και εκτός ωραρίου
      if (!varHoursUnlocked.value && !props.activeHour) return;
      // οι ώρες κλειδωμένες
      // εντός ωραρίου
      // επιτρέπεται μόνο στην ενεργή ώρα
      if (
        !varHoursUnlocked.value &&
        props.activeHour &&
        index !== props.activeHour
      ) {
        return;
      }

      let chkNotSameTeacher = false
      props.arrStudents.forEach((student) => {
        if(props.arrApousies[student.id]['teach'][index] !== '' && !(props.arrApousies[student.id]['teach'][index] == usePage().props.value.auth.user.id)) {
          chkNotSameTeacher =  true
        }
      });
      if (usePage().props.value.auth.user.permissions.admin) chkNotSameTeacher = false
      if(chkNotSameTeacher) return

      let newValue;
      props.arrStudents.forEach((student, loopIndex) => {
        if (loopIndex == 0) {
          newValue = !apouForm[student.id]['apou'][index];
        }
        apouForm[student.id]['apou'][index] = newValue;
      });
    }

    function checkRow(id) {
      // μόνο ο Διαχειριστής
      if (!usePage().props.value.auth.user.permissions.admin) return;
      let newValue;
      for (let loopIndex in apouForm[id]['apou']) {
        if (loopIndex == 1) {
          newValue = !apouForm[id]['apou'][loopIndex];
        }
        apouForm[id]['apou'][loopIndex] = newValue;
        apouForm[id]['teach'][loopIndex] = usePage().props.value.auth.user.id;
        if(newValue == false) {
          apouForm[id]['apov'][loopIndex] = newValue;
          apouForm[id]['teach'][loopIndex] = null;
        }
      }
    }
  
    function checkApovoli(id) {
      // μόνο ο Διαχειριστής
      if (!usePage().props.value.auth.user.permissions.admin) return;
      let newValue;

      for (let loopIndex in apouForm[id]['apov']) {
        if (loopIndex == 1) {
          newValue = !apouForm[id]['apov'][loopIndex];
          if(newValue == true){
            if(! confirm('Θέλετε να καταχωρίσετε Αποβολή;')) return
          }
        }
        apouForm[id]['apov'][loopIndex] = newValue;
        if(newValue == true) {
          apouForm[id]['apou'][loopIndex] = newValue;
          apouForm[id]['teach'][loopIndex] = usePage().props.value.auth.user.id;
        }
      }
    }
  

    function printTime() {
      return new Date().toLocaleTimeString();
    }

    function createQuery(){
      let query = ''
      Object.keys(varSendEmail).forEach(key => {
        if(varSendEmail[key]) query+= ',' + key
      })
      if(! query) return ''
      return '?st=' + query.replace(/(^,)/g, '');
    }

    function checkEmailChks(chkbx){
      Object.keys(varSendEmail).forEach(key => {
        varSendEmail[key] = chkbx.checked
      })
      chkbx.title = chkbx.checked ? "Αποεπιλογή όλων" : "Επιλογή όλων"
    }

    function disableDatePicker(){
      if(usePage().props.value.auth.user.permissions.admin) return false
      if(props.setCustomDate) return true
      if(!props.allowPastDays) return true
      return false
    }

    function checkDisabled(studentId,index){
      if(usePage().props.value.auth.user.permissions.admin) return false
      if(props.activeHour == index) return false
      if(varHoursUnlocked.value && props.allowTeachersEditOthersApousies) return false
      if(varHoursUnlocked.value && (!props.arrApousies[studentId]['teach'][index] || usePage().props.value.auth.user.id == props.arrApousies[studentId]['teach'][index])) return false
      return true
    }

    function toggleApovoli( studentId, index){
      if (checkDisabled(studentId, index) == true) return
      if(apouForm[studentId]['apov'][index]==false) {
        if(! confirm('Θέλετε να καταχωρίσετε Αποβολή;')) return
        apouForm[studentId]['apou'][index]=true
        apouForm[studentId]['apov'][index]=true
        apouForm[studentId]['teach'][index]=usePage().props.value.auth.user.id
      }else{
        apouForm[studentId]['apov'][index]=false
      }
    }

    function toggleApousia( studentId, index){
      if(apouForm[studentId]['apou'][index]==true) {
        apouForm[studentId]['apov'][index]=false
        apouForm[studentId]['teach'][index]=''
      }else{
        apouForm[studentId]['teach'][index]=usePage().props.value.auth.user.id
      }
    }

    return {
      varHoursUnlocked,
      apouForm,
      apouSubmit,
      checkIfAllowWeekends,
      checkIfSelectedTmima,
      checkIfInProgram,
      canUnlockHours,
      canSave,
      printTime,
      showTitleAndButtons,
      showTmima,
      showApousiesCheckBoxes,
      canEmailAll,
      canEmail,
      checkCol,
      checkRow,
      varSendEmail,
      createQuery,
      checkEmailChks,
      disableDatePicker,
      checkDisabled,
      toggleApovoli,
      toggleApousia,
      checkApovoli,
    };
  },
};
</script>
