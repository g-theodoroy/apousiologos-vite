<template>
  <Head title="Καθηγητές" />

  <BreezeAuthenticatedLayout>
    <template #header>
      <AdminLayout></AdminLayout>
      <div class="flex justify-between pt-2 md:pt-6">
        <div class="font-semibold text-xl text-gray-800 leading-tight">
          Καθηγητές
        </div>
        <button @click="editTeacher()" class="gthButton mr-10 md:mr-0">
          Εισαγωγή καθηγητή
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
                w-full
                flex-row flex-no-wrap
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
                      'text-left sm:text-center': index < 1,
                      'sm:hidden lg:table-cell':
                        index == this.tableLabels.length - 2,
                    }"
                    :title="
                      index > 0 && index < 4
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
                <tr
                  v-for="(teacher, index) in teachers.data"
                  :key="teacher.name"
                  class="
                    flex flex-col flex-no
                    wrap
                    sm:table-row
                    hover:bg-gray-100
                    text-sm
                    align-text-top
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
                    {{ index + teachers.from }}
                  </td>
                  <td class="border-grey-light border p-2">
                    {{ teacher.role_id == 1 ? "&#9889;" : "" }}
                    {{ teacher.name }}
                  </td>
                  <td class="border-grey-light border p-2">
                    {{ teacher.email }}
                  </td>
                  <td
                    class="
                      border-grey-light border
                      p-2
                      sm:hidden
                      lg:table-cell
                      overflow-hidden
                    "
                    v-html="teacher.strAnatheseis"
                  ></td>
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
                      @click="editTeacher(teacher)"
                      class="bg-yellow-200 px-0.5 border rounded"
                      :title="'Επεξεργασία ' + teacher.name"
                    >
                      &#128395;
                    </button>
                    <button
                      v-show="firstUserId !== teacher.id"
                      @click="deleteTeacher(teacher)"
                      class="bg-red-200 px-1 border rounded"
                      :title="'Διαγραφή ' + teacher.name"
                    >
                      &#128465;
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            <Pagination
              class="mx-auto pt-4"
              :links="teachers.links"
              :queryStr="queryStr"
            />
          </div>
        </div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
  <!-- MODAL TEACHER -->
  <div
    class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400"
    v-if="teachIsOpen.open"
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
          md:max-w-3xl
          lg:max-w-4xl
        "
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-headline"
      >
        <div
          class="font-semibold text-xl p-2 bg-gray-100 rounded-t-md mx-4 mt-4"
        >
          {{ this.teachEditMode ? "Επεξεργασία" : "Εισαγωγή" }} καθηγητή
        </div>
        <div v-show="this.errMsg.msg" class="text-red-500 mx-4 p-2 text-center">
          {{ this.errMsg.msg }}
        </div>
        <div
          class="
            grid grid-cols-3
            sm:grid-cols-6
            gap-1
            sm:gap-2
            bg-white
            px-4
            pt-5
            pb-4
            sm:p-6 sm:pb-4
          "
        >
          <span class="self-center">Ονομ/νυμο</span>
          <BreezeInput
            v-model="teachForm.name"
            class="w-full p-1 col-span-2 sm:col-span-3 border"
          />
          <span class="self-center sm:justify-self-end">Διαχειριστής</span>
          <BreezeCheckbox
            v-model="teachForm.role_id"
            :checked="teachForm.role_id"
            :disabled="chkDisabled"
            class="self-center justify-self-center col-span-2 sm:col-span-1"
          />
          <span class="self-center">Email</span>
          <BreezeInput
            v-model="teachForm.email"
            class="w-full p-1 col-span-2 border"
          />
          <span class="self-center">Password</span>
          <BreezeInput
            v-model="teachForm.password"
            class="w-full p-1 col-span-2 border"
          />
          <span class="self-baseline pl-6">Τμήμα</span>
          <span class="col-span-2 self-baseline">Μάθημα</span>
          <span class="hidden sm:table-cell self-baseline pl-6">Τμήμα</span>
          <span class="hidden sm:table-cell col-span-2 self-baseline"
            >Μάθημα</span
          >
          <template v-for="index in this.formRows" :key="index">
            <div class="flex flex-row">
              <span
                class="self-center p-1 bg-gray-100 rounded-md mr-1 font-bold"
                >{{ (index - 1) * 2 + 1 }}
              </span>
              <BreezeInput
                v-model="teachForm.anathesi[(index - 1) * 2].tmima"
                class="w-full p-1 border"
              />
            </div>
            <BreezeInput
              v-model="teachForm.anathesi[(index - 1) * 2].mathima"
              class="w-full p-1 col-span-2 border"
            />
            <div class="flex flex-row">
              <span
                class="self-center p-1 bg-gray-100 rounded-md mr-1 font-bold"
                >{{ (index - 1) * 2 + 1 + 1 }}
              </span>
              <BreezeInput
                v-model="teachForm.anathesi[(index - 1) * 2 + 1].tmima"
                class="w-full p-1 border"
              />
            </div>
            <BreezeInput
              v-model="teachForm.anathesi[(index - 1) * 2 + 1].mathima"
              class="w-full p-1 col-span-2 border"
            />
          </template>
        </div>
        <div class="bg-gray-100 px-4 py-3 sm:px-6 text-right space-x-2">
          <button
            @click="submitTeachForm"
            type="button"
            :disabled="!this.teachForm.isDirty"
            class="gthButton disabled:opacity-50"
            :class="{ 'cursor-not-allowed': !this.teachForm.isDirty }"
          >
            Αποθήκευση
          </button>
          <button
            @click="this.teachIsOpen.open = false"
            type="button"
            class="gthButton"
          >
            Άκυρο
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- MODAL teacher ΤΕΛΟΣ-->
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
    firstUserId: Number,
    teachers: Object,
    tableLabels: Array,
    filters: Object,
    fields: Array,
    queryStr: String,
    formTeachers: Object,
    initFormRows: Number,
  },
  setup(props) {
    const formRows = ref(1);
    const chkDisabled = ref(false);
    const teachIsOpen = reactive({ open: false });
    const teachEditMode = ref(false);
    const errMsg = reactive({ msg: "" });
    const teachForm = useForm(props.formTeachers);
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
      Inertia.get(route("teachers"), prms, {
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
    function editTeacher(teacher) {
      this.teachForm.reset();
      this.errMsg.msg = "";
      if (teacher) {
        this.formRows = Math.ceil(teacher.anatheseis.length / 2 + 0.5);
        this.teachEditMode = true;
        this.teachForm.id = teacher.id;
        this.teachForm.name = teacher.name;
        this.teachForm.email = teacher.email;
        this.teachForm.role_id = teacher.role_id == 1 ? true : false;
        this.chkDisabled = teacher.role_id == 1 ? true : false;
        if (teacher.anatheseis) {
          teacher.anatheseis.forEach((anath, ind) => {
            this.teachForm.anathesi[ind].tmima = anath.tmima;
            this.teachForm.anathesi[ind].mathima = anath.mathima;
          });
        }
      } else {
        this.teachEditMode = false;
        this.formRows = props.initFormRows;
      }
      this.teachIsOpen.open = true;
      const onEscape = (e) => {
        if (e.keyCode === 27) {
          this.teachIsOpen.open = false;
          document.removeEventListener("keydown", onEscape);
        }
      };
      document.addEventListener("keydown", onEscape);
    }

    function deleteTeacher(teacher) {
      Inertia.delete(`deleteTeacher/${teacher.id}`, {
        onBefore: () => confirm(teacher.name + "\n\nΘέλετε να διαγραφεί?"),
        preserveScroll: true,
      });
    }

    function submitTeachForm() {
      if (!this.teachForm.name) {
        this.errMsg.msg = "Συμπληρώστε το Ονοματεπώνυμο.";
        return;
      }
      if (!this.teachForm.email) {
        this.errMsg.msg = "Συμπληρώστε το Email.";
        return;
      }
      let validRegex =
        /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
      if (this.teachForm.email && !this.teachForm.email.match(validRegex)) {
        this.errMsg.msg = `Το email "${this.teachForm.email}" δεν είναι έγκυρο.`;
        return;
      }
      if (!this.teachForm.id && !this.teachForm.password) {
        this.errMsg.msg = "Συμπληρώστε το Password.";
        return;
      }
      let sameEmail = true;
      if (this.teachEditMode) {
        props.teachers.data.forEach((teacher) => {
          if (this.teachForm.id == teacher.id) {
            if (this.teachForm.email !== teacher.email) sameEmail = false;
            return;
          }
        });
        if (sameEmail) {
          this.teachForm.post(route("teachers.store"), {
            preserveScroll: true,
          });
          this.teachIsOpen.open = false;
          this.teachForm.reset();
        }
      }
      if (!this.teachEditMode || !sameEmail) {
        let email = this.teachForm.email;
        axios.get("uniqueEmail/" + email).then(function (response) {
          if (response.data > 0) {
            errMsg.msg = `Το Email "${email}" χρησιμοποιείται.`;
            return;
          } else {
            teachForm.post(route("teachers.store"), { preserveScroll: true });
            teachIsOpen.open = false;
            teachForm.reset();
          }
        });
      }
    }

    function printTime() {
      return new Date().toLocaleTimeString();
    }
    return {
      teachIsOpen,
      teachEditMode,
      errMsg,
      params,
      sort,
      printTime,
      teachForm,
      editTeacher,
      deleteTeacher,
      submitTeachForm,
      formRows,
    };
  },
};
</script>
