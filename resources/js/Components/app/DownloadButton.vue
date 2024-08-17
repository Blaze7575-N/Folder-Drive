<template>
    <PrimaryButton
        @click.prevent="download"
        class="px-3 py-0 normal-case text-[16px]"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="size-5 mr-2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"
            />
        </svg>
        <span>Download</span>
    </PrimaryButton>
</template>

<script setup>
//Imports
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { usePage } from "@inertiajs/vue3";
import { httpGet } from "@/http-helper.js";

//Uses
const page = usePage();
//Refs

//Props & Emit
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
    sharedWithMe: Boolean,
});

//Methods
function download() {
    if (!props.all && props.ids.length === 0) {
        return;
    }

    const p = new URLSearchParams();

    // console.log(page.props);

    if (page.props?.folder && page.props.folder.data?.id) {
        p.append("parent_id", page.props.folder.data?.id);
    }

    p.append("all", props.all ? 1 : 0);
    for (let id of props.ids) {
        p.append("ids[]", id);
    }

    let url = route("file.download");
    if(props.sharedWithMe) url = route("file.sdownload");


    console.log("\n\n\n\n", url + "?" + p.toString());
    httpGet(url + "?" + p.toString()).then((res) => {
        // console.log(res.data);
        if (!res.data.url) return;

        const a = document.createElement("a");
        a.download = res.data.filename;
        a.href = res.data.url;
        a.click();
    });
}
</script>
