<script setup lang="ts">
import { computed, ref, watch, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle2, AlertTriangle } from 'lucide-vue-next';

const page = usePage();
const flash = computed(() => page.props.flash || {});
const success = computed(() => flash.value?.success || flash.value?.status);
const error = computed(() => flash.value?.error);

const props = withDefaults(defineProps<{ duration?: number }>(), {
  duration: 3000, // default to 3 seconds
});

const successMessage = ref<string | null>(null);
const errorMessage = ref<string | null>(null);

let successTimeout: ReturnType<typeof setTimeout> | null = null;
let errorTimeout: ReturnType<typeof setTimeout> | null = null;

const clearSuccessTimeout = () => {
  if (successTimeout) {
    clearTimeout(successTimeout);
    successTimeout = null;
  }
};

const clearErrorTimeout = () => {
  if (errorTimeout) {
    clearTimeout(errorTimeout);
    errorTimeout = null;
  }
};

watch(
  success,
  (val) => {
    clearSuccessTimeout();
    if (val) {
      successMessage.value = String(val);
      successTimeout = setTimeout(() => {
        successMessage.value = null;
      }, props.duration);
    } else {
      successMessage.value = null;
    }
  },
  { immediate: true }
);

watch(
  error,
  (val) => {
    clearErrorTimeout();
    if (val) {
      errorMessage.value = String(val);
      errorTimeout = setTimeout(() => {
        errorMessage.value = null;
      }, props.duration);
    } else {
      errorMessage.value = null;
    }
  },
  { immediate: true }
);

onUnmounted(() => {
  clearSuccessTimeout();
  clearErrorTimeout();
});
</script>

<template>
  <div class="mx-auto w-full md:max-w-7xl px-4 mt-4 space-y-3">
    <Alert v-if="successMessage" variant="default" class="border-green-600 bg-green-50 text-green-700">
      <CheckCircle2 class="size-4" />
      <AlertTitle class="font-medium">Berhasil</AlertTitle>
      <AlertDescription>
        {{ successMessage }}
      </AlertDescription>
    </Alert>

    <Alert v-if="errorMessage" variant="destructive">
      <AlertTriangle class="size-4" />
      <AlertTitle class="font-medium">Terjadi Kesalahan</AlertTitle>
      <AlertDescription>
        {{ errorMessage }}
      </AlertDescription>
    </Alert>
  </div>
</template>