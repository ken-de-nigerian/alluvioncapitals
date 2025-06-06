<template>
    <button :type="type" :class="customClasses || defaultClasses" :disabled="processing">
        <span v-if="processing">
            <span class="spinner spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span>
            Processing...
        </span>
        <span v-else>
            <slot>Submit</slot>
        </span>
    </button>
</template>

<script>
    export default {
        props: {
            type: {
                type: String,
                default: 'button'
            },
            processing: {
                type: Boolean,
                default: false
            },
            customClasses: {
                type: [String, Object, Array],
                default: null
            }
        },
        data() {
            return {
                defaultClasses: 'btn btn-lg btn-primary w-100'
            }
        }
    }
</script>
