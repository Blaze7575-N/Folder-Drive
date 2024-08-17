<template>
    <div>
        <button
            @click="showDialog"
            class="inline-flex items-center px-4 py-2 mr-6 text-lg font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="size-5 mr-2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 0 0-3.7-3.7 48.678 48.678 0 0 0-7.324 0 4.006 4.006 0 0 0-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 0 0 3.7 3.7 48.656 48.656 0 0 0 7.324 0 4.006 4.006 0 0 0 3.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3-3 3"
                />
            </svg>

            Restore
        </button>
        <ConfirmationDialog
            :show="show"
            message="Are you sure you want to restrore these files?"
            @confirm="handleRestore"
            @cancel="handleClose"
        />
    </div>
</template>

<script setup>
//Imports
import ConfirmationDialog from "@/Components/app/ConfirmationDialog.vue";
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
        onErrorDialog("Please select files to restore.");
        return;
    }
    show.value = true;
}

function handleClose() {
    show.value = false;
}

function handleRestore() {
    show.value = false;

    props.allSelected
        ? (restoreForm.all = true)
        : (restoreForm.ids = props.selectedIds);

    restoreForm.post(route("file.restore"), {
        preserveScroll: true,
        onSuccess: () => {
            show.value = false;
            emit("restore");
            onSuccessNotification("The Files have been restored.");
        },
    });
}
</script>
