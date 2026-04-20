<script setup>
import { ref, onMounted, watch } from 'vue'
import FilePond from 'filepond'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css'

FilePond.registerPlugin(FilePondPluginImagePreview)

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  acceptedFileTypes: {
    type: Array,
    default: () => ['image/*'],
  },
  maxFiles: {
    type: Number,
    default: 10,
  },
  serverUrl: {
    type: String,
    default: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000',
  },
})

const emit = defineEmits(['update:modelValue'])

const pondRef = ref(null)
let pond = null

const pondOptions = {
  allowMultiple: props.multiple,
  maxFiles: props.multiple ? props.maxFiles : 1,
  acceptedFileTypes: props.acceptedFileTypes,
  server: {
    url: `${props.serverUrl}/upload`,
    headers: {
      Authorization: `Bearer ${localStorage.getItem('token') || ''}`,
    },
  },
  onremovefile: (error, fileItem) => {
    if (error) {
      console.error('Error removing file:', error)
      return
    }
    // Delete from server
    const url = fileItem.serverId
    if (url) {
      try {
        fetch(`${props.serverUrl}/upload?url=${encodeURIComponent(url)}`, {
          method: 'DELETE',
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token') || ''}`,
          },
        })
      } catch (e) {
        console.error('Delete error:', e)
      }
    }
    updateFiles()
  },
  oninit: () => {},
  onaddfile: (error, fileItem) => {
    if (error) {
      console.error('Error adding file:', error)
      return
    }
    updateFiles()
  },
  onremovefile: (error, fileItem) => {
    if (error) {
      console.error('Error removing file:', error)
      return
    }
    updateFiles()
  },
  onprocessfile: (error, fileItem) => {
    if (error) {
      console.error('Error processing file:', error)
      return
    }
    updateFiles()
  },
}

const updateFiles = () => {
  if (!pond) return
  const files = pond.getFiles()
  const urls = files
    .filter((file) => file.serverId)
    .map((file) => {
      try {
        return JSON.parse(file.serverId).url || file.serverId
      } catch {
        return file.serverId
      }
    })
  emit('update:modelValue', urls)
}

onMounted(() => {
  if (pondRef.value) {
    pond = FilePond.create(pondRef.value, pondOptions)
    
    // Load existing images
    if (props.modelValue && props.modelValue.length > 0) {
      props.modelValue.forEach((url) => {
        pond.addFile(url, {
          source: url,
          options: {
            type: 'local',
          },
        })
      })
    }
  }
})

watch(
  () => props.modelValue,
  (newUrls) => {
    if (pond && newUrls && newUrls.length > 0) {
      const existingFiles = pond.getFiles()
      if (existingFiles.length === 0 && newUrls.length > 0) {
        newUrls.forEach((url) => {
          pond.addFile(url, {
            source: url,
            options: {
              type: 'local',
            },
          })
        })
      }
    }
  },
)
</script>

<template>
  <div>
    <input ref="pondRef" type="file" />
  </div>
</template>