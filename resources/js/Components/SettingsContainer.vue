<template>
  <div class="flex justify-between">
    <p>{{ label }}</p>
    <div class="w-28 sm:w-3/12 flex flex-wrap content-center">
      <div class="w-28 sm:w-full flex flex-col items-center">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
    },
  },
};
</script>
