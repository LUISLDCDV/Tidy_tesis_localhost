<template>
  <div class="image-uploader">
    <!-- Botón para subir imagen -->
    <q-btn
      v-if="!uploading && !imageUrl"
      icon="cloud_upload"
      :label="$t('common.uploadImage')"
      color="primary"
      @click="openFileDialog"
      class="q-mb-md"
    />

    <!-- Input file oculto -->
    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      multiple
      style="display: none"
      @change="handleFileSelect"
    />

    <!-- Progreso de subida -->
    <div v-if="uploading" class="upload-progress q-mb-md">
      <q-linear-progress
        :value="uploadProgress"
        color="primary"
        size="md"
        class="q-mb-sm"
      />
      <div class="text-center text-caption">
        {{ $t('common.uploading') }}... {{ Math.round(uploadProgress * 100) }}%
      </div>
    </div>

    <!-- Preview de imágenes -->
    <div v-if="images.length > 0" class="images-grid q-mb-md">
      <div
        v-for="(image, index) in images"
        :key="index"
        class="image-item"
      >
        <q-img
          :src="image.url"
          :alt="image.name"
          class="image-preview"
          fit="cover"
          loading="lazy"
        >
          <div class="absolute-top-right q-pa-xs">
            <q-btn
              icon="close"
              color="negative"
              round
              size="sm"
              @click="removeImage(index)"
              class="bg-red text-white"
            />
          </div>

          <div class="absolute-bottom text-caption text-white bg-black-50 q-pa-xs">
            {{ image.name }}
          </div>
        </q-img>
      </div>
    </div>

    <!-- Botón para agregar más imágenes -->
    <q-btn
      v-if="images.length > 0 && !uploading"
      icon="add_photo_alternate"
      :label="$t('common.addMoreImages')"
      color="secondary"
      outline
      @click="openFileDialog"
      class="q-mb-md"
    />

    <!-- Error display -->
    <q-banner
      v-if="error"
      class="text-negative q-mb-md"
      icon="error"
      rounded
    >
      {{ error }}
      <template v-slot:action>
        <q-btn
          flat
          color="negative"
          label="Cerrar"
          @click="error = null"
        />
      </template>
    </q-banner>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { Notify } from 'quasar'
import firebaseService from '@/services/firebase'

export default {
  name: 'ImageUploader',
  props: {
    initialImages: {
      type: Array,
      default: () => []
    },
    maxImages: {
      type: Number,
      default: 5
    },
    maxSizePerImage: {
      type: Number,
      default: 5 * 1024 * 1024 // 5MB
    },
    path: {
      type: String,
      default: 'general'
    }
  },
  emits: ['update:images', 'upload-complete', 'upload-error'],
  setup(props, { emit }) {
    const { t } = useI18n()

    const fileInput = ref(null)
    const uploading = ref(false)
    const uploadProgress = ref(0)
    const error = ref(null)
    const images = ref([...props.initialImages])

    const canAddMore = computed(() => {
      return images.value.length < props.maxImages
    })

    const openFileDialog = () => {
      if (!canAddMore.value) {
        Notify.create({
          type: 'warning',
          message: t('common.maxImagesReached', { max: props.maxImages })
        })
        return
      }
      fileInput.value?.click()
    }

    const handleFileSelect = async (event) => {
      const files = Array.from(event.target.files)

      if (files.length === 0) return

      // Validar número máximo de imágenes
      const availableSlots = props.maxImages - images.value.length
      const filesToUpload = files.slice(0, availableSlots)

      if (files.length > availableSlots) {
        Notify.create({
          type: 'warning',
          message: t('common.someImagesSkipped', {
            skipped: files.length - availableSlots,
            max: props.maxImages
          })
        })
      }

      // Validar tamaños
      const validFiles = filesToUpload.filter(file => {
        if (file.size > props.maxSizePerImage) {
          Notify.create({
            type: 'negative',
            message: t('common.fileTooLarge', {
              filename: file.name,
              maxSize: formatFileSize(props.maxSizePerImage)
            })
          })
          return false
        }
        return true
      })

      if (validFiles.length === 0) return

      await uploadFiles(validFiles)

      // Limpiar input
      event.target.value = ''
    }

    const uploadFiles = async (files) => {
      uploading.value = true
      uploadProgress.value = 0
      error.value = null

      try {
        const uploadedImages = []

        for (let i = 0; i < files.length; i++) {
          const file = files[i]

          // Actualizar progreso
          uploadProgress.value = i / files.length

          try {
            const result = await firebaseService.uploadImage(file, props.path)
            uploadedImages.push(result)
          } catch (err) {
            console.error('Error subiendo archivo:', file.name, err)
            Notify.create({
              type: 'negative',
              message: t('common.uploadError', { filename: file.name })
            })
          }
        }

        // Agregar imágenes exitosas
        images.value.push(...uploadedImages)
        uploadProgress.value = 1

        // Emitir eventos
        emit('update:images', images.value)
        emit('upload-complete', uploadedImages)

        if (uploadedImages.length > 0) {
          Notify.create({
            type: 'positive',
            message: t('common.uploadSuccess', { count: uploadedImages.length })
          })
        }

      } catch (err) {
        error.value = t('common.uploadGeneralError')
        emit('upload-error', err)
      } finally {
        uploading.value = false
      }
    }

    const removeImage = async (index) => {
      const image = images.value[index]

      try {
        // Eliminar de Firebase Storage
        if (image.path) {
          await firebaseService.deleteImage(image.path)
        }

        // Eliminar del array
        images.value.splice(index, 1)

        // Emitir actualización
        emit('update:images', images.value)

        Notify.create({
          type: 'positive',
          message: t('common.imageDeleted')
        })

      } catch (err) {
        console.error('Error eliminando imagen:', err)
        Notify.create({
          type: 'negative',
          message: t('common.deleteError')
        })
      }
    }

    const formatFileSize = (bytes) => {
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      if (bytes === 0) return '0 Bytes'
      const i = Math.floor(Math.log(bytes) / Math.log(1024))
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
    }

    return {
      fileInput,
      uploading,
      uploadProgress,
      error,
      images,
      canAddMore,
      openFileDialog,
      handleFileSelect,
      removeImage,
      formatFileSize
    }
  }
}
</script>

<style scoped>
.image-uploader {
  width: 100%;
}

.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
}

.image-item {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
}

.image-preview {
  width: 100%;
  height: 150px;
  border-radius: 8px;
  border: 2px solid #e0e0e0;
  transition: border-color 0.3s ease;
}

.image-preview:hover {
  border-color: var(--q-primary);
}

.upload-progress {
  background: #f5f5f5;
  padding: 1rem;
  border-radius: 8px;
}

@media (max-width: 600px) {
  .images-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 0.5rem;
  }

  .image-preview {
    height: 120px;
  }
}
</style>