<template>
    <PrimaryButton
        @click="showDialog"
        class="px-3 py-0 normal-case text-[16px]"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="w-5 h-5 mr-2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"
            />
        </svg>
        <span>Delete Forever</span>
    </PrimaryButton>
    <ConfirmationDialog
        :show="show"
        message="Are you sure you want to delete these files?"
        @confirm="handleDelete"
        @cancel="handleClose"
    />
</template>

<script setup>
//Imports
import {ref} from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import ConfirmationDialog from "@/Components/app/ConfirmationDialog.vue";
import { usePage, useForm } from "@inertiajs/vue3";
import {onErrorDialog, onSuccessNotification} from "@/event-bus.js";



//Uses
const page = usePage();
const deleteForm = useForm({
    all: Boolean,
    ids: Array,
});

//Refs
const show = ref(false);


//Props & Emit
const emit = defineEmits(['confirm'])
const props = defineProps({
    all: {
        type: Boolean,
        required: false,
        default: false,
    },
    ids: {
        type: Array,
        required: false,
    },
});

//Methods
function handleDelete() {
    if (!props.all && props.ids.length === 0) {
        return;
    }

    deleteForm.all = props.all;
    deleteForm.ids = props.ids;

    deleteForm.delete(route("file.delete"), {
        onSuccess: () => {
            emit("confirm");
            show.value = false;
            onSuccessNotification("Files have been deleted.")
        },
        onError: () => {
            emit('confirm')
            show.value = false;
            onErrorDialog("An error occurred while deleting files. Could not delete files");
        }
    });
}

function showDialog() {
    if (!props.all && !props.ids.length) {
        onErrorDialog("Please select files to delete permanently.");
        return;
    }
    show.value = true;
}

function handleClose(){
    show.value = false;
}

</script>
