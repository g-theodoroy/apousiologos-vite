<template>
  <Head title="Μαθητές" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <AdminLayout></AdminLayout>
      <div class="flex justify-between pt-2 md:pt-6">
        <div class="font-semibold text-xl text-gray-800 leading-tight">
          Μαθητές
        </div>
        <button @click="editStudent()" class="gthButton mr-10 md:mr-0">
          Εισαγωγή μαθητή
        </button>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <GthSuccess>
            {{ this.printTime() }}
          </GthSuccess>
          <!-- ΓΡΑΜΜΕΣ & ΑΝΑΖΗΤΗΣΗ -->
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between space-x-2">
              <select
                v-model="params.rows"
                :value="filters.rows"
                class="
                  border-gray-300
                  focus:border-indigo-300
                  focus:ring
                  focus:ring-indigo-200
                  focus:ring-opacity-100
                  rounded-md
                  shadow-sm
                  p-1
                  sm:px-2
                  w-20
                "
              >
                <option
                  v-for="item in [10, 20, 50, 100]"
                  :key="item"
                  :value="item"
                >
                  {{ item }}
                </option>
              </select>
              <BreezeInput
                v-model="params.search"
                type="text"
                class="w-3/4 sm:w-auto"
                placeholder="Αναζήτηση"
              />
            </div>
            <!-- ΓΡΑΜΜΕΣ & ΑΝΑΖΗΤΗΣΗ  ΤΕΛΟΣ-->

            <table
              class="
                w-full flex-row flex-no-wrap
                bg-white
                rounded-lg
                overflow-hidden
                sm:shadow-lg
                my-2
                inline-table
              "
            >
              <tbody class="flex-1 sm:flex-none space-y-2">
                <tr
                  class="
                    text-white
                    bg-gray-400
                    flex flex-col flex-no
                    wrap
                    sm:table-row
                    rounded-t-lg
                  "
                >
                  <td
                    v-for="(label, index) in this.tableLabels"
                    :key="label"
                    class="p-2 font-semibold"
                    :class="{
                      'text-left sm:text-center': index < 3,
                      'sm:hidden xl:table-cell':
                        index == this.tableLabels.length - 2,
                      'sm:hidden lg:table-cell':
                        index == this.tableLabels.length - 3,
                      'sm:hidden md:table-cell':
                        index == this.tableLabels.length - 4,
                    }"
                    :title="
                      index > 0 && index < 8
                        ? params.field !== fields[index] ||
                          (params.field == fields[index] &&
                            params.direction == 'desc')
                          ? 'αύξουσα ' + 'ταξινόμηση κατά ' + label
                          : 'φθίνουσα ' + 'ταξινόμηση κατά ' + label
                        : ''
                    "
                    @click="sort(this.fields[index])"
                  >
                    <span
                      v-show="
                        params.field == fields[index] &&
                        params.direction == 'asc'
                      "
                      >&#8599;</span
                    >
                    <span
                      v-show="
                        params.field == fields[index] &&
                        params.direction == 'desc'
                      "
                      >&#8600;</span
                    >
                    {{ label }}
                  </td>
                </tr>
                <template
                  v-for="(student, index) in students.data"
                  :key="student.id"
                >
                  <tr
                    class="
                      flex flex-col flex-no
                      wrap
                      sm:table-row
                      hover:bg-gray-100
                      text-sm
                    "
                  >
                    <td
                      class="
                        border-grey-light border
                        hover:bg-gray-100
                        p-2
                        text-left
                        sm:text-center
                      "
                    >
                      {{ index + students.from }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        text-left
                        sm:text-center
                      "
                    >
                      {{ student.id }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        text-left
                        sm:text-center
                      "
                    >
                      {{ student.sumap == 0 ? "&nbsp;" : student.sumap }}
                    </td>
                    <td class="border-grey-light border p-2">
                      {{ student.eponimo }}
                    </td>
                    <td class="border-grey-light border p-2">
                      {{ student.onoma }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        sm:hidden
                        md:table-cell
                      "
                    >
                      {{ student.patronimo }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        sm:hidden
                        lg:table-cell
                      "
                    >
                      {{ student.email }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        sm:hidden
                        xl:table-cell
                      "
                    >
                      {{ student.tmimataStr }}
                    </td>
                    <td
                      class="
                        border-grey-light border
                        p-2
                        truncate
                        text-left
                        sm:text-center
                        space-x-2
                      "
                    >
                      <button
                        @click="
                          student.sumap
                            ? (showApouForStu[student.id] =
                                !showApouForStu[student.id])
                            : false
                        "
                        class="
                          px-0.5
                          border
                          rounded
                          bg-green-200
                          disabled:opacity-30
                          disabled:bg-gray-100
                          disabled:cursor-not-allowed
                        "
                        :class="{
                          'cursor-not-allowed': !student.sumap,
                        }"
                        :disabled="!student.sumap"
                        :title="
                          student.sumap
                            ? showApouForStu[student.id]
                              ? 'Κρύψε απουσίες για ' +
                                student.eponimo +
                                ' ' +
                                student.onoma
                              : 'Δείξε απουσίες για ' +
                                student.eponimo +
                                ' ' +
                                student.onoma
                            : ''
                        "
                      >
                        {{
                          showApouForStu[student.id] ? "&#10134;" : "&#128065;"
                        }}
                      </button>
                      <button
                        @click="editApousies(student)"
                        class="bg-blue-200 px-0.5 border rounded"
                        :title="
                          'Εισαγωγή απουσιών ' +
                          student.eponimo +
                          ' ' +
                          student.onoma
                        "
                      >
                        &#10133;
                      </button>
                      <button
                        @click="editStudent(student)"
                        class="bg-yellow-200 px-0.5 border rounded"
                        :title="
                          'Επεξεργασία ' + student.eponimo + ' ' + student.onoma
                        "
                      >
                        &#128395;
                      </button>
                      <button
                        @click="deleteStudent(student)"
                        class="bg-red-200 px-1 border rounded"
                        :title="
                          'Διαγραφή ' + student.eponimo + ' ' + student.onoma
                        "
                      >
                        &#128465;
                      </button>
                    </td>
                  </tr>
                  <tr
                    v-show="showApouForStu[student.id]"
                    class="flex flex-col flex-no wrap sm:table-row bg-blue-100"
                  >
                    <td :colspan="tableLabels.length" class="text-center">
                      <!-- ΤΑΒΛΕ ΑΠΟΥΣΙΕΣ -->
                      <table
                        class="
                          w-11/12
                          sm:w-10/12
                          bg-white
                          rounded-lg
                          overflow-hidden
                          sm:shadow-lg
                          my-2
                          inline-table
                        "
                      >
                        <tbody class="flex-1 sm:flex-none">
                          <tr
                            class="
                              text-white
                              bg-gray-400
                              table-row
                              rounded-t-lg
                            "
                          >
                            <td
                              v-for="(label, index) in this.tableApouLabels"
                              :key="label"
                              class="p-1 sm:px-2 font-semibold"
                              :class="{
                                'hidden sm:table-cell':
                                  index > 3 &&
                                  index < this.tableApouLabels.length - 1,
                              }"
                            >
                              {{ label }}
                            </td>
                          </tr>
                          <template
                            v-for="apousies in apousiesForStudent[student.id]"
                            :key="apousies.id"
                          >
                            <tr class="table-row hover:bg-gray-100 text-sm">
                              <td
                                rowspan="2"
                                class="
                                  border-grey-light border
                                  p-1
                                  text-center
                                  sm:hidden
                                "
                              >
                                {{ apousies.aa }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  px-2
                                  text-center
                                  hidden
                                  sm:table-cell
                                "
                              >
                                {{ apousies.aa }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                "
                              >
                                {{ apousies.tot }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                "
                              >
                                {{ apousies.dateShow }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                  font-bold
                                "
                              >
                                {{
                                  apousies.sum == 0 ? "&nbsp;" : apousies.sum
                                }}
                              </td>
                              <td
                                v-for="(day, index) in apousies.arrApou['apou']"
                                :key="index"
                                class="
                                  border-gray-500 border border-l-2 border-r-2
                                  p-1
                                  sm:px-2
                                  text-center
                                  hidden
                                  sm:table-cell
                                "
                                :class="{
                                  'bg-red-100':apousies.arrApou.apov[index]
                                }"
                                :title="arrNames[apousies.arrApou.teach[index]]"
                              >
                                {{ day ? "+" : "&nbsp;" }}
                              </td>
                              <td
                                class="
                                  border-grey-light border
                                  p-1
                                  sm:px-2
                                  text-center
                                  space-x-2
                                "
                              >
                                <button
                                  @click="editApousies(student, apousies)"
                                  class="bg-yellow-200 px-0.5 border rounded"
                                  :title="
                                    'Επεξεργασία απουσιών για ' +
                                    apousies.dateShow
                                  "
                                >
                                  &#128395;
                                </button>
                                <button
                                  @click="deleteApousies(student, apousies)"
                                  class="bg-red-200 px-1 border rounded"
                                  :title="
                                    'Διαγραφή απουσιών για ' + apousies.dateShow
                                  "
                                >
                                  &#128465;
                                </button>
                              </td>
                            </tr>
                            <tr class="sm:hidden hover:bg-gray-100">
                              <td colspan="5" class="border text-xs">
                                <table class="w-full">
                                  <tr>
                                    <td class="w-16 text-right">ώρες:</td>

                                    <td
                                      v-for="(chk, index) in apousies.arrApou.apou"
                                      :key="index"
                                      class="w-4"
                                      :class="{
                                        'opacity-20': !chk,
                                        'font-medium': chk,
                                      }"
                                    >
                                      {{ index + "η" }}
                                    </td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </template>
                        </tbody>
                      </table>
                      <!-- ΤΑΒΛΕ ΑΠΟΥΣΙΕΣ ΤΕΛΟΣ -->
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>

            <Pagination
              class="mx-auto pt-4"
              :links="students.links"
              :queryStr="queryStr"
            />
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>

  <!-- MODAL STUDENT -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="stuIsOpen.open"
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
          {{ this.stuEditMode ? "Επεξεργασία" : "Εισαγωγή" }} μαθητή
        </div>
        <div v-show="this.errMsg.msg" class="text-red-500 mx-4 p-2 text-center">
          {{ this.errMsg.msg }}
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
          <span class="sm:col-start-3">Α.Μ.</span>
          <BreezeInput
            v-model="stuForm.id"
            :disabled="this.stuEditMode"
            class="w-full p-1 text-center"
            :class="{
              border: !this.stuEditMode,
              'select-none': this.stuEditMode,
            }"
          />
          <span class="sm:col-span-2">&nbsp;</span>
          <span>Επώνυμο</span>
          <BreezeInput
            v-model="stuForm.eponimo"
            class="w-full p-1 col-span-2 border"
          />
          <span>Όνομα</span>
          <BreezeInput
            v-model="stuForm.onoma"
            class="w-full p-1 col-span-2 border"
          />
          <span>Πατρώνυμο</span>
          <BreezeInput
            v-model="stuForm.patronimo"
            class="w-full p-1 col-span-2 border"
          />
          <span>Email</span>
          <BreezeInput
            v-model="stuForm.email"
            class="w-full p-1 col-span-2 border"
          />
          <template v-for="index in this.tmimataRows" :key="index">
            <span>Τμήμα</span>
            <BreezeInput
              v-model="stuForm.tmima[(index - 1) * 2]"
              class="w-full p-1 col-span-2 border"
            />
            <span>Τμήμα</span>
            <BreezeInput
              v-model="stuForm.tmima[(index - 1) * 2 + 1]"
              class="w-full p-1 col-span-2 border"
            />
          </template>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            @click="submitStuForm"
            type="button"
            :disabled="!this.stuForm.isDirty"
            class="gthButton disabled:opacity-50"
            :class="{ 'cursor-not-allowed': !this.stuForm.isDirty }"
          >
            Αποθήκευση
          </button>
          <button
            @click="this.stuIsOpen.open = false"
            type="button"
            class="gthButton"
          >
            Άκυρο
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL STUDENT ΤΕΛΟΣ-->
  <!-- MODAL APOUSIES -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="apouIsOpen"
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
          {{ this.apouEditMode ? "Επεξεργασία" : "Εισαγωγή" }} απουσιών
        </div>
        <div
          v-show="this.errMsg.msg"
          class="text-red-500 mx-4 mt-2 text-center"
        >
          {{ this.errMsg.msg }}
        </div>
        <div class="font-semibold text-xl mx-4 mt-2 text-center">
          {{ this.stuForApousies }}
        </div>
        <div class="flex bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 justify-evenly">
          <div class="flex">
            <BreezeInput
              v-model="apouForm.date"
              type="date"
              :disabled="this.apouEditMode"
              class="w-max p-1 text-center"
              :class="{
                border: !this.apouEditMode,
                'select-none': this.apouEditMode,
              }"
            />
          </div>
          <div class="flex bg-gray-100 space-x-1 pb-1 mx-2 rounded-lg">
            <div
              v-for="index in totalHours"
              :key="index"
              class="text-gray-400 sm:px-1"
            >
              <div class="flex flex-col"
               :class="{
                  'bg-red-200':apouForm.apov[index]
                }"
                :title="arrNames[apouForm.teach[index]]"
              >
                <span>{{ index }}η</span>
                <BreezeCheckbox
                  class="ml-0.5"
                  v-model="apouForm.apou[index]"
                  :checked="apouForm.apou[index]"
                  @click="toggleApousia(index)"
 
                />

                    <!-- κουμπί αποβολής -->
                    <div 
                      class="h-2 bg-gray-300 mt-1 cursor-pointer rounded" 
                      :class="{
                          'bg-red-400':apouForm.apov[index]
                        }"
                      title="Αποβολή" 
                      @click="toggleApovoli(index)"
                    />

              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            @click="submitApouForm"
            type="button"
            :disabled="!this.apouForm.isDirty"
            class="gthButton disabled:opacity-50"
            :class="{ 'cursor-not-allowed': !this.apouForm.isDirty }"
          >
            Αποθήκευση
          </button>
          <button
            @click="this.apouIsOpen = false"
            type="button"
            class="gthButton"
          >
            Άκυρο
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL APOUSIES ΤΕΛΟΣ-->
</template>
<style lang="postcss" scoped>
.gthButton {
  @apply bg-gray-100  hover:bg-gray-300  active:bg-gray-500  text-gray-700  hover:text-gray-900  active:text-gray-100
        text-sm  font-semibold  py-1 px-2 border border-gray-300  hover:border-transparent rounded-md;
}
</style>
<script>
import BreezeAuthenticatedLayout from "@/Layouts/Authenticated.vue";
import AdminLayout from "@/Layouts/AdminMenu.vue";
import { Head } from "@inertiajs/inertia-vue3";
import { Link } from "@inertiajs/inertia-vue3";
import Pagination from "@/Components/Pagination.vue";
import BreezeInput from "@/Components/Input.vue";
import { watch, reactive, ref, onMounted, onUnmounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/inertia-vue3";
import { usePage } from "@inertiajs/inertia-vue3";
import axios from "axios";
import GthSuccess from "@/Components/GthSuccess.vue";
import BreezeCheckbox from "@/Components/Checkbox.vue";

export default {
  components: {
    BreezeAuthenticatedLayout,
    AdminLayout,
    Head,
    Pagination,
    Link,
    BreezeInput,
    BreezeCheckbox,
    GthSuccess,
  },
  props: {
    students: Object,
    arrNames: Object,
    tableLabels: Array,
    filters: Object,
    fields: Array,
    queryStr: String,
    iniShowApouForStu: Object,
    tableApouLabels: Array,
    apousiesForStudent: Object,
    tmimataRows: Number,
    totalHours: Number,
    formStudents: Object,
    formApousies: Object,
  },
  setup(props) {
    const showApouForStu = ref(props.iniShowApouForStu);
    const stuIsOpen = reactive({ open: false });
    const apouIsOpen = ref(false);
    const stuEditMode = ref(false);
    const apouEditMode = ref(false);
    const stuForApousies = ref("");
    const stuForm = useForm(props.formStudents);
    const apouForm = useForm(props.formApousies);
    const errMsg = reactive({ msg: "" });
    const params = reactive({
      page: props.filters.page,
      rows: props.filters.rows,
      search: props.filters.search,
      field: props.filters.field,
      direction: props.filters.direction,
    });
    watch(params, (currentValue, oldValue) => {
      let prms = params;
      Object.keys(prms).forEach((key) => {
        if (prms[key] == "") {
          delete prms[key];
        }
      });
      Inertia.get(route("students"), prms, {
        replace: true,
        preserveState: true,
        preserveScroll: true,
      });
    });

    function sort(field) {
      if (field == "") return;
      if (params.field !== field) {
        params.direction = "asc";
      } else {
        params.direction = params.direction == "asc" ? "desc" : "asc";
      }
      params.field = field;
    }

    function editStudent(student) {
      this.stuForm.reset();
      this.errMsg.msg = "";
      if (student) {
        this.stuEditMode = true;
        this.stuForm.id = student.id;
        this.stuForm.eponimo = student.eponimo;
        this.stuForm.onoma = student.onoma;
        this.stuForm.patronimo = student.patronimo;
        this.stuForm.email = student.email;
        if (student.tmimataStr) {
          student.tmimataStr.split(", ").forEach((tmima, ind) => {
            this.stuForm.tmima[ind] = tmima;
          });
        }
      } else {
        this.stuEditMode = false;
      }
      this.stuIsOpen.open = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          this.stuIsOpen.open = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function deleteStudent(student) {
      Inertia.delete(`studentDelete/${student.id}`, {
        onBefore: () =>
          confirm(
            student.eponimo + " " + student.onoma + "\n\nΘέλετε να διαγραφεί?"
          ),
        preserveScroll: true,
      });
    }

    function editApousies(student, apousies) {
      this.apouForm.reset();
      this.errMsg.msg = "";
      this.stuForApousies = student.eponimo + " " + student.onoma;
      this.apouForm.student_id = student.id;
      if (apousies) {
        this.apouEditMode = true;
        this.apouForm.date = apousies.date;
        for (var key in apousies.arrApou.apou) {
          this.apouForm['apou'][key] = apousies.arrApou.apou[key];
          this.apouForm['apov'][key] = apousies.arrApou.apov[key];
          this.apouForm['teach'][key] = apousies.arrApou.teach[key];
        }
      } else {
        this.apouEditMode = false;
        this.apouForm.date = new Date().toISOString().split("T")[0];
      }
      this.apouIsOpen = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          this.apouIsOpen = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function deleteApousies(student, apousies) {
      Inertia.delete(`apousiesDelete/${apousies.id}`, {
        onBefore: () =>
          confirm(
            student.eponimo +
              " " +
              student.onoma +
              "\nΗμνια: " +
              apousies.dateShow +
              "\nΑπουσίες: " +
              apousies.sum +
              "\n\nΘέλετε να διαγραφoύν οι απουσίες?"
          ),
        preserveScroll: true,
      });
      if (props.apousiesForStudent[student.id].length < 2) {
        this.showApouForStu[student.id] = false;
      }
    }

    function submitStuForm() {
      if (!this.stuForm.id) {
        this.errMsg.msg = "Συμπληρώστε τον Αρ.Μητρώου";
        return;
      }
      if (isNaN(this.stuForm.id)) {
        this.errMsg.msg = `Ο Αρ. Μητρώου "${this.stuForm.id}" δεν είναι αριθμός`;
        return;
      }
      if (!this.stuForm.eponimo) {
        this.errMsg.msg = "Συμπληρώστε το Επώνυμο";
        return;
      }
      if (!this.stuForm.onoma) {
        this.errMsg.msg = "Συμπληρώστε το Όνομα";
        return;
      }
      let validRegex =
        /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
      if (this.stuForm.email && !this.stuForm.email.match(validRegex)) {
        this.errMsg.msg = `Το email "${this.stuForm.email}" δεν είναι έγκυρο`;
        return;
      }
      if (this.stuEditMode) {
        stuForm.post(route("students.store"), { preserveScroll: true });
        this.stuForm.reset();
        this.stuIsOpen.open = false;
      } else {
        let id = this.stuForm.id;
        axios.get("studentUnique/" + id).then(function (response) {
          if (response.data > 0) {
            errMsg.msg = `Ο Αρ. Μητρώου "${id}" χρησιμοποιείται`;
            return;
          } else {
            stuForm.post(route("students.store"), { preserveScroll: true });
            stuForm.reset();
            stuIsOpen.open = false;
          }
        });
      }
    }

    function submitApouForm() {
      if (!this.apouForm.date) {
        this.errMsg.msg = "Επιλέξτε την ημερομηνία";
        return;
      }
      let apouExist = false;
      for (var key in this.apouForm['apou']){
        if (!isNaN(key)) {
          if (this.apouForm['apou'][key]) apouExist = true;
        }
      }
      if (!apouExist) {
        this.errMsg.msg = "Καταχωρίστε τούλαχιστον μία απουσία";
        return;
      }

      if (!this.apouEditMode) {
        let sameDate = null;
        props.apousiesForStudent[this.apouForm.student_id].forEach(
          (apousies) => {
            if (apousies.date == this.apouForm.date) {
              sameDate = apousies.dateShow;
              return;
            }
          }
        );
        if (sameDate) {
          if (
            !confirm(
              "Υπάρχουν καταχωρισμένες απουσίες τις " +
                sameDate +
                ".\nΑν συνεχίσετε θα αντικατασταθούν από τις παρούσες.\nΘέλετε ωστόσο να συνεχίσετε;"
            )
          )
            return;
        }
      }
      this.apouForm.post(route("apousiesStore"), { preserveScroll: true });
      this.apouForm.reset();
      this.apouIsOpen = false;
    }

    function printTime() {
      return new Date().toLocaleTimeString();
    }

        function toggleApovoli(index){
      if(apouForm['apov'][index]==false) {
        apouForm['apou'][index]=true
        apouForm['apov'][index]=true
        apouForm['teach'][index]=usePage().props.value.auth.user.id
      }else{
        apouForm['apov'][index]=false
      }
    }

    function toggleApousia(index){
      if(apouForm['apou'][index]==true) {
        apouForm['apov'][index]=false
        apouForm['teach'][index]=''
      }else{
        apouForm['teach'][index]=usePage().props.value.auth.user.id
      }
    }


    return {
      showApouForStu,
      params,
      stuIsOpen,
      apouIsOpen,
      sort,
      deleteStudent,
      editApousies,
      deleteApousies,
      editStudent,
      stuEditMode,
      apouEditMode,
      stuForApousies,
      stuForm,
      apouForm,
      submitStuForm,
      submitApouForm,
      errMsg,
      printTime,
      toggleApovoli,
      toggleApousia,
    };
  },
};
</script>
