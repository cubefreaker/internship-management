<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem } from '@/components/ui/dropdown-menu'
import { ChevronDown } from 'lucide-vue-next'

interface OptionItem {
  [key: string]: any
}

interface Props {
  modelValue: string
  options: OptionItem[]
  labelKey?: string
  valueField?: string
  placeholder?: string
  disabled?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  labelKey: 'label',
  valueField: 'id',
  placeholder: 'Pilih...'
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const query = ref('')

// Clear query when options change or when modelValue changes to reduce stale filters
watch(() => props.options, () => { query.value = '' })
watch(() => props.modelValue, () => { query.value = '' })

const selectedLabel = computed(() => {
  const found = props.options.find((o) => String(o[props.valueField]) === props.modelValue)
  return found ? String(found[props.labelKey]) : ''
})

const filteredOptions = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return props.options
  return props.options.filter((o) => String(o[props.labelKey] || '').toLowerCase().includes(q))
})

const onSelect = (opt: OptionItem) => {
  emit('update:modelValue', String(opt[props.valueField]))
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button :disabled="props.disabled" variant="outline" class="w-full justify-between" :class="props.class">
        <span>{{ selectedLabel || props.placeholder }}</span>
        <ChevronDown class="w-4 h-4 opacity-60" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent class="w-64 p-2">
      <input
        v-model="query"
        placeholder="Cari..."
        autofocus
        class="flex h-9 w-full min-w-0 rounded-md border border-input bg-background px-3 py-1 text-sm mb-2 focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] outline-none"
      />
      <div class="max-h-48 overflow-auto">
        <DropdownMenuItem v-for="opt in filteredOptions" :key="String(opt[props.valueField])" @click="onSelect(opt)">
          {{ opt[props.labelKey] }}
        </DropdownMenuItem>
      </div>
    </DropdownMenuContent>
  </DropdownMenu>
</template>