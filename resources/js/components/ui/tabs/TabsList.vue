<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { TabsList, type TabsListProps } from 'reka-ui'
import { cn } from '@/lib/utils'

interface Props extends TabsListProps {
  class?: HTMLAttributes['class']
}

const props = defineProps<Props>()
</script>

<template>
  <TabsList
    :loop="loop"
    :as="as"
    :as-child="asChild"
    :class="cn(
      'inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground',
      props.class
    )"
  >
    <slot />
  </TabsList>
</template>