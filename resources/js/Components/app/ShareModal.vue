<template>
    <modal :show="props.modelValue" @show="onShow" max-width="2xl">
        <div class="px-8 pt-10 pb-5">
            <h2 class="text-2xl font-medium text-gray-900">
                Share File or Folder
            </h2>
            <div class="mt-6">
                <InputLabel
                    for="EmailAddress"
                    value="Email Address"
                    class="sr-only"
                />

                <TextInput
                    type="email"
                    ref="emailInput"
                    id="EmailAddress"
                    v-model="form.email"
                    class="px-4 py-4 mt-1 block w-full"
                    :class="
                        form.errors.email
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                            : ''
                    "
                    placeholder="Email Address"
                    @keyup.enter.prevent="shareFile"
                />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>
            <div class="mt-10 flex justify-end">
                <SecondaryButton @click="closeModal" class="px-8 py-4"
                    >Cancel</SecondaryButton
                >
                <PrimaryButton
                    class="ml-3 px-8 py-4"
                    :class="{ 'opacity-25': form.processing }"
                    @click.prevent="shareFile"
                    :disable="form.processing"
                >
                    Share
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
    email: '',
    all: false,
    ids: [],
    parent_id: null,
});

const page = usePage();

//Refs

const emailInput = ref(false);

//Props & Emit

const props = defineProps({
    modelValue: Boolean,
    all: Boolean,
    ids: Array,
});
const emit = defineEmits(["update:modelValue"]);

//Methods

function onShow() {
    nextTick(() => emailInput.value.focus());
}

function shareFile(event) {
    // // event.preventDefault();
    // console.log("returning", props.all, props.ids);
    // return;

    if (props.all) {
        form.all = true;
        form.ids = [];
    } else {
        form.ids = props.ids;
    }
    form.parent_id = page.props.folder.data.id;
    console.log(form.email, form.all, form.ids, form.parent_id);

    form.post(route("file.share"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
            onSuccessNotification("File/Folder has been shared IF the User exists.");
        },
        onError: () => emailInput.value.focus(),
    });
}

function closeModal() {
    emit("update:modelValue");
    form.clearErrors();
    form.reset();
}
</script>
