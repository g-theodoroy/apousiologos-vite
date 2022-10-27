<template>
  <nav class="bg-white">
    <!-- Primary Navigation Menu -->
    <div class="max-w-full">
      <div class="flex justify-between">
        <div class="flex">
          <!-- Navigation Links -->
          <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
            <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
              <BreezeNavLink
                :href="route('settings')"
                :active="route().current('settings')"
              >
                Ρυθμίσεις
              </BreezeNavLink>
            </div>

            <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
              <BreezeNavLink
                :href="route('importXls')"
                :active="route().current('importXls')"
              >
                Εισαγωγή_xls
              </BreezeNavLink>
            </div>
            <BreezeNavLink
              :href="route('exportXls')"
              :active="route().current('exportXls')"
            >
              Εξαγωγή_xls
            </BreezeNavLink>
          </div>

          <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
            <BreezeNavLink
              :href="route('teachers')"
              :active="route().current('teachers')"
            >
              Καθηγητές
            </BreezeNavLink>
          </div>

          <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
            <BreezeNavLink
              :href="route('students')"
              :active="route().current('students')"
            >
              Μαθητές
            </BreezeNavLink>
          </div>

          <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
            <a
              :href="route('logs')"
              target="_blank"
              class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
            >
              Logs
            </a>
          </div>



        </div>
      </div>
    </div>

    <!-- Hamburger -->
    <div
      v-show="$page.props.auth.user && $page.props.auth.user.permissions.admin"
      class="relative"
    >
      <div class="-mr-2 absolute inset-y-0 right-0 items-center md:hidden">
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

    <!-- Responsive Navigation Menu -->
    <div
      :class="{
        block: showingNavigationDropdown,
        hidden: !showingNavigationDropdown,
      }"
      class="space-y-2 pt-14 md:hidden"
    >
      <div>
        <BreezeResponsiveNavLink
          :href="route('settings')"
          :active="route().current('settings')"
        >
          Ρυθμίσεις
        </BreezeResponsiveNavLink>
      </div>
      <div>
        <BreezeResponsiveNavLink
          :href="route('importXls')"
          :active="route().current('importXls')"
        >
          Εισαγωγή&nbsp;xls
        </BreezeResponsiveNavLink>
      </div>
      <div>
        <BreezeResponsiveNavLink
          :href="route('exportXls')"
          :active="route().current('exportXls')"
        >
          Εξαγωγή&nbsp;xls
        </BreezeResponsiveNavLink>
      </div>
      <div>
        <BreezeResponsiveNavLink
          :href="route('teachers')"
          :active="route().current('teachers')"
        >
          Καθηγητές
        </BreezeResponsiveNavLink>
      </div>
      <div
        v-show="
          $page.props.auth.user
            ? $page.props.auth.user.permissions.teacherOrAdmin
            : false
        "
      >
        <BreezeResponsiveNavLink
          :href="route('students')"
          :active="route().current('students')"
        >
          Μαθητές
        </BreezeResponsiveNavLink>
      </div>
    </div>
  </nav>
</template>

<script>
import BreezeNavLink from "@/Components/NavLink.vue";
import BreezeResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/inertia-vue3";

export default {
  components: {
    BreezeNavLink,
    BreezeResponsiveNavLink,
    Link,
  },

  data() {
    return {
      showingNavigationDropdown: false,
    };
  },
};
</script>
