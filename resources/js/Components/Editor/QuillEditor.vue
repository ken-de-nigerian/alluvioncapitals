<template>
    <div class="quill-editor">
        <QuillEditor ref="quill" v-model:content="content" :options="editorOptions" contentType="html" @update:content="handleUpdate" />
    </div>
</template>

<script setup lang="ts">
    import { QuillEditor } from '@vueup/vue-quill'
    import '@vueup/vue-quill/dist/vue-quill.snow.css'
    import { ref, watch } from 'vue'

    const props = defineProps({
        modelValue: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: 'Write something...'
        },
        toolbar: {
            type: [Array, String],
            default: 'full'
        }
    })

    const emit = defineEmits(['update:modelValue'])

    const content = ref(props.modelValue)
    const quill = ref(null)

    // Editor options
    const editorOptions = ref({
        placeholder: props.placeholder,
        theme: 'bubble',
        modules: {
            toolbar: getToolbarOptions(props.toolbar)
        }
    })

    // Handle toolbar configuration
    function getToolbarOptions(toolbar) {
        if (Array.isArray(toolbar)) return toolbar

        const presets = {
            full: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean'],
                ['link', 'image', 'video']
            ],
            basic: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }

        return presets[toolbar] || presets.basic
    }

    // Handle content updates
    function handleUpdate(newContent) {
        emit('update:modelValue', newContent)
    }

    // Watch for external modelValue changes
    watch(() => props.modelValue, (newValue) => {
        if (newValue !== content.value) {
            content.value = newValue
        }
    })
</script>

<style>
    .ql-editor {
        min-height: 200px;
    }

    /* Customize the editor appearance */
    .ql-toolbar.ql-snow {
        border-radius: 4px 4px 0 0;
        border: 1px solid #ddd;
    }

    .ql-container.ql-snow {
        border-radius: 0 0 4px 4px;
        border: 1px solid #ddd;
        font-family: inherit;
    }

    .ql-toolbar.ql-snow + .ql-container.ql-snow {
        border-top: 0;
        margin-bottom: 10px;
    }
</style>
