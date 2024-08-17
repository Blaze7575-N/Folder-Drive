<template>
    <div class="w-56 text-rightx">
        <Menu as="div" class="relative inline-block text-left">
            <div>
                <MenuButton
                    class="w-full px-4 py-2 inline-flex justify-center rounded-md  text-xl font-medium text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75">
                    {{ $page.props.auth.user.name }}
                    <ChevronDownIcon class="h-6 w-6 ml-2 my-auto text-black-200" aria-hidden="true" />
                </MenuButton>
            </div>

            <transition enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0">
                <MenuItems
                    class="absolute right-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                    <div class="px-1 py-1">
                        <MenuItem v-slot="{ active }">
                        <ResponsiveNavLink :href="route('profile.edit')" as="button">
                            Profile
                        </ResponsiveNavLink>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            Logout
                        </ResponsiveNavLink>
                        </MenuItem>
                    </div>

                </MenuItems>
            </transition>
        </Menu>
    </div>
</template>

<script setup>

// Imports
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import { ChevronDownIcon } from '@heroicons/vue/20/solid';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';


// Uses


</script>