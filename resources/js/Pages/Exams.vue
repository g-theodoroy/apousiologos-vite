<template>
  <Head title="Διαγωνίσματα" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <div
        class="
          flex flex-col
          sm:flex-row
          space-y-4
          sm:space-y-0 sm:justify-between
        "
      >
        <div
          class="
            text-center
            md:text-left
            font-semibold
            text-xl text-gray-800
            leading-tight
          "
        >
          Διαγωνίσματα
        </div>
        <div class="w-max self-center space-x-1 sm:space-x-2">
          <a
            v-show="$page.props.auth.user.permissions.admin"
            class="gthButton"
            :href="
              route('exportExamsXls', {
                year: this.year,
                month: this.month,
              })
            "
          >
            Εξαγωγή
          </a>
          <button class="gthButton" @click="myExams">
            Τα διαγωνίσματά μου
          </button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div id="calendar" class="p-6 bg-white border-b border-gray-200">
            <GthSuccess property="success">
              {{ this.printTime() }}
            </GthSuccess>
            <GthError property="error" />
            <div
              class="
                font-bold
                text-xl
                p-4
                flex
                space-x-2 space-y-2
                flex-col
                md:flex-row md:space-y-0
                items-center
                justify-center
              "
            >
              <span>{{ selectedMonth }}</span>
              <div class="flex space-x-2">
                <button
                  @click="previous()"
                  class="px-2 border rounded font-extrabold text-2xl"
                  title="προηγούμενος μήνας"
                >
                  &lt;
                </button>
                <Link
                  v-show="
                    this.month !==
                    parseInt(
                      new Date().toISOString().split('T')[0].substr(5, 2)
                    )
                  "
                  :href="
                    route(
                      'exams',
                      {
                        g: gridMode.value,
                      },
                      { preserveScroll: true }
                    )
                  "
                  class="px-1 border rounded font-extrabold text-2xl"
                  title="σήμερα"
                  preserve-state
                  preserve-scroll
                  >&#9962;</Link
                >
                <button
                  class="px-2 border rounded font-extrabold text-2xl"
                  @click="this.gridMode = !this.gridMode"
                  :title="this.gridMode ? 'Λίστα' : 'Πλέγμα'"
                >
                  {{ this.gridMode ? "&#9636;" : "&#9638;" }}
                </button>
                <button
                  @click="next()"
                  class="px-2 border rounded font-extrabold text-2xl"
                  title="επόμενος μήνας"
                >
                  &gt;
                </button>
              </div>
            </div>
            <div
              class="grid grid-cols-1 gap-0.5 bg-white :p-6 sm:pb-4"
              :class="{ 'md:grid-cols-5': this.gridMode }"
            >
              <div
                v-for="label in [
                  'Δευτέρα',
                  'Τρίτη',
                  'Τετάρτη',
                  'Πέμπτη',
                  'Παρασκευή',
                ]"
                :key="label"
                class="font-semibold text-gray-700 hidden border rounded px-2"
                :class="{ 'md:table-cell': this.gridMode }"
              >
                {{ label }}
              </div>
              <div
                v-for="date in this.dateValues"
                :key="date.date"
                class="
                  min-h-20
                  border border-gray-300
                  bg-white
                  hover:border-blue-500
                  rounded
                "
                :class="{
                  'md:min-h-12': !this.gridMode,
                  hidden: parseInt(date.date.substr(5, 2)) !== month,
                  'md:table-cell':
                    parseInt(date.date.substr(5, 2)) !== month && this.gridMode,
                  'bg-blue-100 hover:bg-blue-200':
                    date.date == new Date().toISOString().split('T')[0],
                  'bg-gray-50 hover:bg-gray-100':
                    parseInt(date.date.substr(5, 2)) !== month,
                  'hover:bg-yellow-50 ':
                    parseInt(date.date.substr(5, 2)) == month,
                  'bg-red-200 hover:bg-red-300': this.noExams[date.date],
                }"
                @click.self="dateClicked(date.date)"
                @drop="onDrop($event, date.date)"
                @dragover.prevent
                @dragenter.prevent
              >
                <div class="flex max-w-min">
                  <div
                    class="font-bold ml-1 pl-1 pr-2 w-10"
                    :class="{
                      'opacity-50': parseInt(date.date.substr(5, 2)) !== month,
                      'md:hidden': this.gridMode,
                    }"
                  >
                    {{ date.shortName }}
                  </div>
                  <div
                    class="font-bold"
                    :class="{
                      'opacity-50': parseInt(date.date.substr(5, 2)) !== month,
                    }"
                  >
                    {{ date.date.substr(-2) + "/" + date.date.substr(5, 2) }}
                  </div>
                </div>
                <div
                  v-for="exam in exams[date.date]"
                  :key="exam.id"
                  class="border border-gray-300 rounded mx-1 px-2 truncate"
                  :class="setBgColor(exam)"
                  :title="exam.title"
                  @click="eventClicked(exam)"
                  :draggable="
                    exam.user_id == $page.props.auth.user.id ||
                    $page.props.auth.user.permissions.admin
                  "
                  @dragstart="startDrag($event, exam)"
                >
                  {{ exam.title }}
                </div>
              </div>
            </div>
            <!-- ΕΠΙΚΕΦΑΛΙΔΕΣ ΠΑΛΙ -->
            <div
              class="
                font-bold
                text-xl
                p-4
                flex
                space-x-2 space-y-2
                flex-col
                md:flex-row md:space-y-0
                items-center
                justify-center
              "
            >
              <span>{{ selectedMonth }}</span>
              <div class="flex space-x-2">
                <button
                  @click="previous()"
                  class="px-2 border rounded font-extrabold text-2xl"
                  title="προηγούμενος μήνας"
                >
                  &lt;
                </button>
                <Link
                  v-show="
                    this.month !==
                    parseInt(
                      new Date().toISOString().split('T')[0].substr(5, 2)
                    )
                  "
                  :href="
                    route(
                      'exams',
                      {
                        g: gridMode.value,
                      },
                      { preserveScroll: true }
                    )
                  "
                  class="px-1 border rounded font-extrabold text-2xl"
                  title="σήμερα"
                  preserve-state
                  preserve-scroll
                  >&#9962;</Link
                >
                <button
                  class="px-2 border rounded font-extrabold text-2xl"
                  @click="this.gridMode = !this.gridMode"
                  :title="this.gridMode ? 'Λίστα' : 'Πλέγμα'"
                >
                  {{ this.gridMode ? "&#9636;" : "&#9638;" }}
                </button>
                <button
                  @click="next()"
                  class="px-2 border rounded font-extrabold text-2xl"
                  title="επόμενος μήνας"
                >
                  &gt;
                </button>
              </div>
            </div>
            <!-- ΕΠΙΚΕΦΑΛΙΔΕΣ ΠΑΛΙ ΤΕΛΟΣ -->
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>

  <!-- MODAL EXAMS -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="examsIsOpen.open"
  >
    <div
      class="
        flex
        items-end
        justify-center
        min-h-screen
        pt-4
        px-4
        pb-20
        text-center
        sm:block sm:p-0
      "
    >
      <div class="fixed inset-0 transition-opacity">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- This element is to trick the browser into centering the modal contents. -->

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

      <div
        class="
          inline-block
          align-bottom
          bg-white
          rounded-lg
          text-left
          overflow-hidden
          shadow-xl
          transform
          transition-all
          sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full
        "
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-headline"
      >
        <div
          class="font-semibold text-xl p-2 bg-gray-100 rounded-t-md mx-4 mt-4"
        >
          {{ this.examsEditMode ? "Επεξεργασία" : "Δήλωση" }}
          διαγωνίσματος
        </div>
        <div v-show="this.errMsg.msg" class="text-red-500 mx-4 p-2 text-center">
          {{ this.errMsg.msg }}
        </div>
        <div
          v-show="this.examsForm.title"
          class="text-gray-900 mx-4 p-2 text-center font-semibold text-xl"
        >
          {{ this.examsForm.title }}
        </div>
        <div
          class="
            grid grid-cols-3
            sm:grid-cols-6
            gap-4
            bg-white
            px-4
            pt-5
            pb-4
            sm:p-6 sm:pb-4
          "
        >
          <span class="sm:col-start-3 items-center">Ημνια</span>
          <BreezeInput
            type="date"
            v-model="this.examsForm.date"
            :disabled="!this.examsEditMode"
            @keydown.prevent
            @click="this.errMsg.msg = ''"
            class="w-full p-1 text-center col-span-2 disabled:opacity-50"
            :class="{
              border: this.examsEditMode,
              'select-none': !this.examsEditMode,
            }"
          />
          <span class="hidden sm:table-cell">&nbsp;</span>
          <span>Τμήμα</span>
          <select
            v-model="examsForm.tmima1"
            :value="examsForm.tmima1"
            :disabled="this.examsEditMode"
            @change="setTmima2Values($event)"
            class="
              border-gray-300
              focus:border-indigo-300
              focus:ring
              focus:ring-indigo-200
              focus:ring-opacity-100
              rounded-md
              shadow-sm
              sm:px-2
              w-full
              p-1
              col-span-2
              border
              disabled:opacity-50
            "
          >
            <option value=""></option>
            <option v-for="item in tmimata.tmima1" :key="item" :value="item">
              {{ item }}
            </option>
          </select>
          <span>Τμήμα</span>
          <select
            v-model="examsForm.tmima2"
            :value="examsForm.tmima2"
            :disabled="!examsForm.tmima1 || this.examsEditMode"
            class="
              border-gray-300
              focus:border-indigo-300
              focus:ring
              focus:ring-indigo-200
              focus:ring-opacity-100
              rounded-md
              shadow-sm
              sm:px-2
              w-full
              p-1
              col-span-2
              border
              disabled:opacity-50
            "
          >
            <option value=""></option>
            <option v-for="item in tmimata.tmima2" :key="item" :value="item">
              {{ item }}
            </option>
          </select>
          <span>Μάθημα</span>
          <select
            v-model="examsForm.mathima"
            :value="examsForm.mathima"
            :disabled="this.examsEditMode"
            class="
              border-gray-300
              focus:border-indigo-300
              focus:ring
              focus:ring-indigo-200
              focus:ring-opacity-100
              rounded-md
              shadow-sm
              sm:px-2
              w-full
              p-1
              col-span-2
              sm:col-span-5
              border
              disabled:opacity-50
            "
          >
            <option value=""></option>
            <option v-for="item in mathimata" :key="item" :value="item">
              {{ item }}
            </option>
          </select>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            v-show="
              this.examsEditMode &&
              (this.examsForm.user_id == $page.props.auth.user.id ||
                $page.props.auth.user.permissions.admin)
            "
            @click="deleteExam"
            type="button"
            class="gthButton disabled:opacity-50"
          >
            Διαγραφή
          </button>
          <button
            v-show="
              !this.examsForm.user_id ||
              this.examsForm.user_id == $page.props.auth.user.id ||
              $page.props.auth.user.permissions.admin
            "
            @click="submitExamsForm"
            type="button"
            :disabled="!this.examsForm.isDirty"
            class="gthButton disabled:opacity-50"
            :class="{ 'cursor-not-allowed': !this.examsForm.isDirty }"
          >
            Αποθήκευση
          </button>
          <button
            @click="this.examsIsOpen.open = false"
            type="button"
            class="gthButton"
          >
            Άκυρο
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL EXAMS ΤΕΛΟΣ-->
  <!-- MODAL SHOW EXAMS -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="showIsOpen.open"
  >
    <div
      class="
        flex
        items-end
        justify-center
        min-h-screen
        pt-4
        px-4
        pb-20
        text-center
        sm:block sm:p-0
      "
    >
      <div class="fixed inset-0 transition-opacity">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- This element is to trick the browser into centering the modal contents. -->

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

      <div
        class="
          inline-block
          align-bottom
          bg-white
          rounded-lg
          text-left
          overflow-hidden
          shadow-xl
          transform
          transition-all
          md:my-8 md:align-middle md:max-w-2xl md:w-full
        "
        :class="{
          'lg:max-w-4xl': $page.props.auth.user.permissions.admin,
        }"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-headline"
      >
        <div
          class="
            font-semibold
            text-xl
            p-2
            bg-gray-100
            rounded-t-md
            mx-4
            mt-4
            p-4
          "
        >
          Τα διαγωνίσματά μου
        </div>
        <div class="text-gray-900 p-4" v>
          <div
            class="grid grid-cols-1 gap-1"
            :class="{
              'md:grid-cols-8': !$page.props.auth.user.permissions.admin,
              'md:grid-cols-12': $page.props.auth.user.permissions.admin,
            }"
          >
            <div class="md:text-center font-bold">Α/Α</div>
            <div class="md:text-center font-bold col-span-1 md:col-span-2">
              Ημ/νια
            </div>
            <div class="md:text-center font-bold col-span-1 md:col-span-2">
              Τμήμα
            </div>
            <div class="font-bold col-span-1 md:col-span-4">Μάθημα</div>
            <div
              v-show="$page.props.auth.user.permissions.admin"
              class="font-bold col-span-1 md:col-span-3"
            >
              Καθηγητής
            </div>

            <template v-for="(data, index) in this.userExams.data" :key="index">
              <div
                class="md:text-center"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.aa }}
              </div>
              <div
                class="md:text-center col-span-1 md:col-span-2"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.dateShow }}
              </div>
              <div
                class="md:text-center col-span-1 md:col-span-2 truncate"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.tmima }}
              </div>
              <div
                class="col-span-1 md:col-span-4 truncate"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.mathima }}
              </div>
              <div
                v-show="$page.props.auth.user.permissions.admin"
                class="col-span-1 md:col-span-3 truncate"
                :class="{
                  'opacity-50':
                    data.date <
                    new Date().toISOString().split('T')[0].replace(/-/g, ''),
                }"
              >
                {{ data.teacher }}
              </div>
            </template>
          </div>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            @click="this.showIsOpen.open = false"
            type="button"
            class="gthButton"
          >
            Εντάξει
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL EXAMS ΤΕΛΟΣ-->
</template>

