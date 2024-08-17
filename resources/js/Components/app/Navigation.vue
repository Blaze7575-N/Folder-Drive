<template>
    <nav class="min-w-[200px] pt-4">
        <Link href="/dashboard"  class="h-[80px] px-3 flex items-center gap-3">
        <ApplicationLogo class=" h-9 fill-current text-gray-800" />
        LaraStorage
        </Link>

        <div class="px-3">
            <CreateNewDropdown />
            <div class="py-3">
                <Navlink :href="route('myFiles')"  :active="$page.url.substring(0,9) === '/my-files'"> My Files</Navlink>
                <Navlink :href="route('SWMe')" :active="$page.url.substring(0,15) === '/shared-with-me'">Shared with me</Navlink>
                <Navlink :href="route('SBMe')" :active="$page.url.substring(0,13) === '/shared-by-me'">Shared by me</Navlink>
                <Navlink :href="route('trash')" :active="$page.url.substring(0,6) === '/trash'">Trash</Navlink>
            </div>
        </div>
    </nav>
</template>

<script setup>

import { Link } from '@inertiajs/vue3'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Navlink from "@/Components/NavLink.vue";
import CreateNewDropdown from "@/Components/app/CreateNewDropdown.vue"

</script>