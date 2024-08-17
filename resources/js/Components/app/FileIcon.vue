<template>
    <div>
        <span v-if="file.is_folder" class="h-6 w-6">
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
                    d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"
                />
            </svg>
        </span>
        <img
            class="w-6 h-6"
            v-else-if="isImage(file)"
            :src="`/Images/Icons/pic.svg`"
        />
        <img
            class="w-6 h-6"
            v-else-if="isPDF(file)"
            :src="`/Images/Icons/pdf.svg`"
        />
        <img
            class="w-6 h-6"
            v-else-if="isAudio(file)"
            :src="`/Images/Icons/mp3.svg`"
        />
        <img
            class="w-6 h-6"
            v-else-if="isVideo(file)"
            :src="`/Images/Icons/video.svg`"
        />
        <img
            class="w-6 h-6"
            v-else-if="isWord(file)"
            :src="`/Images/Icons/doc.svg`"
        />
        <img
            class="w-6 h-6"
            v-else-if="isExcel(file)"
            :src="`/Images/Icons/xls.svg`"
        />
        <img
            class="w-6 h-6"
            v-else-if="isZip(file)"
            :src="`/Images/Icons/zip.svg`"
        />
        <img
            class="w-6 h-6"
            v-else-if="isText(file)"
            :src="`/Images/Icons/txt.svg`"
        />
        <img class="w-6 h-6" v-else :src="`/Images/Icons/file.svg`" />
    </div>
</template>

<script setup>
//Imports
import {
    isImage,
    isPDF,
    isAudio,
    isVideo,
    isWord,
    isExcel,
    isZip,
    isText,
} from "@/Helper/file_helper.js";

//Uses

//Methods

//Refs

//Props & Emit
const { file } = defineProps({
    file: Object,
});
</script>
