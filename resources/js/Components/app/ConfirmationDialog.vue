<template>
    <Modal :show="props.show" max-width="xl">
        <div class="px-8 pt-10 pb-6">
            <h2 class="text-2xl mb-2 font-semibold">Please Confirm</h2>
            <span class="font-medium text-lg"> {{ props.message }}</span>
            <div class="h-10 mt-6 flex justify-end gap-4">
                <SecondaryButton @click="emit('cancel')">Cancel</SecondaryButton>
                <DangerButton @click="emit('confirm')">Confirm</DangerButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
//Imports
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

//Uses

//Refs

//Props & Emit
const props = defineProps({
    show: Boolean,
    message: String,
})
const emit = defineEmits(['confirm', 'cancel'])

//Methods
</script>
