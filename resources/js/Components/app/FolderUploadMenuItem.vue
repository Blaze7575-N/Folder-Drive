<template>
    <MenuItem v-slot="{ active }">
        <a href="#" class="relative">
            Upload Folder
            <input
                type="file"
                @change="onChange"
                class="absolute left-0 right-0 top-0 bottom-0 p-1 opacity-0"
                placeholder="Chose File"
                multiple
                directory
                webkitdirectory
            />
        </a>
    </MenuItem>
</template>

<script setup>

//Imports
import { MenuItem } from "@headlessui/vue";
import { emitter, FILE_UPLOAD_STARTED } from "@/event-bus.js";


//Uses

//Methods
function onChange(ev) {
    emitter.emit(FILE_UPLOAD_STARTED, ev.target.files)
}


//Refs

//Props & Emit
</script>
