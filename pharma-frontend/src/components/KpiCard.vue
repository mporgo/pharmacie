<template>
  <div :class="['bg-white rounded-xl shadow p-5 flex items-center gap-4 border-l-4', borderColor]">
    <div :class="['w-12 h-12 rounded-full flex items-center justify-center text-2xl', bgColor]">
      {{ icon }}
    </div>
    <div>
      <p class="text-sm text-gray-500">{{ label }}</p>
      <p :class="['text-xl font-bold', textColor]">{{ value }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon:  String,
  label: String,
  value: [String, Number],
  color: { type: String, default: 'blue' }
})

const colorMap = {
  blue:   { border: 'border-blue-500',   bg: 'bg-blue-50',   text: 'text-blue-700' },
  green:  { border: 'border-green-500',  bg: 'bg-green-50',  text: 'text-green-700' },
  orange: { border: 'border-orange-400', bg: 'bg-orange-50', text: 'text-orange-600' },
  red:    { border: 'border-red-500',    bg: 'bg-red-50',    text: 'text-red-700' },
}

const borderColor = computed(() => colorMap[props.color]?.border)
const bgColor     = computed(() => colorMap[props.color]?.bg)
const textColor   = computed(() => colorMap[props.color]?.text)
</script>