<template>
    <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
        <template slot="field">
            <multiselect
                v-model="value"
                :options="options"
                :searchable="true"
                :disabled="isReadonly"
                track-by="value"
                label="label"
                placeholder="Pick a value"
                @input="onChange">
            </multiselect>
        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import Multiselect from "vue-multiselect";

export default {
    components: { Multiselect },
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            options: []
        };
    },

    created() {
        if (this.field.dependsOn) {
            this.field.dependsOn.forEach(function(item) {
                Nova.$on("nova-dynamic-select-changed-" + this.addFlexibleContentPrefix(item, this.field), this.onDependencyChanged);
            }, this);
        }
    },

    beforeDestroy() {
        if (this.field.dependsOn) {
            this.field.dependsOn.forEach(function(item) {
                Nova.$off("nova-dynamic-select-changed-" + this.addFlexibleContentPrefix(item, this.field), this.onDependencyChanged);
            }, this);
        }
    },

    methods: {
        addFlexibleContentPrefix(item, field) {
            var splitted = field.attribute.toLowerCase().split('__');
            return (splitted.length === 2 ? splitted[0] + '__' : '') + item;
        },

        removeFlexibleContentPrefix(field) {
            return field.split('__').length === 2 ? field.split('__')[field.split('__').length - 1] : field
        },

        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.options = this.field.options;
            if(this.field.value) {
                this.value = this.options.find(item => item['value'] == this.field.value);
            }
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(
                this.field.attribute,
                typeof this.value == 'undefined' || !this.value ? '' : this.value.value
            )
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },

        getDependValues(value, field) {
            this.field.dependValues[this.removeFlexibleContentPrefix(field)] = value;
            return this.field.dependValues;
        },

        async onChange(row) {
            Nova.$emit("nova-dynamic-select-changed-" + this.field.attribute.toLowerCase(), {
                value: row ? row.value : null,
                field: this.field
            });
        },

        async onDependencyChanged(dependsOnValue) {
            Nova.$emit("nova-dynamic-select-changed-" + this.field.attribute.toLowerCase(), {
                value: this.value,
                field: this.field
            });
            if(this.$parent.$parent.$options.name == 'confirm-action-modal') {

                let dependValues = {};
                for (const [key, value] of Object.entries(this.getDependValues(dependsOnValue.value, dependsOnValue.field.attribute.toLowerCase()))) {
                    dependValues[key] = value;
                }

                this.options = (await Nova.request().get("/nova-vendor/dynamic-select/action-options/"+this.resourceName, {
                    params: {
                        action: this.$parent.$parent.$options.propsData.action.uriKey,
                        pivotAction: false,
                        viaResource: '',
                        viaResourceId: '',
                        viaRelationship: '',
                        attribute: this.removeFlexibleContentPrefix(this.field.attribute),
                        depends: dependValues,
                        resources: this.$parent.$parent.selectedResources.join(',')
                    }
                })).data.options;
            } else {
                this.options = (await Nova.request().post("/nova-vendor/dynamic-select/options/"+this.resourceName, {
                    attribute: this.removeFlexibleContentPrefix(this.field.attribute),
                    depends: this.getDependValues(dependsOnValue.value, dependsOnValue.field.attribute.toLowerCase())
                })).data.options;
            }

            if(this.value) {
                this.value = this.options.find(item => item['value'] == this.value['value']);
            }
        }
    },
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style>
    .multiselect {
        box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
        min-height: 36px !important;
        border-radius: 0.5rem;
    }
    .multiselect__tags {
        min-height: 36px !important;
        border: 1px solid var(--60) !important;
        color: var(--80);
        border-radius: 0.5rem !important;
    }
    .multiselect__select {
        background-repeat: no-repeat;
        background-size: 10px 6px;
        background-position: center right 0.75rem;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 6"><path fill="#35393C" fill-rule="nonzero" d="M8.293.293a1 1 0 0 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4A1 1 0 0 1 1.707.293L5 3.586 8.293.293z"/></svg>');
    }
    .multiselect__select:before {
        content: none !important;
    }

    .multiselect__content-wrapper {
        overflow: initial !important;
    }
</style>
