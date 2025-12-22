import Uppy, { BasePlugin } from '@uppy/core'
import Dashboard from '@uppy/dashboard'

import '@uppy/core/css/style.min.css'
import '@uppy/dashboard/css/style.min.css'

class ChunkedUploadPlugin extends BasePlugin {
  constructor (uppy, opts) {
    super(uppy, opts)
    this.id = 'ChunkedUpload'
    this.type = 'uploader'

    this.opts = {
      endpoint: '/admin/ajax/upload/chunk',
      chunkSize: 5 * 1024 * 1024, // 5MB
      fieldName: 'file',
      ...opts,
    }

    this.uploadFiles = this.uploadFiles.bind(this)
    this.uploadOne = this.uploadOne.bind(this)
  }

  install () {
    this.uppy.addUploader(this.uploadFiles)
  }

  uninstall () {
  }

  async uploadFiles (fileIDs) {
    return Promise.all(fileIDs.map((id) => this.uploadOne(id)))
  }

  async uploadOne (fileID) {
    const file = this.uppy.getFile(fileID)
    const { chunkSize, endpoint, fieldName } = this.opts
    const uploadSessionId = `upload-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`
    const totalChunks = Math.ceil(file.size / chunkSize)
    const startedAt = Date.now()
    let uploadedBytes = 0
    let finalResult = null

    this.uppy.setFileState(file.id, {
      progress: {
        uploadStarted: startedAt,
        uploadComplete: false,
        percentage: 0,
        bytesUploaded: 0,
        bytesTotal: file.size,
      },
    })

    try {
      for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
        const start = chunkIndex * chunkSize
        const end = Math.min(start + chunkSize, file.size)
        const chunk = file.data.slice(start, end)

        const formData = new FormData()
        formData.append(fieldName, chunk, file.name)
        formData.append('fileId', uploadSessionId)
        formData.append('chunkIndex', chunkIndex.toString())
        formData.append('totalChunks', totalChunks.toString())
        formData.append('filename', file.name)

        const response = await fetch(endpoint, {
          method: 'POST',
          body: formData,
        })

        if (!response.ok) {
          throw new Error(`Upload failed: ${response.status} ${response.statusText}`)
        }

        const result = await response.json()
        if (result.error) {
          throw new Error(result.error)
        }

        uploadedBytes += (end - start)
        const percentage = Math.round((uploadedBytes / file.size) * 100)

        this.uppy.setFileState(file.id, {
          progress: {
            uploadStarted: startedAt,
            uploadComplete: false,
            percentage: percentage,
            bytesUploaded: uploadedBytes,
            bytesTotal: file.size,
          },
        })

        this.uppy.emit('upload-progress', file, {
          uploader: this,
          bytesUploaded: uploadedBytes,
          bytesTotal: file.size,
        })

        if (result.completed) {
          finalResult = result
        }
      }

      const successResponse = {
        status: 200,
        body: finalResult || { success: true },
        uploadURL: finalResult?.path,
      }

      this.uppy.setFileState(file.id, {
        progress: {
          uploadStarted: startedAt,
          uploadComplete: true,
          percentage: 100,
          bytesUploaded: file.size,
          bytesTotal: file.size,
        },
        response: successResponse,
      })

      this.uppy.emit('upload-success', file, successResponse)

      return successResponse

    } catch (error) {
      this.uppy.emit('upload-error', file, {
        uploader: this,
        error: error,
      })
      throw error
    }
  }
}

const initializeUppyForFileInput = (fileInput) => {
  if (!fileInput || fileInput.type !== 'file' || fileInput.dataset.uppyInitialized === 'true') {
    return
  }

  const fieldContainer = fileInput.closest('.field') || fileInput.parentNode
  const uppyId = `uppy-${fileInput.id || Math.random().toString(36).substring(2, 9)}`

  const uploadEndpointElement = document.querySelector('[data-uppy-chunked-upload-path]')
  if (!uploadEndpointElement) {
    throw new Error('Upload endpoint not found. Element with [data-uppy-chunked-upload-path] attribute is required.')
  }

  const uploadEndpoint = uploadEndpointElement.dataset.uppyChunkedUploadPath
  if (!uploadEndpoint) {
    throw new Error('Upload endpoint is empty. data-uppy-chunked-upload-path attribute must have a value.')
  }

  const chunkSizeElement = document.querySelector('[data-uppy-chunk-size]')
  if (!chunkSizeElement) {
    throw new Error('Chunk size not found. Element with [data-uppy-chunk-size] attribute is required.')
  }

  const chunkSize = parseInt(chunkSizeElement.dataset.uppyChunkSize)
  if (isNaN(chunkSize) || chunkSize <= 0) {
    throw new Error('Invalid chunk size. data-uppy-chunk-size attribute must be a positive integer.')
  }

  const uppy = new Uppy({
    id: uppyId,
    autoProceed: true,
    restrictions: {
      maxNumberOfFiles: 1,
      maxFileSize: 500 * 1024 * 1024,
    },
  })

  uppy.use(Dashboard, {
    target: fieldContainer,
    inline: true,
    height: 180,
    hideUploadButton: true,
    showProgressDetails: true,
    proudlyDisplayPoweredByUppy: false,
    locale: {
      strings: {
        dropPasteFiles: 'Drop file here or %{browseFiles}',
      }
    }
  })

  uppy.use(ChunkedUploadPlugin, {
    endpoint: uploadEndpoint,
    chunkSize: chunkSize,
  })

  uppy.on('upload-success', (file, response) => {
    if (response.body && response.body.fileId) {
      const configurationContainer = fileInput.closest('[data-form-collection="item"]') || fileInput.closest('.field')

      if (configurationContainer) {
        const chunkFileIdInput = configurationContainer.querySelector('input[name$="[chunkFileId]"]')
        const chunkFilenameInput = configurationContainer.querySelector('input[name$="[chunkOriginalFilename]"]')

        if (chunkFileIdInput) {
          chunkFileIdInput.value = response.body.fileId
        }
        if (chunkFilenameInput) {
          chunkFilenameInput.value = response.body.originalFilename
        }

        fileInput.value = ''
      }
    }
  })

  fileInput.style.display = 'none'
  fileInput.dataset.uppyInitialized = 'true'
}

document.addEventListener('DOMContentLoaded', () => {
  const selector = 'input[type="file"][data-test-uploaded-file]'

  document.querySelectorAll(selector).forEach(initializeUppyForFileInput)

  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      mutation.addedNodes.forEach((node) => {
        if (node.nodeType === Node.ELEMENT_NODE) {
          if (node.matches(selector)) {
            initializeUppyForFileInput(node)
          } else {
            node.querySelectorAll(selector).forEach(initializeUppyForFileInput)
          }
        }
      })
    })
  })

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  })
})
