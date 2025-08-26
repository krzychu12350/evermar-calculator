<template>
  <!-- Wrapper for horizontal scroll on small screens -->
  <div class="overflow-x-auto">
<!--    <pre>-->
<!--      {{props}}-->
<!--    </pre>-->
  <!-- Hidden invoice layout for PDF generation -->
  <div id="invoice-pdf" class="bg-white text-gray-800 p-8 w-[800px] mx-auto">
    <header class="flex justify-between items-center border-b pb-4 mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Oferta</h1>
        <p class="text-sm text-gray-500">Wygenerowano automatycznie</p>
      </div>
      <div>
        <InvoiceLogo />
      </div>
    </header>

    <!-- Invoice meta info -->
    <section class="mb-6">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <h2 class="font-semibold text-gray-700">Sprzedawca</h2>
          <p>Evermar Sp. z o.o.</p>
          <p>aleja Armii Krajowej 4A/113</p>
          <p>35-307 Rzeszów</p>
          <p>NIP: 5170417233</p>
        </div>
<!--        <div>-->
<!--          <h2 class="font-semibold text-gray-700">Klient</h2>-->
<!--          <p>{{ invoiceData.client?.name || "Imię i nazwisko" }}</p>-->
<!--          <p>{{ invoiceData.client?.address || "Adres klienta" }}</p>-->
<!--          <p>{{ invoiceData.client?.nip || "NIP klienta" }}</p>-->
<!--        </div>-->
      </div>
    </section>

    <!-- Table -->
    <section class="mb-6">
      <h2 class="text-lg font-semibold text-gray-700 mb-2">{{ invoiceData.title || "Pozycje faktury" }}</h2>
      <p class="text-sm text-gray-500 mb-4">{{ invoiceData.description || "Szczegóły pozycji instalacji" }}</p>

      <table class="min-w-full border-2 border-gray-700 text-sm">
        <thead class="bg-gray-100">
        <tr>
          <th class="border-2 border-gray-700 text-left px-4 py-2">Nazwa</th>
          <th class="border-2 border-gray-700 text-center px-4 py-2">Ilość</th>
          <th class="border-2 border-gray-700 text-right px-4 py-2">Cena jedn. (netto)</th>
          <th class="border-2 border-gray-700 text-right px-4 py-2">Wartość netto</th>
          <th class="border-2 border-gray-700 text-center px-4 py-2">VAT</th>
          <th class="border-2 border-gray-700 text-right px-4 py-2">Cena jedn. (brutto)</th>
          <th class="border-2 border-gray-700 text-right px-4 py-2">Wartość brutto</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(item, idx) in invoiceData.data" :key="idx" class="border-2 border-gray-700">
          <td class="border-2 border-gray-700 px-4 py-2">{{ item.name }}</td>
          <td class="border-2 border-gray-700 px-4 py-2 text-center">{{ item.quantity }}</td>
          <td class="border-2 border-gray-700 px-4 py-2 text-right">{{ formatPrice(item.unit_price) }}</td>
          <td class="border-2 border-gray-700 px-4 py-2 text-right">{{ formatPrice(item.total) }}</td>
          <td class="border-2 border-gray-700 px-4 py-2 text-center">{{ item.vat }}</td>
          <td class="border-2 border-gray-700 px-4 py-2 text-right">{{ formatPrice(item.price_with_vat) }}</td>
          <td class="border-2 border-gray-700 px-4 py-2 text-right">{{ formatPrice(item.total_with_vat) }}</td>
        </tr>
        </tbody>
      </table>
    </section>

    <!-- Summary & Sales Rep Section -->
    <section class="mt-8 flex justify-between">
      <div class="w-1/2 p-4 rounded">
        <h3 class="font-semibold text-gray-700 mb-2">Przedstawiciel sprzedaży</h3>
        <p class="text-gray-800">{{ salesRepData.name || "Imię i nazwisko" }}</p>
        <p class="text-gray-800">{{ salesRepData.phone || "+48 000 000 000" }}</p>
        <p class="text-gray-800">{{ salesRepData.email || "email@example.com" }}</p>
      </div>

      <div class="w-1/2 p-4 rounded">
        <h3 class="font-semibold text-gray-700 mb-2">Podsumowanie</h3>
        <table class="w-full text-sm">
          <tbody>
          <tr>
            <td class="font-medium text-gray-600">Suma netto</td>
            <td class="text-right">{{ formatPrice(invoiceData.summary?.totalNettoWithMargin) }}</td>
          </tr>
          <tr class="font-bold">
            <td class="text-gray-800">Wartość inwestycji brutto</td>
            <td class="text-right text-gray-600">{{ formatPrice(invoiceData.summary?.totalBruttoWithMargin) }}</td>
          </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Footer -->
    <footer class="mt-12 text-xs text-gray-500 border-t pt-4">
      <div class="grid grid-cols-3 gap-4 text-center">
        <div>
          <p class="font-semibold">EVERMAR SP. Z O.O.</p>
          <p>aleja Armii Krajowej 4A/113</p>
          <p>35-307 Rzeszów</p>
        </div>
        <div>
          <p>NIP: 5170417233</p>
          <p>KRS: 0000907270</p>
          <p>REGON: 389227386</p>
        </div>
        <div>
          <p>Tel: +48 790-780-703</p>
          <p>E-mail: kontakt@evermar.net</p>
          <p>www.evermar.net</p>
        </div>
      </div>
    </footer>
  </div>
  </div>
