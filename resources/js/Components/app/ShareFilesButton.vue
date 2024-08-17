<template>
    <div>
        <button
            @click="showDialog"
            class=" h-full inline-flex items-center px-4 py-2 text-lg font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-6"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"
                />
            </svg>
        </button>

        <ShareModal v-model="show" :all="allSelected" :ids="selectedIds"/>
    </div>
</template>

<script setup>
//Imports
import ShareModal from "@/Components/app/ShareModal.vue";
import { ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { onErrorDialog, onSuccessNotification } from "@/event-bus.js";

//Uses
const restoreForm = useForm({
    all: null,
    ids: [],
});

//Refs
const show = ref(false);

//Props & Emit
const props = defineProps({
    allSelected: {
        type: Boolean,
        required: false,
        default: false,
    },
    selectedIds: {
        type: Array,
        required: false,
    },
});
const emit = defineEmits(["restore"]);

//Methods
function showDialog() {
    // console.log(props.allSelected, props.selectedIds);
    if (!props.allSelected && !props.selectedIds.length) {
        onErrorDialog("Please select files to share.");
        return;
    }
    // console.log(props.allSelected, props.selectedIds);
    show.value = true;
}
</script>
