<template>
    <Modal :show="show" max-width="xl">
        <div class="p-6">
            <div class="text-4xl font-bold text-red-500">Error</div>
            <div class="mt-4 text-black text-lg">{{ message }}</div>
            <div class="h-9 flex justify-end">
                <PrimaryButton class="px-6 h-10" @click="close">
                    OK
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
//Imports
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { emitter, ON_UPLOAD_ERROR } from "@/event-bus.js";
import { onMounted, ref } from "vue";

//Uses

//Methods
function close() {
    show.value = false;
    message.value = "";
}

onMounted(() => {
    emitter.on(ON_UPLOAD_ERROR, (msg) => {
        // console.log(msg);
        message.value = msg;
        show.value = true;
    });
});

//Refs
const message = ref("");
const show = ref(false);

//Props & Emit
const emit = defineEmits(["close"]);
</script>
