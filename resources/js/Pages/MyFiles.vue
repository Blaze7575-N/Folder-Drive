<template>
    <AuthenticatedLayout>
        <!-- {{ onlyFavourite }} -->
        <nav class="flex items-center justify-between p-1 mt-10 mb-3">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li
                    v-for="ans of ancestors.data"
                    :key="ans.id"
                    class="inline-flex items-center"
                >
                    <Link
                        v-if="!ans.parent_id"
                        :href="route('myFiles')"
                        class="inline-flex items-center text-md font-medium text-gray-700 hover:text-blue-600"
                    >
                        <HomeIcon class="w-5 mr-[1px]" />
                        <div class="whitespace-nowrap">My Files</div>
                    </Link>
                    <div v-else class="flex items-center">
                        <svg
                            aria-hidden="true"
                            class="w-6 h-6 text-gray-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <Link
                            :href="route('myFiles', { folder: ans.path })"
                            class="ml-1 text-md font-medium text-gray-700 hover:text-blue-600 md:ml-2"
                        >
                            {{ ans.name }}
                        </Link>
                    </div>
                </li>
            </ol>
            <div class="flex gap-4">
                <div  class="py-3 flex">
                    <label class="text-lg font-medium">
                        OnlyFavourites
                        <Checkbox
                            @change="onlyFavourites"
                            v-model:checked="onlyFavourite"
                        />
                    </label>
                </div>
                <ShareFilesButton :allSelected="allSelected" :selectedIds="selectedIds" />
                <StarButton :all="allSelected" :ids="selectedIds" />
                <DownloadButton :all="allSelected" :ids="selectedIds" />
                <TrashButton
                    :allSelected="allSelected.value"
                    :selectedIds="selectedIds"
                    @delete="deleteFiles"
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
                            class="min-w-[600px] text-sm font-medium text-gray-900 px-6 py-4 text-left"
                        >
                            Name
                        </th>
                        <th
                            class="text-sm font-medium text-gray-900 px-6 py-4 text-left"
                        >
                            Owner
                        </th>
                        <th
                            class="text-sm font-medium text-gray-900 px-6 py-4 text-left"
                        >
                            Last Modified
                        </th>
                        <th
                            class="text-sm font-medium text-gray-900 px-6 py-4 text-left"
                        >
                            Size
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
                        @dblclick="openFolder(file)"
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
                        <td class="pl-6 py-4 flex justify-between">
                            <div class="flex gap-2">
                                <FileIcon :file="file" />
                                <div class="mt-[2px]">{{ file.name }}</div>
                            </div>
                            <img
                                v-if="file.is_favourite"
                                :src="StarFilled"
                                class="size-6"
                                @click.stop="Unstar(file)"
                            />
                            <img
                                v-else
                                :src="StarHollow"
                                class="size-6"
                                @click.stop="Unstar(file)"
                            />
                        </td>
                        <td class="px-6 py-4">
                            {{ file.owner }}
                        </td>
                        <td class="px-6 py-4">
                            {{ file.created_at }}
                        </td>
                        <td class="px-6 py-4">
                            {{ file.size }}
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
import TrashButton from "@/Components/app/TrashButton.vue";
import StarButton from "@/Components/app/StarButton.vue";
import ShareFilesButton from "@/Components/app/ShareFilesButton.vue";
import Notification from "@/Components/Notification.vue";
import { router, useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { HomeIcon } from "@heroicons/vue/20/solid";
import FileIcon from "@/Components/app/FileIcon.vue";
import StarHollow from "/public/Images/Icons/starHollow.svg";
import StarFilled from "/public/Images/Icons/starFilled.svg";
import { onMounted, onUpdated, computed, ref } from "vue";
import { httpGet } from "@/http-helper.js";
import { onErrorDialog, onSuccessNotification } from "@/event-bus.js";

//Uses
const form = useForm({
    id: null,
});

//Methods

function openFolder(file) {
    if (!file.is_folder) {
        return;
    }
    
    router.visit(route("myFiles", { folder: file.path }));
}

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

function deleteFiles() {
    allSelected.value = false;
    selected.value = {};
}

function Unstar(file) {
    // console.log(file.is_favourite);

    if (!file) {
        console.log("File not found");
        return;
    }

    form.id = file.id;

    form.post(route("file.addRemoveFavourite"), {
        onSuccess: () => {
            file.is_favourite
                ? onSuccessNotification("File Unstarred successfully")
                : onSuccessNotification("File Starred successfully");
        },
    });
}

function onlyFavourites() {
    const url = new URLSearchParams();
    url.set("favourite", onlyFavourite.value? 1: 0);
    console.log(url, props.folder.data);
    router.visit(route("myFiles", { folder: props.folder.data.path }), {
        data: url,
    });


    return;
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
const onlyFavourite = ref(false);

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

    let params = new URLSearchParams(window.location.search);
    // console.log(params.get('favourite'), params.get('favourite') == 1);
    onlyFavourite.value = params.get('favourite') == 1;

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
