<template>
    <div>
        <button
            @click="handleStarringFiles"
            class="inline-flex items-center h-full px-4 py-2 m-0 bg-[#1F2937] text-white text-lg font-medium border border-gray-200 rounded-lg hover:opacity-[.90] focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700"
        >
            <!-- {{ selectedIds }} -->
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
                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"
                />
            </svg>
        </button>
    </div>
</template>

<script setup>
//Imports
import { useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { onErrorDialog, onSuccessNotification } from "@/event-bus.js";

//Uses
const starForm = useForm({
    all: null,
    ids: [],
    parent_id: null,
});
const page = usePage();

//Refs
const selectedIds = computed(() => {
    const files = page.props.files.data;
    let data = [];
    files.map((file) => {
        if (props.ids.includes(file.id.toString()) && !file.is_favourite) {
            data.push(file.id);
        }
    });
    return data;
});

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
});

//Methods
function handleStarringFiles() {
    if (!props.all && !props.ids.length) {
        onErrorDialog("Please select atleast one file.");
        return;
    } else if (!selectedIds.value.length) return;

    props.allSelected
        ? (starForm.all = true)
        : (starForm.ids = selectedIds.value);

    starForm.parent_id = page.props.folder.data?.id;

    // console.log("returning", props.all, props.ids);
    // return;

    starForm.post(route("file.addToFavourite"), {
        preserveScroll: true,
        onSuccess: () => {
            starForm.ids = [];
            starForm.all = false;
            onSuccessNotification("The Files have been starred.");
        },
    });
}
</script>
