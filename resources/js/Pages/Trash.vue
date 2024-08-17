<template>
    <AuthenticatedLayout>
        <nav class="flex items-center justify-end p-1 mt-10 mb-3">
            <div class="flex gap-4">
                <DeleteForeverButton
                    :all="allSelected"
                    :ids="selectedIds"
                    @confirm="formReset"
                />
                <RestoreFileButton
                    :allSelected="allSelected"
                    :selectedIds="selectedIds"
                    @confirm="formReset"
                />
            </div>
        </nav>
        <!-- <pre>{{  }} </pre> -->
        <!-- <pre>{{ selectedIds }} </pre> -->

        <!-- Table -->
        <div class="flex-1 overflow-auto">
            <table class="min-w-full mt-0">
                <thead>
                    <tr class="bg-gray-400 border-b">
                        <th class="w-[30px] max-w-[30px]">
                            <Checkbox
                                @change="onSelectAllChange"
                                v-model:checked="allSelected"
                            />
                        </th>
                        <th
                            class="text-sm font-medium text-gray-900 px-6 py-4 text-left"
                        >
                            Name
                        </th>
                        <th
                            class="text-sm font-medium text-gray-900 px-6 py-4 text-left"
                        >
                            Path
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="file of allFiles.data"
                        :key="file.id"
                        class="border-b transition duration-300 ease-in-out hover:bg-blue-100 cursor-pointer"
                        :class="
                            selected[file.id] || allSelected ? 'bg-blue-50' : ''
                        "
                        @click="(event) => toggleSelectChange(file, event)"
                    >
                        <td class="w-[30px] max-w-[30px] px-[8px]">
                            <Checkbox
                                @change="
                                    (event) => checkSelectChange(file, event)
                                "
                                v-model="selected[file.id]"
                                :checked="selected[file.id] || allSelected"
                            />
                        </td>
                        <td class="px-6 py-4 flex gap-2">
                            <FileIcon :file="file" />
                            <div class="mt-[2px]">{{ file.name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            {{ file.path }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div ref="loadMoreIntersect"></div>
        </div>

        <div v-if="!allFiles.data.length" class="w-full h-full overflow-auto">
            <div
                class="w-[20vw] px-8 py-4 pr-10 mt-[7.5vh] ml-10 rounded-r-lg bg-gray-500 text-white text-xl font-bold opacity-80"
            >
                The folder is Empty
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
//imports
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Checkbox from "@/Components/Checkbox.vue";
import DownloadButton from "@/Components/app/DownloadButton.vue";
import RestoreFileButton from "@/Components/app/RestoreFileButton.vue";
import DeleteForeverButton from "@/Components/app/DeleteForeverButton.vue";
import Notification from "@/Components/Notification.vue";
import { router, Link } from "@inertiajs/vue3";
import { HomeIcon } from "@heroicons/vue/20/solid";
import FileIcon from "@/Components/app/FileIcon.vue";
import { onMounted, onUpdated, computed, ref } from "vue";
import { httpGet } from "@/http-helper.js";

//Uses

//Methods

async function loadMore() {
    // console.log("load More");
    // console.log(allFiles.value.next);

    const url = allFiles.value.next;
    if (!url) return;

    const response = await httpGet(url).then((response) => {
        // console.log(response);
        allFiles.value.data = [...allFiles.value.data, ...response.data.data];
        allFiles.value.next = response.data.links.next;
        response.data.data.map((file) => fileIndexes.value.push(file.id));
        // console.log(fileIndexes.value);
    });
}

function onSelectAllChange() {
    allFiles.value.data.forEach((file) => {
        selected.value[file.id] = allSelected.value;
    });
    lastSelectedIndex.value = [];
}

function toggleSelectChange(file, event) {
    selected.value[file.id] = !selected.value[file.id];

    if (event.shiftKey && lastSelectedIndex.value.length > 0) {
        // console.log(
        //     lastSelectedIndex.value,
        //     lastSelectedIndex.value[lastSelectedIndex.value.length - 1]
        // );

        const lastSelected = fileIndexes.value.indexOf(
            lastSelectedIndex.value[lastSelectedIndex.value.length - 1]
        );
        const currentSelected = fileIndexes.value.indexOf(file.id);
        // console.log(lastSelected, currentSelected);

        // just to deal with the edge case where you click the last selected file again and the indexes are equal and we dont need to run this code segment.
        if (!(currentSelected == lastSelected)) {
            const selection =
                lastSelected > currentSelected
                    ? fileIndexes.value.slice(currentSelected, lastSelected + 1)
                    : fileIndexes.value.slice(
                          lastSelected,
                          currentSelected + 1
                      );

            // console.log("Past Selection", selection);

            for (let file of selection) {
                selected.value[file] = true;
            }
        }
    }

    if (selected.value[file.id]) lastSelectedIndex.value.push(file.id);
    checkSelectChange(file, event);
}
function checkSelectChange(file, event) {
    if (!selected.value[file.id]) {
        allSelected.value = false;
    } else {
        let checked = true;
        for (let file of allFiles.value.data) {
            if (!selected.value[file.id]) {
                checked = false;
                break;
            }
        }

        checked ? (allSelected.value = true) : (allSelected.value = false);
    }
}

function formReset(params) {
    allSelected.value = false;
    selected.value = {};
}

//Refs
const loadMoreIntersect = ref(null);
const allFiles = ref({
    data: [],
    next: null,
});
const allSelected = ref(false);
const selected = ref({});
const fileIndexes = ref([]);
const lastSelectedIndex = ref([]);

const selectedIds = computed(() => {
    return Object.entries(selected.value)
        .filter((elem) => elem[1])
        .map((elem) => elem[0]);
});

//Props & Emit

const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
});

//Hooks
onUpdated(() => {
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next,
    };
});

onMounted(() => {
    // console.log(props);
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next,
    };

    fileIndexes.value = props.files.data.map((file) => file.id);
    // console.log(fileIndexes.value);

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => entry.isIntersecting && loadMore());
        },
        {
            rootMargin: "-250px 0px 0px 0px",
        }
    );

    observer.observe(loadMoreIntersect.value);
});
</script>
