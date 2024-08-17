<template>
    <Menu as="div" class="relative block text-left">
        <div>
            <MenuButton
                class="w-full px-4 py-2 mt-4 inline-flex justify-center rounded-md text-xl font-medium text-gray-900 border-[1px] border-black"
            >
                Create New
            </MenuButton>
        </div>

        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <MenuItems
                class="absolute right-0 w-36 mt-2 py-2 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 text-gray-700 font-medium"
            >
                <div class="px-3 py-2">
                    <MenuItem v-slot="{ active }">
                        <div>
                            <a href="#" @click.prevent="showCreateFolderModal"
                                >New Folder
                            </a>
                        </div>
                    </MenuItem>
                </div>
                <div class="px-3 py-2 flex flex-col gap-1">
                    <FileUploadMenuItem />
                    <FolderUploadMenuItem />
                </div>
            </MenuItems>
        </transition>
        <CreateFolderModal v-model="showModalValue" />
    </Menu>
</template>

<script setup>
// import

import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";
import CreateFolderModal from "@/Components/app/CreateFolderModal.vue";
import FileUploadMenuItem from "@/Components/app/FileUploadMenuItem.vue";
import FolderUploadMenuItem from "@/Components/app/FolderUploadMenuItem.vue";
import { ref } from "vue";

//Uses

//Refs
const showModalValue = ref(false);

//Methods
const showCreateFolderModal = () => {
    showModalValue.value = true;
};

function close() {}

//Props & Emit
</script>