</template>

<script setup lang="ts">
import {defineProps, onMounted} from "vue";
import InvoiceLogo from "@/Components/Calculations/Invoice/InvoiceLogo.vue";
import html2canvas from "html2canvas-pro";
import {jsPDF} from "jspdf";
import {useEventable} from "@/Shared/utilities/eventBus";
import { router } from '@inertiajs/vue3';
import {route} from "ziggy-js";

const { emit, watchEvent, bus } = useEventable();

const props = defineProps<{
  invoiceData: any,
  salesRepData: {
    name: string,
    phone: string,
    email: string
  }
}>();

function formatPrice(value: any) {
  if (value == null) return "-";
  return new Intl.NumberFormat("pl-PL", { style: "currency", currency: "PLN" }).format(Number(value));
}


onMounted(() => {
  // Catch invoice data
  // watchEvent("updateInvoice", (payload: any) => {
  //   invoiceData.value = payload;
  // });
  //
  // // Catch summary updates
  // watchEvent("updateInvoiceSummary", (payload: any) => {
  //   if (!invoiceData.value) invoiceData.value = {};
  //   invoiceData.value.summary = payload.summaryCost;
  // });

  // Download trigger


  watchEvent("download-invoice", async () => {
    const element = document.getElementById("invoice-pdf");
    if (!element) return;

 //   emit("show-logo-loader");

    try {
      const canvas = await html2canvas(element, {
        scale: 2,
        backgroundColor: "#ffffff",
        useCORS: true,
        allowTaint: true,
      });

      const imgData = canvas.toDataURL("image/png");
      const pdf = new jsPDF({ unit: "px", format: "a4", orientation: "portrait" });
      const pageWidth = pdf.internal.pageSize.getWidth();
      const pageHeight = pdf.internal.pageSize.getHeight();
      const imgProps = pdf.getImageProperties(imgData);
      const pdfHeight = (imgProps.height * pageWidth) / imgProps.width;

      let position = 3;
      let remainingHeight = pdfHeight;
      while (remainingHeight > 0) {
        pdf.addImage(imgData, "PNG", 0, position, pageWidth, pdfHeight);
        remainingHeight -= pageHeight;
        if (remainingHeight > 0) pdf.addPage();
        position = -pageHeight + remainingHeight + 3;
      }

      pdf.save("oferta.pdf");
    } finally {
      emit("download-invoice-finish");
     // emit("hide-logo-loader");
        // setTimeout(()=> {
        //   router.get(route('home'));
        // },1000);
    }
  });

});
</script>

<style scoped>
#invoice-pdf * {
  color: black !important;
  background-color: white !important;
}
</style>
