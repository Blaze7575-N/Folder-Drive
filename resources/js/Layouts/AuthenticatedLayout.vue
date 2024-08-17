<template>
    <div class="w-full h-screen flex bg-gray-50 gap-4 relative">
        <Navigation />

        <main
            class="flex flex-col flex-1 px-4 pt-8 overflow-scroll"
            :class="dragOver ? 'dropzone' : ''"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
            @drop.prevent="handleDrop"
        >
            <template v-if="dragOver">
                <div class="mb-20 pointer-events-none">Drop Your Files Here</div>
            </template>

            <template v-else>
                <div class="flex justify-between items-center">
                    <SearchForm />
                    <UserSettings />
                </div>
                <div class="flex flex-col flex-1 overflow-hidden">
                    <slot />
                </div>
            </template>
        </main>

        <ErrorDialog />
        <UploadProgress :form="form" />
        <Notification />
    </div>
</template>

<script setup>
//Imports
import Navigation from "@/Components/app/Navigation.vue";
import SearchForm from "@/Components/app/SearchForm.vue";
import UserSettings from "@/Components/app/UserSettingDropdown.vue";
import UploadProgress from "@/Components/app/UploadProgress.vue";
import ErrorDialog from "@/Components/app/ErrorDialog.vue";
import Notification from "@/Components/Notification.vue";
import { onMounted, ref } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { emitter, FILE_UPLOAD_STARTED, onErrorDialog } from "@/event-bus.js";

//Uses
const page = usePage();
const form = useForm({
    files: [],
    parent_id: null,
    relative_paths: [],
});

//Methods

function onDragOver() {
    dragOver.value = true;
}

function onDragLeave() {
    dragOver.value = false;
}

function handleDrop(ev) {
    // console.log(ev.dataTransfer);
    const files = ev.dataTransfer.files;
    if (!files.length) {
        return;
    }
    
    uploadFiles(files);
    dragOver.value = false;
}

function uploadFiles(files) {
    // console.log(files)
    form.files = files;
    form.parent_id = page.props.folder.data.id;
    form.relative_paths = [...files].map((f) => f.webkitRelativePath);
    // console.log(form.files, form.parent_id, form.relative_paths);
    form.post(route("file.store"), {
        onError: (error) => {
            let message = "";

            if (Object.keys(error).length > 0) {
                message = error[Object.keys(error)[0]];
            } else {
                message = "Error during file upload. Please try again later.";
            }

            onErrorDialog(message);
        }
    });
}

onMounted(() => {
    emitter.on(FILE_UPLOAD_STARTED, uploadFiles);
});

//Refs
const dragOver = ref(false);

//Props & Emit
</script>

<style scooped>
.dropzone {
    width: 100%;
    height: 100%;
    font-size: 30px;
    color: gray;
    border: 2px dashed black;
    opacity: 0.75;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
