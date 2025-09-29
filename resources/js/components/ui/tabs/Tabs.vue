<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { TabsRoot, type TabsRootProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends TabsRootProps {
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  orientation: 'horizontal'
})
</script>

<template>
  <TabsRoot
    :default-value="defaultValue"
    :model-value="modelValue"
    :orientation="orientation"
    :activation-mode="activationMode"
    :dir="dir"
    :as="as"
    :as-child="asChild"
    :class="cn('w-full', props.class)"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <slot />
  </TabsRoot>
</template>