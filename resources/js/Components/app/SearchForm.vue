<template>
    <div class="w-[600px] h-80px flex items-center">
        <!-- <pre> {{ form.search }} </pre> -->
        <!-- <pre> {{ $page }} </pre> -->
        <TextInput
            type="text"
            v-model="form.search"
            autocomplete="false"
            placeholder="Search for files and folder"
            class="w-full py-2 px-3"
            @keyup.enter.prevent="onSearch"
        />
    </div>
</template>

<script setup>
// imports
import TextInput from "@/Components/TextInput.vue";
import { useForm, usePage, router } from "@inertiajs/vue3";

// Uses
const form = useForm({
    search: "",
});
const page = usePage();

//Refs

//Props & Emit

//Methods
function onSearch() {
    console.log(form.search, page.props);

    // if (!page.props.folder.path) console.log("nothing");
    router.visit(window.location.pathname, {
        data:{search:form.search},
        onSuccess: () => {
            console.log("Searching Sucessfull");
        },
    });
}
</script>
