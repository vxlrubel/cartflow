<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import Link from '@tiptap/extension-link'
import Underline from '@tiptap/extension-underline'
import Subscript from '@tiptap/extension-subscript'
import Superscript from '@tiptap/extension-superscript'
import { watch, onBeforeUnmount, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Enter description...',
  },
})

const emit = defineEmits(['update:modelValue'])

const showLinkInput = ref(false)
const linkUrl = ref('')

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit.configure({
      codeBlock: false,
    }),
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
    Link.configure({
      openOnClick: false,
    }),
    Underline,
    Subscript,
    Superscript,
  ],
  editorProps: {
    attributes: {
      class: 'prose prose-sm max-w-none min-h-[150px] p-3 focus:outline-none',
    },
  },
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML())
  },
})

watch(() => props.modelValue, (newValue) => {
  if (editor.value && editor.value.getHTML() !== newValue) {
    editor.value.commands.setContent(newValue, false)
  }
})

onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy()
  }
})

const setLink = () => {
  if (linkUrl.value) {
    editor.value.chain().focus().setLink({ href: linkUrl.value }).run()
  } else {
    editor.value.chain().focus().unsetLink().run()
  }
  showLinkInput.value = false
  linkUrl.value = ''
}

const closeLinkInput = () => {
  showLinkInput.value = false
  linkUrl.value = ''
}

const isHeadingActive = (level) => editor.value?.isActive('heading', { level })
</script>

<template>
  <div class="border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
    <div v-if="editor" class="flex flex-wrap items-center gap-1 p-2 bg-gray-50 border-b border-gray-300">
      <!-- Text Format -->
      <button
        type="button"
        @click="editor.chain().focus().toggleBold().run()"
        :class="{ 'bg-gray-200': editor.isActive('bold') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Bold (Ctrl+B)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleItalic().run()"
        :class="{ 'bg-gray-200': editor.isActive('italic') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Italic (Ctrl+I)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4m-2 0v16m-4 0h8" transform="skewX(-12)" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleUnderline().run()"
        :class="{ 'bg-gray-200': editor.isActive('underline') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Underline (Ctrl+U)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3M4 21h16" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleStrike().run()"
        :class="{ 'bg-gray-200': editor.isActive('strike') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Strikethrough"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16M7 8c0 2 2 3 5 3s5-1 5-3" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleCode().run()"
        :class="{ 'bg-gray-200': editor.isActive('code') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Inline Code"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
        </svg>
      </button>

      <div class="w-px h-5 bg-gray-300 mx-1"></div>

      <!-- Headings -->
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
        :class="{ 'bg-gray-200': isHeadingActive(1) }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors font-bold text-sm"
        title="Heading 1"
      >
        H1
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
        :class="{ 'bg-gray-200': isHeadingActive(2) }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors font-bold text-sm"
        title="Heading 2"
      >
        H2
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
        :class="{ 'bg-gray-200': isHeadingActive(3) }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors font-bold text-sm"
        title="Heading 3"
      >
        H3
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleParagraph().run()"
        :class="{ 'bg-gray-200': editor.isActive('paragraph') && !isHeadingActive(1) && !isHeadingActive(2) && !isHeadingActive(3) }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors text-sm"
        title="Paragraph"
      >
        P
      </button>

      <div class="w-px h-5 bg-gray-300 mx-1"></div>

      <!-- Lists -->
      <button
        type="button"
        @click="editor.chain().focus().toggleBulletList().run()"
        :class="{ 'bg-gray-200': editor.isActive('bulletList') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Bullet List"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleOrderedList().run()"
        :class="{ 'bg-gray-200': editor.isActive('orderedList') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Numbered List"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10M3 8h.01M3 12h.01M3 16h.01" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleBlockquote().run()"
        :class="{ 'bg-gray-200': editor.isActive('blockquote') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Blockquote"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16h6" />
        </svg>
      </button>

      <div class="w-px h-5 bg-gray-300 mx-1"></div>

      <!-- Text Alignment -->
      <button
        type="button"
        @click="editor.chain().focus().setTextAlign('left').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'left' }) }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Align Left"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8M4 18h16" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().setTextAlign('center').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'center' }) }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Align Center"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M8 12h8M4 18h16" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().setTextAlign('right').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'right' }) }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Align Right"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M12 12h8M4 18h16" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().setTextAlign('justify').run()"
        :class="{ 'bg-gray-200': editor.isActive({ textAlign: 'justify' }) }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors"
        title="Justify"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <div class="w-px h-5 bg-gray-300 mx-1"></div>

      <!-- Link -->
      <div class="relative">
        <button
          type="button"
          @click="showLinkInput = !showLinkInput"
          :class="{ 'bg-gray-200': editor.isActive('link') }"
          class="p-1.5 rounded hover:bg-gray-200 transition-colors"
          title="Insert Link"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
        </button>
        <div
          v-if="showLinkInput"
          class="absolute top-full left-0 mt-1 p-2 bg-white border border-gray-300 rounded-lg shadow-lg z-10 flex items-center gap-2"
        >
          <input
            v-model="linkUrl"
            type="url"
            placeholder="Enter URL"
            class="select text-sm py-1"
            @keyup.enter="setLink"
          />
          <button
            type="button"
            @click="setLink"
            class="px-2 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700"
          >
            Add
          </button>
          <button
            v-if="editor.isActive('link')"
            type="button"
            @click="editor.chain().focus().unsetLink().run(); closeLinkInput()"
            class="px-2 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600"
          >
            Remove
          </button>
          <button
            type="button"
            @click="closeLinkInput"
            class="text-gray-500 hover:text-gray-700"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <div class="w-px h-5 bg-gray-300 mx-1"></div>

      <!-- Subscript / Superscript -->
      <button
        type="button"
        @click="editor.chain().focus().toggleSubscript().run()"
        :class="{ 'bg-gray-200': editor.isActive('subscript') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors text-xs"
        title="Subscript"
      >
        X<sub>2</sub>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().toggleSuperscript().run()"
        :class="{ 'bg-gray-200': editor.isActive('superscript') }"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors text-xs"
        title="Superscript"
      >
        X<sup>2</sup>
      </button>

      <div class="w-px h-5 bg-gray-300 mx-1"></div>

      <!-- Undo / Redo -->
      <button
        type="button"
        @click="editor.chain().focus().undo().run()"
        :disabled="!editor.can().undo()"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors disabled:opacity-50"
        title="Undo (Ctrl+Z)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
        </svg>
      </button>
      <button
        type="button"
        @click="editor.chain().focus().redo().run()"
        :disabled="!editor.can().redo()"
        class="p-1.5 rounded hover:bg-gray-200 transition-colors disabled:opacity-50"
        title="Redo (Ctrl+Y)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
        </svg>
      </button>
    </div>
    <editor-content :editor="editor" :placeholder="placeholder" />
  </div>
</template>

<style scoped>
.ProseMirror {
  min-height: 150px;
}

.ProseMirror p.is-editor-empty:first-child::before {
  color: #9ca3af;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

.ProseMirror ul {
  list-style-type: disc;
  padding-left: 1.5rem;
}

.ProseMirror ol {
  list-style-type: decimal;
  padding-left: 1.5rem;
}

.ProseMirror blockquote {
  border-left: 3px solid #d1d5db;
  padding-left: 1rem;
  margin-left: 0;
  color: #6b7280;
}

.ProseMirror code {
  background-color: #f3f4f6;
  padding: 0.2rem 0.4rem;
  border-radius: 0.25rem;
  font-family: monospace;
  font-size: 0.875em;
}
</style>