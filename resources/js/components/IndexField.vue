<template>
    <span>{{ field.options.find(item => item['value'] == field.value).label }}</span>
</template>

<script>
export default {
    props: ['resourceName', 'field'],
}
</script>
