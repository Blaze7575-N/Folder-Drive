<template>
    <modal :show="modelValue" @show="onShow" max-width="2xl">
        <div class="px-8 pt-10 pb-5">
            <h2 class="text-2xl font-medium text-gray-900">
                Create New Folder
            </h2>
            <div class="mt-6">
                <InputLabel
                    for="folderName"
                    value="Folder Name"
                    class="sr-only"
                />

                <TextInput
                    type="text"
                    ref="folderNameInput"
                    id="folderName"
                    v-model="form.name"
                    class="px-4 py-4 mt-1 block w-full"
                    :class="
                        form.errors.name
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                            : ''
                    "
                    placeholder="Folder Name"
                    @keyup.enter="createFolder"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div class="mt-10 flex justify-end">
                <SecondaryButton @click="closeModal" class="px-8 py-4"
                    >Cancel</SecondaryButton
                >
                <PrimaryButton
                    class="ml-3 px-8 py-4"
                    :class="{ 'opacity-25': form.processing }"
                    @click="createFolder"
                    :disable="form.processing"
                >
                    Create
                </PrimaryButton>
            </div>
        </div>
    </modal>
</template>

<script setup>
//Imports
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { onSuccessNotification } from "@/event-bus.js";
import { useForm, usePage } from "@inertiajs/vue3";
import { nextTick, ref } from "vue";

//Uses
const form = useForm({
    name: "",
    parent_id: "",
});

const page = usePage();

//Refs

const folderNameInput = ref(null);

//Props & Emit

const { modelValue } = defineProps({
    modelValue: Boolean,
});
const emit = defineEmits(["update:modelValue"]);

//Methods

function onShow() {
    nextTick(() => folderNameInput.value.focus());
}

function createFolder(event) {
    event.preventDefault();
    form.parent_id = page.props.folder.data.id;
    form.post(route("folder.create"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
            onSuccessNotification("The Folder has been created.");
        },
        onError: () => folderNameInput.value.focus(),
    });
}

function closeModal() {
    emit("update:modelValue");
    form.clearErrors();
    form.reset();
}
</script>
