<template>
  <div v-if="links.length > 3">
    <div class="flex flex-wrap justify-center -mb-1">
      <template v-for="(link, p) in links" :key="p">
        <div
          v-if="link.url === null"
          class="
            mr-1
            mb-1
            px-4
            py-3
            text-sm
            leading-4
            text-gray-400
            border
            rounded
          "
          v-html="link.label"
        />
        <Link
          v-else
          class="
            mr-1
            mb-1
            px-4
            py-3
            text-sm
            leading-4
            border
            rounded
            hover:bg-gray-100 hover:text-gray-500
            focus:border-gray-500 focus:text-gray-700
          "
          :class="{ 'bg-gray-700 text-white': link.active }"
          :href="link.url + queryStr"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>

<script>
import { Link } from "@inertiajs/inertia-vue3";

export default {
  components: {
    Link,
  },
  props: {
    links: Array,
    queryStr: String,
  },
};
</script>