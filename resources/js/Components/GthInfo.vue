<template>
  <div
    v-if="content.value && visible"
    class="
      fixed
      top-4
      left-1/2
      transform
      -translate-x-1/2
      p-4
      text-center
      w-full
      max-w-max
      bg-blue-700
      hover:cursor-pointer
      text-white
      rounded-lg
      flex flex-col
      sm:flex-row sm:justify-center
    "
    @click="hide()"
    @mouseenter="stopTimeOut()"
    @mouseleave="setFinalTimeOut()"
  >
    <div class="font-semibold pr-2">Ενημέρωση:</div>
    <div class="w-full">{{ content.value }} <slot></slot></div>
  </div>
</template>
<script>
import { ref, computed, reactive } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";

export default {
  props: {
    property: String,
    message: String,
  },
  setup(props) {
    const visible = ref(false);
    const content = ref(null);
    const timer = reactive({ first: null, second: null });
    content.value = computed(() => {
      unhide();
      stopTimeOut();
      timer.first = setTimeout(() => {
        hide();
      }, 5000);
      if (props.message) return props.message;
      return props.property
        ? usePage().props.value.flash.message &&
            usePage().props.value.flash.message[props.property]
        : usePage().props.value.flash.message;
    });
    function unhide() {
      visible.value = true;
    }
    function hide() {
      visible.value = false;
    }
    function stopTimeOut() {
      clearTimeout(timer.first);
    }
    function setFinalTimeOut() {
      timer.second = setTimeout(() => {
        hide();
      }, 1000);
    }

    return { content, visible, hide, timer, stopTimeOut, setFinalTimeOut };
  },
};
</script>
