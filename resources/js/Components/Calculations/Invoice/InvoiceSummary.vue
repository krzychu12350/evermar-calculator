<script setup lang="ts">
import {ref, onMounted} from 'vue';
import {useEventable} from '@/Shared/utilities/eventBus.ts';

const summaryCost = ref({
  totalNettoWithMargin: 0,
  marginAmount: 0,
  totalBruttoWithMargin: 0
});

const taxType = ref<'private' | 'company'>('private');

const {watchEvent} = useEventable();

// Listen for invoice summary updates
onMounted(() => {
  watchEvent('updateInvoiceSummary', (payload: any) => {
    summaryCost.value = payload.summaryCost || summaryCost.value;
    taxType.value = payload.taxType || taxType.value;
  });
});

// Price formatter
const formatPrice = (value: number) => {
  if (!value && value !== 0) return '-';
  return new Intl.NumberFormat('pl-PL', {style: 'currency', currency: 'PLN'}).format(value);
};
</script>

<template>
  <div class="flex flex-col flex-wrap">
    <div class="flex justify-end px-4 py-4  border-t border-gray-200">
      <div class=" max-w-[600px] break-words">
        <h2 class="text-md font-semibold text-gray-900 text-left">Podsumowanie</h2>
      </div>

    </div>
    <div v-if="summaryCost.totalNettoWithMargin !== 0" class="flex justify-end">
      <table class="w-78">
        <tbody class="bg-white">
        <tr class="border-t border-gray-200">
          <td class="px-4 py-2 text-sm  text-gray-700 font-bold">Netto (z marżą)</td>
          <td class="px-4 py-2 text-right text-gray-900 font-bold">{{
              formatPrice(summaryCost.totalNettoWithMargin)
            }}
          </td>
        </tr>
        <tr class="border-t border-gray-200">
          <td class="px-4 py-2 text-sm  font-bold text-gray-700">Zarobek handlowca</td>
          <td class="px-4 py-2 text-right font-semibold text-gray-900">{{ formatPrice(summaryCost.marginAmount) }}</td>
        </tr>
        <tr class="border-t border-gray-200">
          <td class="px-4 py-2 text-sm font-bold text-gray-700">Brutto ({{ taxType === 'private' ? '8%' : '23%' }}
            VAT)
          </td>
          <td class="px-4 py-2 text-right font-semibold text-gray-900">{{
              formatPrice(summaryCost.totalBruttoWithMargin)
            }}
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
