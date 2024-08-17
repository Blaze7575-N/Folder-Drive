<script setup>

const props = defineProps({
    prop1:String,
    prop2:String,
    prop3:String,
    // prop4:String,
    class:String,
})

</script>


<template>
    <div class="p-4 mt-10 bg-gray-300 rounded-lg">
        <span>{{props.prop1}}</span><br>
        <span>{{props.prop2}}</span><br>
        <span>{{props.prop3}}</span><br>
        <span>{{props.prop4}}</span><br>
        <span>{{props.class}}</span><br>
    </div>
</template>


<!-- testing complete: Result props can be passed to an element but to accesss props they need to defined in the definition of the element and using the defineProps() we can check as well 
     as retrieve the props and then use the object to use the props in our code-->
