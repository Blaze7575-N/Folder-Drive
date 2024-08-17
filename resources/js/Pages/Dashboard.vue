<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Test from '@/Components/app/test.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You're logged in!</div>
                </div>
            </div>
        </div>

        
        <Test prop1="Hi this is prop 1" prop2="Prop 2 here" prop3="I am prop 3" prop4="I came uninvited" class="pokemon"/>

    </AuthenticatedLayout>
</template>
