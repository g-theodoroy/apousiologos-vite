<template>
  <div class="flex flex-col space-y-4 border-2 rounded-lg p-2">
    <div
      class="
        text-lg
        font-semibold
        self-center
        bg-gray-100
        w-full
        text-center
        py-2
      "
    >
      {{ title }}
    </div>
    <div class="px-2 pb-4">
      <div class="flex flex-col space-y-2 sm:space-y-4 max-w-5xl mx-auto">
        <slot />
      </div>
    </div>

    <button
      v-show="button"
      type="submit"
      class="
        w-max
        bg-gray-100
        hover:bg-gray-300
        active:bg-gray-500
        text-gray-700
        hover:text-gray-900
        active:text-gray-100
        text-sm
        font-semibold
        py-1
        px-4
        border border-gray-300
        hover:border-transparent
        rounded-md
        self-center
      "
      @click="this.$emit('submit')"
    >
      Αποθήκευση
    </button>
  </div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
    },
    button: {
      type: Boolean,
      default: false,
    },
  },
};
</script>
