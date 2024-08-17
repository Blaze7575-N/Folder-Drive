<template>
    <div
        v-if="form.progress"
        class="w-[25%] px-6 py-4 bg-white border-gray-900 border rounded-md absolute left-[39%] bottom-8"
    >
        <div class="flex justify-center">
            Uploading {{ form.files.length }} files
        </div>
        <div class="w-full h-[5px] mt-1 bg-gray-200 rounded-full">
            <div
                class="w-full h-full bg-gray-700 rounded-full transition-all"
                :style="{ width: form.progress.percentage + '%' }"
            />
        </div>
    </div>
</template>

<script setup>
//Imports

//Uses

//Methods

//Refs

//Props & Emit
const props = defineProps({
    form: Object,
});
</script>
