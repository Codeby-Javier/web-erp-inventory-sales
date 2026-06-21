<template>
  <div>
    <!-- Modal Backdrop -->
    <div v-if="show" class="modal-backdrop fade show" style="z-index: 1040;"></div>
    
    <!-- Modal Dialog -->
    <div v-if="show" class="modal fade show d-block" tabindex="-1" style="z-index: 1050; display: block;" @click.self="close">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
          <div class="modal-header border-bottom-0 pb-0">
            <h5 class="modal-title fw-bold">{{ isEdit ? 'Edit Data' : 'Tambah Data' }}</h5>
            <button type="button" class="btn-close" @click="close" aria-label="Close"></button>
          </div>
          
          <form @submit.prevent="submitForm">
            <div class="modal-body">
              <div v-if="errorMsg" class="alert alert-danger">{{ errorMsg }}</div>
              
              <div class="mb-3" v-for="field in fields" :key="field.key">
                <label class="form-label fw-medium">{{ field.label }} <span v-if="field.required" class="text-danger">*</span></label>
                
                <input v-if="field.type === 'text'" type="text" class="form-control" v-model="formData[field.key]" :required="field.required">
                <input v-else-if="field.type === 'number'" type="number" class="form-control" v-model="formData[field.key]" :required="field.required">
                <textarea v-else-if="field.type === 'textarea'" class="form-control" rows="3" v-model="formData[field.key]" :required="field.required"></textarea>
                
                <select v-else-if="field.type === 'select'" class="form-select" v-model="formData[field.key]" :required="field.required">
                  <option value="" disabled>-- Pilih --</option>
                  <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>

                <div v-else-if="field.type === 'boolean'" class="form-check form-switch mt-2">
                  <input class="form-check-input" type="checkbox" v-model="formData[field.key]">
                  <span class="form-check-label">Aktif</span>
                </div>
              </div>
            </div>
            
            <div class="modal-footer border-top-0 pt-0">
              <button type="button" class="btn btn-link link-secondary" @click="close">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Simpan Data
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '../utils/api'

const props = defineProps({
  show: Boolean,
  isEdit: Boolean,
  table: String,
  fields: Array,
  initialData: Object
})

const emit = defineEmits(['close', 'saved'])

const formData = ref({})
const loading = ref(false)
const errorMsg = ref('')

watch(() => props.show, (newVal) => {
  if (newVal) {
    errorMsg.value = ''
    formData.value = {}
    
    // Setup form data
    props.fields.forEach(f => {
      formData.value[f.key] = f.type === 'boolean' ? true : ''
    })
    
    if (props.isEdit && props.initialData) {
      Object.keys(props.initialData).forEach(k => {
        if (formData.value[k] !== undefined || k === 'id') {
          formData.value[k] = props.initialData[k]
        }
      })
    }
  }
})

const close = () => {
  emit('close')
}

const submitForm = async () => {
  loading.value = true
  errorMsg.value = ''
  
  try {
    const payload = { ...formData.value }
    // Convert boolean to 1/0 for MySQL
    props.fields.forEach(f => {
      if (f.type === 'boolean') {
        payload[f.key] = payload[f.key] ? 1 : 0
      }
    })
    
    if (props.isEdit) {
      const id = payload.id
      delete payload.id
      delete payload.created_at
      delete payload.updated_at
      
      await api.put(`/crud/${props.table}/${id}`, payload)
    } else {
      await api.post(`/crud/${props.table}`, payload)
    }
    
    emit('saved')
    close()
  } catch (err) {
    errorMsg.value = err.response?.data?.message || err.message || 'Gagal menyimpan data.'
  } finally {
    loading.value = false
  }
}
</script>
