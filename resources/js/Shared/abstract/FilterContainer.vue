<template>
  <div class="bg-white">
    <!--    <div class="bg-white rounded-lg shadow">-->
    <div>
      <!-- Mobile filter dialog -->
      <TransitionRoot as="template" :show="mobileFiltersOpen">
        <Dialog class="relative z-40 lg:hidden" @close="mobileFiltersOpen = false">
          <TransitionChild
              as="template"
              enter="transition-opacity ease-linear duration-300"
              enter-from="opacity-0"
              enter-to="opacity-100"
              leave="transition-opacity ease-linear duration-300"
              leave-from="opacity-100"
              leave-to="opacity-0"
          >
            <div class="fixed inset-0 bg-black/25"/>
          </TransitionChild>

          <div class="fixed inset-0 z-40 flex">
            <TransitionChild
                as="template"
                enter="transition ease-in-out duration-300 transform"
                enter-from="translate-x-full"
                enter-to="translate-x-0"
                leave="transition ease-in-out duration-300 transform"
                leave-from="translate-x-0"
                leave-to="translate-x-full"
            >
              <DialogPanel
                  class="relative ml-auto flex size-full max-w-xs flex-col overflow-y-auto bg-white py-4 pb-12 shadow-xl"
              >
                <div class="flex items-center justify-between px-4">
                  <h2 class="text-lg font-medium text-gray-900">Filtry</h2>
                  <button
                      type="button"
                      class="-mr-2 flex size-10 items-center justify-center rounded-md bg-white p-2 text-gray-400"
                      @click="mobileFiltersOpen = false"
                  >
                    <span class="sr-only">Close menu</span>
                    <XMarkIcon class="size-6" aria-hidden="true"/>
                  </button>
                </div>

                <!-- Filters -->
                <form class="mt-4 border-t border-gray-200">
                  <!-- <h3 class="sr-only">Categories</h3>
                  <ul role="list" class="px-2 py-3 font-medium text-gray-900">
                    <li v-for="category in subCategories" :key="category.name">
                      <a :href="category.href" class="block px-2 py-3">{{
                        category.name
                      }}</a>
                    </li>
                  </ul> -->

                  <Disclosure
                      as="div"
                      v-for="section in filters"
                      :key="section.id"
                      class="border-t border-gray-200 px-4 py-6"
                      v-slot="{ open }"
                  >
                    <h3 class="-mx-2 -my-3 flow-root">
                      <DisclosureButton
                          class="flex w-full items-center justify-between bg-white px-2 py-3 text-gray-400 hover:text-gray-500"
                      >
                        <span class="font-medium text-gray-900">{{ section.name }}</span>
                        <span class="ml-6 flex items-center">
                          <PlusIcon v-if="!open" class="size-5" aria-hidden="true"/>
                          <MinusIcon v-else class="size-5" aria-hidden="true"/>
                        </span>
                      </DisclosureButton>
                    </h3>
                    <DisclosurePanel class="pt-6">
                      <div v-if="section.type === 'checkbox'" class="space-y-6">
                        <div
                            v-for="(option, optionIdx) in section.options"
                            :key="option.value"
                            class="flex gap-3"
                        >
                          <div class="flex h-5 shrink-0 items-center">
                            <input
                                type="checkbox"
                                :id="`filter-${section.id}-${optionIdx}`"
                                :value="option.value"
                                v-model="option.checked"
                                @change="setActiveFilters"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                          </div>
                          <label
                              :for="`filter-${section.id}-${optionIdx}`"
                              class="min-w-0 flex-1 text-gray-500"
                          >
                            {{ option.label }}
                          </label>
                        </div>
                      </div>
                      <div v-else-if="section.type === 'radio'" class="space-y-6">
                        <div
                            v-for="(option, optionIdx) in section.options"
                            :key="option.value"
                            class="flex gap-3"
                        >
                          <div class="flex h-5 shrink-0 items-center">
                            <input
                                type="radio"
                                :id="`filter-${section.id}-${optionIdx}`"
                                :name="section.id"
                                :value="option.value"
                                v-model="section.selected"
                                @change="setActiveFilters"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                          </div>
                          <label
                              :for="`filter-${section.id}-${optionIdx}`"
                              class="min-w-0 flex-1 text-gray-500"
                          >
                            {{ option.label }}
                          </label>
                        </div>
                      </div>
                      <div v-else-if="section.type === 'date-range'" class="space-y-6">
                        <vue-datepicker
                            v-model="section.options.value"
                            @update:modelValue="setActiveFilters"
                            :placeholder="section.options.label"
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            range
                        />
                      </div>

                      <div v-if="section.type === 'multi-select'" class="space-y-6">
                        <Multiselect
                            v-model="section.selected"
                            :options="section.options"
                            @update:modelValue="setActiveFilters"
                            :multiple="false"
                            :close-on-select="false"
                            :clear-on-select="false"
                            :preserve-search="true"
                            placeholder="Wybierz..."
                            label="label"
                            track-by="value"
                            :preselect-first="false"
                        />
                      </div>

                      <div v-else-if="section.type === 'range'" class="space-y-6">
                        <RangeInput
                            v-model:value="section.value"

                            :min="section.min"
                            :max="section.max"
                            :unit="section.unit"
                            :id="section.id"
                            :name="section.id"
                            @update:value="setActiveFilters"
                        />
                      </div>


                    </DisclosurePanel>
                  </Disclosure>
                  <div class="flex justify-end p-4">
                    <!--                    <CustomButton @click="closeMobileFilterPanel" label="Zastosuj" />-->
                    <slot name="actions"/>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </Dialog>
      </TransitionRoot>

      <div class="w-full px-4 sm:px-6 lg:px-8">
        <div
            class="flex items-baseline justify-between border-b border-gray-200 pb-6 pt-12"
        >
          <h1 class="text-2xl lg:text-4xl font-bold tracking-tight text-gray-900">
            {{ title }}
          </h1>

          <div class="flex items-center">
            <Menu as="div" class="relative inline-block text-left">
              <!-- <div>
                <MenuButton
                  class="group inline-flex justify-center text-sm font-medium text-gray-700 hover:text-gray-900"
                >
                  Sort
                  <ChevronDownIcon
                    class="-mr-1 ml-1 size-5 shrink-0 text-gray-400 group-hover:text-gray-500"
                    aria-hidden="true"
                  />
                </MenuButton>
              </div> -->

              <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
              >
                <MenuItems
                    class="absolute right-0 z-10 mt-2 w-40 origin-top-right rounded-md bg-white shadow-2xl ring-1 ring-black/5 focus:outline-none"
                >
                  <div class="py-1">
                    <MenuItem
                        v-for="option in sortOptions"
                        :key="option.name"
                        v-slot="{ active }"
                    >
                      <a
                          :href="option.href"
                          :class="[
                          option.current ? 'font-medium text-gray-900' : 'text-gray-500',
                          active ? 'bg-gray-100 outline-none' : '',
                          'block px-4 py-2 text-sm',
                        ]"
                      >{{ option.name }}</a
                      >
                    </MenuItem>
                  </div>
                </MenuItems>
              </transition>
            </Menu>

            <button
                type="button"
                class="-m-2 ml-5 p-2 text-gray-400 hover:text-gray-500 sm:ml-7"
            >
              <span class="sr-only">View grid</span>
              <div v-if="is_mode_toogling" class="p-0">
                <slot name="view_mode"></slot>
              </div>
            </button>
            <button
                type="button"
                class="-m-2 ml-4 p-2 text-gray-400 hover:text-gray-500 sm:ml-6 lg:hidden"
                @click="mobileFiltersOpen = true"
            >
              <span class="sr-only">Filters</span>
              <FunnelIcon class="size-6" aria-hidden="true"/>
            </button>
          </div>
        </div>

        <section aria-labelledby="products-heading" class="pb-24 pt-6">
          <h2 id="products-heading" class="sr-only">Products</h2>

          <div class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-4">
            <!-- Filters -->
            <form class="hidden lg:block">
              <!-- <h3 class="sr-only">Categories</h3>
              <ul
                role="list"
                class="space-y-4 border-b border-gray-200 pb-6 text-sm font-medium text-gray-900"
              >
                <li v-for="category in subCategories" :key="category.name">
                  <a :href="category.href">{{ category.name }}</a>
                </li>
              </ul> -->
              <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Filtry</h2>
                <p class="text-sm text-gray-600 mt-2">Wybierz interesujące cię opcje</p>
              </div>
              <Disclosure
                  as="div"
                  v-for="section in filters"
                  :key="section.id"
                  class="border-b border-gray-200 py-6"
                  v-slot="{ open }"
              >
                <h3 class="-my-3 flow-root">
                  <DisclosureButton
                      class="flex w-full items-center justify-between bg-white py-3 text-sm text-gray-400 hover:text-gray-500"
                  >
                    <span class="font-medium text-gray-900">{{ section.name }}</span>
                    <span class="ml-6 flex items-center">
                      <PlusIcon v-if="!open" class="size-5" aria-hidden="true"/>
                      <MinusIcon v-else class="size-5" aria-hidden="true"/>
                    </span>
                  </DisclosureButton>
                </h3>
                <DisclosurePanel class="pt-6">
                  <div class="space-y-4">
                    <!-- Checkbox Filters -->
                    <div
                        v-if="section.type === 'checkbox'"
                        v-for="(option, optionIdx) in section.options"
                        :key="option.value"
                        class="flex gap-3"
                    >
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input
                              type="checkbox"
                              :id="`filter-${section.id}-${optionIdx}`"
                              :value="option.value"
                              v-model="option.checked"
                              @change="setActiveFilters"
                              class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto"
                          />
                          <svg
                              class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25"
                              viewBox="0 0 14 14"
                              fill="none"
                          >
                            <path
                                class="opacity-0 group-has-[:checked]:opacity-100"
                                d="M3 8L6 11L11 3.5"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                class="opacity-0 group-has-[:indeterminate]:opacity-100"
                                d="M3 7H11"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                          </svg>
                        </div>
                      </div>
                      <label
                          :for="`filter-${section.id}-${optionIdx}`"
                          class="text-sm text-gray-600"
                      >
                        {{ option.label }}
                      </label>
                    </div>

                    <!-- Radio Filters -->
                    <div
                        v-if="section.type === 'radio'"
                        v-for="(option, optionIdx) in section.options"
                        :key="option.value"
                        class="flex items-center gap-3"
                    >
                      <input
                          type="radio"
                          :id="`filter-${section.id}-${optionIdx}`"
                          :name="`filter-${section.id}`"
                          :value="option.value"
                          v-model="section.selected"
                          @change="setActiveFilters"
                          class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <label
                          :for="`filter-${section.id}-${optionIdx}`"
                          class="text-sm text-gray-600"
                      >
                        {{ option.label }}
                      </label>
                    </div>

                    <!-- Date-Range Filters -->
                    <div v-if="section.type === 'date-range'" class="space-y-2">
                      <label class="text-sm text-gray-600 sr-only">{{
                          section.name
                        }}</label>

                      <vue-datepicker
                          v-model="section.options.value"
                          @update:modelValue="setActiveFilters"
                          :placeholder="section.options.label"
                          class="w-full rounded-md text-sm p-2"
                          range
                      />
                    </div>

                    <div v-if="section.type === 'multi-select'" class="space-y-6">
                      <Multiselect
                          v-model="section.selected"
                          :options="section.options"
                          @update:modelValue="setActiveFilters"
                          :multiple="false"
                          :close-on-select="false"
                          :clear-on-select="false"
                          :preserve-search="true"
                          placeholder="Wybierz..."
                          label="label"
                          track-by="value"
                          :preselect-first="false"
                      />
                    </div>

                    <div v-else-if="section.type === 'range'" class="space-y-6">
                      <RangeInput
                          v-model:value="section.value"

                          :min="section.min"
                          :max="section.max"
                          :unit="section.unit"
                          :id="section.id"
                          :name="section.id"
                          @update:value="setActiveFilters"
                      />
                    </div>


                  </div>
                </DisclosurePanel>
              </Disclosure>
              <!--              <div class="py-4 flex justify-end gap-2">-->
              <!--                &lt;!&ndash; Clear all filters button &ndash;&gt;-->
              <!--                <CustomButton-->
              <!--                    @click="clearAllFilters"-->
              <!--                    label="Wyczyść"-->
              <!--                    variant="outline"-->
              <!--                />-->

              <!--                &lt;!&ndash; Apply filters button &ndash;&gt;-->
              <!--                <CustomButton-->
              <!--                    @click="closeMobileFilterPanel"-->
              <!--                    label="Zastosuj"-->
              <!--                />-->
              <!--              </div>-->
              <slot name="actions"/>
            </form>

            <!-- Product grid -->
            <div class="lg:col-span-3">
              <!-- Your content -->
              <slot name="default"></slot>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, defineProps, onMounted, toRaw} from "vue";