<style lang="postcss" scoped>
.gthButton {
  @apply bg-gray-100  hover:bg-gray-300  active:bg-gray-500  text-gray-700  hover:text-gray-900  active:text-gray-100
        text-sm  font-semibold  py-1 px-2 border border-gray-300  hover:border-transparent rounded-md;
}
</style>

<script>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated.vue";
import { Head } from "@inertiajs/inertia-vue3";
import { Link } from "@inertiajs/inertia-vue3";
import { reactive, ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/inertia-vue3";
import axios from "axios";
import BreezeInput from "@/Components/Input.vue";
import { usePage } from "@inertiajs/inertia-vue3";
import GthSuccess from "@/Components/GthSuccess.vue";
import GthError from "@/Components/GthError.vue";

export default {
  props: {
    mathimata: Array,
    month: Number,
    year: Number,
    dateValues: Object,
    selectedMonth: String,
    exams: Object,
    formExams: Object,
    noExams: Object,
    initGridmode: Number,
  },
  components: {
    BreezeAuthenticatedLayout,
    Head,
    Link,
    BreezeInput,
    GthSuccess,
    GthError,
  },
  setup(props) {
    const gridMode = ref(props.initGridmode);
    const oldDate = ref("");
    const examsIsOpen = reactive({ open: false });
    const showIsOpen = reactive({ open: false });
    const userExams = reactive({ data: [] });
    const examsEditMode = ref(false);
    const tmimata = reactive({ tmima1: [], tmima2: [] });
    const examsForm = useForm(props.formExams);
    const errMsg = reactive({ msg: "" });

    function previous() {
      let mm = props.month - 1;
      let yy = props.year;
      if (mm < 1) {
        mm = 12;
        yy = yy - 1;
      }
      Inertia.get(
        route("exams", { y: yy, m: mm, g: gridMode.value }),
        {},
        {
          preserveScroll: true,
        }
      );
    }

    function next() {
      let mm = props.month + 1;
      let yy = props.year;
      if (mm > 12) {
        mm = 1;
        yy = yy + 1;
      }
      Inertia.get(
        route("exams", { y: yy, m: mm, g: gridMode.value }),
        {},
        {
          preserveScroll: true,
        }
      );
    }

    function dateClicked(date) {
      if (
        !usePage().props.value.auth.user.permissions.admin &&
        props.noExams[date]
      )
        return;
      this.oldDate = date;
      axios.get("exams/tmimata/" + date).then(function (response) {
        tmimata.tmima1 = response.data;
      });
      this.examsEditMode = false;
      this.examsForm.reset();
      this.examsForm.date = date;
      this.errMsg.msg = "";
      this.examsIsOpen.open = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          this.examsIsOpen.open = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function setTmima2Values(event) {
      this.tmimata.tmima2 = [];
      if (event.target.value) {
        Object.keys(this.tmimata.tmima1).forEach((key) => {
          if (
            this.tmimata.tmima1[key] !== event.target.value &&
            this.tmimata.tmima1[key] !== "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ"
          )
            this.tmimata.tmima2.push(this.tmimata.tmima1[key]);
        });
      } else {
        this.examsForm.tmima2 = "";
      }
    }

    function eventClicked(exam) {
      this.oldDate = exam.date;
      if (!usePage().props.value.auth.user.permissions.admin) {
        if (
          parseInt(exam.user_id) !==
          parseInt(usePage().props.value.auth.user.id)
        )
          return;
      }
      tmimata.tmima1 = [exam.tmima1];
      tmimata.tmima2 = [exam.tmima2];
      this.examsEditMode = true;
      this.examsForm.reset();
      this.examsForm.id = exam.id;
      this.examsForm.user_id = exam.user_id;
      this.examsForm.date = exam.date;
      this.examsForm.title = exam.title;
      this.examsForm.tmima1 = exam.tmima1;
      this.examsForm.tmima2 = exam.tmima2;
      this.examsForm.mathima = exam.mathima;
      this.errMsg.msg = "";
      this.examsIsOpen.open = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          this.examsIsOpen.open = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function startDrag(evt, exam) {
      this.oldDate = exam.date;
      evt.dataTransfer.dropEffect = "move";
      evt.dataTransfer.effectAllowed = "move";
      evt.dataTransfer.setData("exam", JSON.stringify(exam));
    }

    function onDrop(evt, date) {
      if (date == this.oldDate) return;
      if (
        !usePage().props.value.auth.user.permissions.admin &&
        props.noExams[date]
      ) {
        this.errMsg.msg =
          "Όχι διαγωνίσματα τις " + new Date(date).toLocaleDateString();
        return;
      }
      const exam = JSON.parse(evt.dataTransfer.getData("exam"));
      Inertia.put(
        route("exams.update", {
          event: exam.id,
          date: date,
        }),
        {},
        {
          preserveScroll: true,
        }
      );
    }

    function setBgColor(exam) {
      if (this.gridMode) {
        if (exam.tmima1 == "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ")
          return "text-white bg-red-500 hover:bg-red-700";
        if (
          exam.date.replace(/-/g, "") <
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id == usePage().props.value.auth.user.id
        )
          return "text-white bg-blue-500 hover:bg-blue-700 opacity-40";
        if (
          exam.date.replace(/-/g, "") <
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id !== usePage().props.value.auth.user.id
        )
          return "text-white bg-gray-500 hover:bg-gray-700 opacity-40";
        if (
          exam.date.replace(/-/g, "") >=
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id == usePage().props.value.auth.user.id
        )
          return "text-white bg-blue-500 hover:bg-blue-700";
        if (
          exam.date.replace(/-/g, "") >=
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id !== usePage().props.value.auth.user.id
        )
          return "text-white bg-gray-500 hover:bg-gray-700";
      } else {
        if (exam.tmima1 == "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ")
          return "text-white bg-red-500 hover:bg-red-700 md:ml-24 ";
        if (
          exam.date.replace(/-/g, "") <
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id == usePage().props.value.auth.user.id
        )
          return "opacity-40 font-semibold text-blue-700 hover:text-blue-900 bg-transparent hover:bg-yellow-100 md:ml-24 border-0";
        if (
          exam.date.replace(/-/g, "") <
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id !== usePage().props.value.auth.user.id
        )
          return "opacity-40 font-semibold text-gray-700 hover:text-gray-900 bg-transparent hover:bg-yellow-100 md:ml-24 border-0";
        if (
          exam.date.replace(/-/g, "") >=
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id == usePage().props.value.auth.user.id
        )
          return "font-semibold text-blue-700 hover:text-blue-900 bg-transparent hover:bg-yellow-100  md:ml-24 border-0";
        if (
          exam.date.replace(/-/g, "") >=
            new Date().toISOString().split("T")[0].replace(/-/g, "") &&
          exam.user_id !== usePage().props.value.auth.user.id
        )
          return "font-semibold text-gray-700 hover:text-gray-900 bg-transparent hover:bg-yellow-100 md:ml-24 border-0";
      }
    }
    function submitExamsForm() {
      if (!this.examsForm.date) {
        this.errMsg.msg = "Επιλέξτε ημερομηνία.";
        return;
      }
      if (!this.examsForm.tmima1) {
        this.errMsg.msg = "Επιλέξτε ένα τμήμα.";
        return;
      }
      if (
        !this.examsForm.mathima &&
        this.examsForm.tmima1 !== "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ"
      ) {
        this.errMsg.msg = "Επιλέξτε ένα μάθημα.";
        return;
      }
      let chkAllowExams = true;
      if (!usePage().props.value.auth.user.permissions.admin) {
        if (props.exams.hasOwnProperty(this.examsForm.date)) {
          props.exams[this.examsForm.date].forEach((exam) => {
            if (exam.tmima1 == "ΟΧΙ_ΔΙΑΓΩΝΙΣΜΑΤΑ") chkAllowExams = false;
          });
        }
      }
      if (!chkAllowExams) {
        this.errMsg.msg =
          "Όχι διαγωνίσματα τις " +
          new Date(this.examsForm.date).toLocaleDateString();
        return;
      }
      if (!this.examsForm.id) {
        this.examsForm.post(route("exams.store"), {
          preserveScroll: true,
        });
        this.errMsg.msg = "";
        this.examsIsOpen.open = false;
        this.examsForm.reset();
      } else {
        if (this.examsForm.date == this.oldDate) {
          this.errMsg.msg = "";
          this.examsIsOpen.open = false;
          this.examsForm.reset();
          return;
        }
        Inertia.put(
          route("exams.update", {
            event: this.examsForm.id,
            date: this.examsForm.date,
          }),
          {},
          {
            preserveScroll: true,
          }
        );
        examsIsOpen.open = false;
        examsForm.reset();
      }
    }

    function deleteExam() {
      Inertia.delete(route("deleteExam", { id: this.examsForm.id }), {
        onBefore: () =>
          confirm(
            "Διαγραφή διαγωνίσματος\n\nΗμνια: " +
              new Date(this.examsForm.date).toLocaleDateString() +
              "\n" +
              this.examsForm.title +
              "\n\nΝα διαγραφεί;"
          ),
        preserveScroll: true,
      });
      this.examsIsOpen.open = false;
    }

    function myExams() {
      axios.get("userExams").then(function (response) {
        userExams.data = response.data;
        showIsOpen.open = true;
        const onEscape = (e) => {
          if (e.keyCode === 27) {
            showIsOpen.open = false;
            document.removeEventListener("keydown", onEscape);
          }
        };
        document.addEventListener("keydown", onEscape);
      });
    }

    function printTime() {
      return new Date().toLocaleTimeString();
    }

    return {
      previous,
      next,
      gridMode,
      myExams,
      dateClicked,
      eventClicked,
      startDrag,
      onDrop,
      examsIsOpen,
      showIsOpen,
      examsEditMode,
      examsForm,
      errMsg,
      setBgColor,
      tmimata,
      setTmima2Values,
      submitExamsForm,
      deleteExam,
      printTime,
      oldDate,
      userExams,
    };
  },
};
</script>
