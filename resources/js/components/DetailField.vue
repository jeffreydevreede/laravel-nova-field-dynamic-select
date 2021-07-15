<template>
    <panel-item :field="this.dynamicField" />
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],

    computed: {
        dynamicField() {
            this.field.value = this.field.options.find(item => item['value'] == this.field.value).label;
            return this.field;
        },
    },
}
</script>