import {
  Dialog,
  DialogPanel,
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {XMarkIcon} from "@heroicons/vue/24/outline";
import {
  ChevronDownIcon,
  FunnelIcon,
  MinusIcon,
  PlusIcon,
  Squares2X2Icon,
  TableCellsIcon,
  CalendarIcon,
} from "@heroicons/vue/20/solid";
import CustomButton from "../forms/buttons/CustomButton.vue";
import VueDatepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";
import RangeInput from "@/Shared/forms/inputs/RangeInput.vue";
import {useEventable} from "@/Shared/utilities/eventBus";

const {watchEvent} = useEventable();

onMounted(() => {
  filters.value = props.filters;
  watchEvent('clear-all-filters', clearAllFilters);
  watchEvent('close-mobile-filter-panel', closeMobileFilterPanel);
});

let filters = ref<any[]>();
let activeFilters = ref<any[]>();

const sortOptions = [
  {name: "Most Popular", href: "#", current: true},
  {name: "Best Rating", href: "#", current: false},
  {name: "Newest", href: "#", current: false},
  {name: "Price: Low to High", href: "#", current: false},
  {name: "Price: High to Low", href: "#", current: false},
];
const subCategories = [
  {name: "Totes", href: "#"},
  {name: "Backpacks", href: "#"},
  {name: "Travel Bags", href: "#"},
  {name: "Hip Bags", href: "#"},
  {name: "Laptop Sleeves", href: "#"},
];

const mobileFiltersOpen = ref(false);

function selectedItems(values: any) {
  console.log(values);
}

const props = withDefaults(
    defineProps<{
      title: string;
      is_mode_toogling: boolean;
      filters: Array<any>;
      // kategorie filtrów z różnymi wartościami/ bądź polami do wpisania
    }>(),
    {
      is_mode_toogling: false,
    }
);

const emit = defineEmits(["apply-filters"]);

const selectedRadio = ref({}); // Tracks selected radio button values

function rawSelected(section: any) {
  return toRaw(section.selected);
}

const setActiveFilters = () => {
  console.log(filters.value);
  activeFilters.value = filters.value
      .map((filter: any) => ({
        id: filter.id,
        selected: filter.type === "radio" ? filter.selected : null,
        options: filter.type === "checkbox" ? filter.options.filter((o) => o.checked) : [],
        dateRange: filter.type === "date-range" ? filter.options.value : null,
        multiSelected: filter.type === "multi-select" ? filter.selected : [],
        range: filter.type === "range" ? filter.value : null, // ← Add this
      }))
      .filter(
          (filter) =>
              (filter.selected !== null && filter.selected !== "") ||
              (filter.options && filter.options.length > 0) ||
              filter.dateRange ||
              filter.multiSelected ||
              filter.range !== null
      );

  console.log("Active Filters:", activeFilters.value);
};

function clearAllFilters() {
  filters.value.forEach((section: any) => {
    if (section.type === "checkbox") {
      section.options.forEach((opt: any) => (opt.checked = false));
    } else if (section.type === "radio") {
      section.selected = null;
    } else if (section.type === "date-range") {
      section.options.value = null;
    } else if (section.type === "multi-select") {
      section.selected = [];
    } else if (section.type === "range") {
      section.value = {from: section.min, to: section.max};
    }
  });

  setActiveFilters();
}

function closeMobileFilterPanel() {
  emit("apply-filters", activeFilters.value);
  mobileFiltersOpen.value = false;
}
</script>
