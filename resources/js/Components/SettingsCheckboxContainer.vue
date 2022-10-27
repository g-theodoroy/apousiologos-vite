<template>
  <SettingsContainer :label="label">
    <div class="w-28 sm:w-full flex flex-col items-center">
      <slot></slot>
    </div>
  </SettingsContainer>
</template>

<script>
import SettingsContainer from "@/Components/SettingsContainer.vue";

export default {
  components: {
    SettingsContainer,
  },
  props: {
    label: {
      type: String,
    },
  },
};
</script>
