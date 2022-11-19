<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="bg-white border-b border-gray-100">
      <!-- Primary Navigation Menu -->
      <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex-col flex items-center">
              <Link :href="route('welcome')">
                <BreezeApplicationLogo class="block h-9" />
              </Link>
              <div class="pt-1 text-sm text-center text-gray-900 font-normal">
                {{ $page.props.schoolName }}
              </div>
            </div>

            <!-- Navigation Links -->
            <div
              class="hidden space-x-8 md:-my-px md:ml-10 md:flex"
              v-show="
                $page.props.auth.user
                  ? $page.props.auth.user.permissions.admin
                  : false
              "
            >
              <BreezeNavLink
                :href="route('settings')"
                :active="
                  route().current('teachers') ||
                  route().current('students') ||
                  route().current('settings') ||
                  route().current('importXls') ||
                  route().current('exportXls')
                "
              >
                Διαχείριση
              </BreezeNavLink>
            </div>

            <div
              v-show="$page.props.auth.user"
              class="hidden space-x-8 md:-my-px md:ml-10 md:flex"
            >
              <BreezeNavLink
                :href="route('apousiologos')"
                :active="route().current('apousiologos')"
              >
                Απουσιολόγος
              </BreezeNavLink>
            </div>

            <div
              class="hidden space-x-8 md:-my-px md:ml-10 md:flex"
          v-show="
            ($page.props.auth.user
              ? $page.props.auth.user.permissions.admin
              : false) ||
            ($page.props.allowExams &&
              ($page.props.auth.user
                ? $page.props.auth.user.permissions.teacher
                : false))
          "
            >
              <BreezeNavLink
                :href="route('exams')"
                :active="route().current('exams')"
              >
                Διαγωνίσματα
              </BreezeNavLink>
            </div>

            <div
              class="hidden space-x-8 md:-my-px md:ml-10 md:flex"
              v-show="
                ($page.props.auth.user
                  ? $page.props.auth.user.permissions.admin
                  : false) ||
                ($page.props.activeGradesPeriod &&
                  ($page.props.auth.user
                    ? $page.props.auth.user.permissions.teacher
                    : false))
              "
            >
              <BreezeNavLink
                :href="route('grades')"
                :active="route().current('grades')"
              >
                Βαθμολογία
              </BreezeNavLink>
            </div>
            <div
              v-show="!$page.props.auth.user"
              class="hidden space-x-8 md:-my-px md:ml-10 md:flex"
            >
              <BreezeNavLink :href="route('welcome')"> Αρχική </BreezeNavLink>
            </div>
            <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
              <BreezeNavLink
                :href="route('about')"
                :active="route().current('about')"
              >
                Πληροφορίες
              </BreezeNavLink>
            </div>
          </div>

          <div class="hidden md:flex md:items-center md:ml-6">
            <!-- Settings Dropdown -->
            <div v-show="$page.props.auth.user" class="ml-3 relative">
              <BreezeDropdown align="right" width="48">
                <template #trigger>
                  <span class="inline-flex rounded-md">
                    <button
                      type="button"
                      class="
                        inline-flex
                        items-center
                        px-3
                        py-2
                        border border-transparent
                        text-sm text-left
                        leading-4
                        font-medium
                        rounded-md
                        text-gray-500
                        bg-white
                        hover:text-gray-700
                        focus:outline-none
                        transition
                        ease-in-out
                        duration-150
                      "
                    >
                      {{
                        $page.props.auth.user ? $page.props.auth.user.name : ""
                      }}<br />{{
                        $page.props.auth.user ? $page.props.auth.user.email : ""
                      }}

                      <svg
                        class="ml-2 -mr-0.5 h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </span>
                </template>

                <template #content>
                  <BreezeDropdownLink
                    :href="route('logout')"
                    method="post"
                    as="button"
                  >
                    Έξοδος
                  </BreezeDropdownLink>
                </template>
              </BreezeDropdown>
            </div>
          </div>

          <!-- Hamburger -->
          <div class="-mr-2 flex items-center md:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="
                inline-flex
                items-center
                justify-center
                p-2
                rounded-md
                text-gray-400
                hover:text-gray-500 hover:bg-gray-100
                focus:outline-none focus:bg-gray-100 focus:text-gray-500
                transition
                duration-150
                ease-in-out
              "
            >
              <svg
                class="h-6 w-6"
                stroke="currentColor"
                fill="none"
                viewBox="0 0 24 24"
              >
                <path
                  :class="{
                    hidden: showingNavigationDropdown,
                    'inline-flex': !showingNavigationDropdown,
                  }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  :class="{
                    hidden: !showingNavigationDropdown,
                    'inline-flex': showingNavigationDropdown,
                  }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Responsive Navigation Menu -->
      <div
        :class="{
          block: showingNavigationDropdown,
          hidden: !showingNavigationDropdown,
        }"
        class="space-y-2 md:hidden"
      >
        <div
          v-show="
            $page.props.auth.user
              ? $page.props.auth.user.permissions.admin
              : false
          "
        >
          <BreezeResponsiveNavLink
            :href="route('settings')"
            :active="
              route().current('teachers') ||
              route().current('students') ||
              route().current('settings') ||
              route().current('importXls') ||
              route().current('exportXls')
            "
          >
            Διαχείριση
          </BreezeResponsiveNavLink>
        </div>

        <AdminLayout class="hidden md:block"></AdminLayout>
        <div v-show="$page.props.auth.user">
          <BreezeResponsiveNavLink
            :href="route('apousiologos')"
            :active="route().current('apousiologos')"
          >
            Απουσιολόγος
          </BreezeResponsiveNavLink>
        </div>
        <div
          v-show="
            ($page.props.auth.user
              ? $page.props.auth.user.permissions.admin
              : false) ||
            ($page.props.allowExams &&
              ($page.props.auth.user
                ? $page.props.auth.user.permissions.teacher
                : false))
          "
        >
          <BreezeResponsiveNavLink
            :href="route('exams')"
            :active="route().current('exams')"
          >
            Διαγωνίσματα
          </BreezeResponsiveNavLink>
        </div>
        <div
          v-show="
            ($page.props.auth.user
              ? $page.props.auth.user.permissions.admin
              : false) ||
            ($page.props.activeGradesPeriod &&
              ($page.props.auth.user
                ? $page.props.auth.user.permissions.teacher
                : false))
          "
        >
          <BreezeResponsiveNavLink
            :href="route('grades')"
            :active="route().current('grades')"
          >
            Βαθμολογία
          </BreezeResponsiveNavLink>
        </div>
        <div v-show="!$page.props.auth.user">
          <BreezeResponsiveNavLink :href="route('welcome')">
            Αρχική
          </BreezeResponsiveNavLink>
        </div>
        <div>
          <BreezeResponsiveNavLink
            :href="route('about')"
            :active="route().current('about')"
          >
            Πληροφορίες
          </BreezeResponsiveNavLink>
        </div>

        <!-- Responsive Settings Options -->
        <div
          class="
            pt-2
            pb-2
            border-t border-gray-200
            space-y-3
            flex
            justify-between
            items-center
          "
        >
          <div class="px-4">
            <div class="font-medium text-base text-gray-800">
              {{ $page.props.auth.user ? $page.props.auth.user.name : "" }}
            </div>
            <div class="font-medium text-sm text-gray-500">
              {{ $page.props.auth.user ? $page.props.auth.user.email : "" }}
            </div>
          </div>
          <div v-show="$page.props.auth.user">
            <BreezeResponsiveNavLink
              :href="route('logout')"
              method="post"
              as="button"
            >
              Έξοδος
            </BreezeResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Heading -->
    <header class="bg-white shadow" v-if="$slots.header">
      <div class="max-w-7xl mx-auto py-4 px-4 md:px-6 lg:px-8">
        <slot name="header" />
      </div>
    </header>

    <!-- Page Content -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script>
import BreezeApplicationLogo from "@/Components/ApplicationLogo.vue";
import BreezeDropdown from "@/Components/Dropdown.vue";
import BreezeDropdownLink from "@/Components/DropdownLink.vue";
import BreezeNavLink from "@/Components/NavLink.vue";
import BreezeResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/inertia-vue3";
import AdminLayout from "@/Layouts/AdminMenu.vue";

export default {
  components: {
    BreezeApplicationLogo,
    BreezeDropdown,
    BreezeDropdownLink,
    BreezeNavLink,
    BreezeResponsiveNavLink,
    Link,
    AdminLayout,
  },

  data() {
    return {
      showingNavigationDropdown: false,
    };
  },
};
</script>
