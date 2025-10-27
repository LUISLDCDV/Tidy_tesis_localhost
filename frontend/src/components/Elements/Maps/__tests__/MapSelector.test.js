import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import MapSelector from '../MapSelector.vue'

// Mock de Leaflet
const mockMap = {
  setView: vi.fn(),
  on: vi.fn(),
  remove: vi.fn()
}

const mockMarker = {
  setLatLng: vi.fn(),
  on: vi.fn()
}

const mockLayer = {
  addTo: vi.fn()
}

global.L = {
  map: vi.fn(() => mockMap),
  marker: vi.fn(() => mockMarker),
  tileLayer: vi.fn(() => mockLayer)
}

// Mock de fetch para geocoding
global.fetch = vi.fn()

describe('MapSelector.vue', () => {
  let wrapper

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()

    // Mock successful fetch responses
    global.fetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve([
        {
          lat: '-34.6037',
          lon: '-58.3816',
          display_name: 'Buenos Aires, Argentina'
        }
      ])
    })

    wrapper = mount(MapSelector, {
      props: {
        modelValue: true,
        initialLocation: { lat: -34.6037, lng: -58.3816 }
      }
    })
  })

  afterEach(() => {
    wrapper.unmount()
  })

  it('renderiza correctamente cuando está abierto', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('.q-dialog').exists()).toBe(true)
  })

  it('no renderiza cuando modelValue es false', async () => {
    await wrapper.setProps({ modelValue: false })
    expect(wrapper.find('.q-dialog').isVisible()).toBe(false)
  })

  it('muestra el título correcto', () => {
    const title = wrapper.find('.text-h6')
    expect(title.text()).toBe('maps.selectLocation')
  })

  it('inicializa el mapa correctamente', async () => {
    // Simular que Leaflet se ha cargado
    await wrapper.vm.loadLeaflet()

    expect(wrapper.vm.leafletLoaded).toBe(true)
  })

  it('maneja la búsqueda de ubicaciones', async () => {
    wrapper.vm.searchQuery = 'Buenos Aires'

    await wrapper.vm.searchLocation()

    expect(global.fetch).toHaveBeenCalledWith(
      expect.stringContaining('nominatim.openstreetmap.org/search')
    )
  })

  it('establece la ubicación correctamente', () => {
    const lat = -34.6037
    const lng = -58.3816
    const address = 'Buenos Aires, Argentina'

    wrapper.vm.setLocation(lat, lng, address)

    expect(wrapper.vm.selectedLocation).toEqual({
      lat,
      lng,
      address
    })
  })

  it('emite location-selected cuando se confirma la selección', async () => {
    wrapper.vm.selectedLocation = {
      lat: -34.6037,
      lng: -58.3816,
      address: 'Buenos Aires, Argentina'
    }

    await wrapper.vm.confirmSelection()

    expect(wrapper.emitted('location-selected')).toBeTruthy()
    expect(wrapper.emitted('location-selected')[0][0]).toEqual({
      lat: -34.6037,
      lng: -58.3816,
      address: 'Buenos Aires, Argentina',
      coordinates: '-34.603700, -58.381600'
    })
  })

  it('emite update:modelValue cuando se cierra', async () => {
    await wrapper.vm.closeModal()

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0][0]).toBe(false)
  })

  it('maneja errores de geocodificación', async () => {
    global.fetch.mockResolvedValueOnce({
      ok: false,
      status: 404
    })

    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    await wrapper.vm.searchLocation()

    expect(consoleSpy).toHaveBeenCalled()
    consoleSpy.mockRestore()
  })

  it('realiza geocodificación inversa', async () => {
    global.fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({
        display_name: 'Buenos Aires, Argentina'
      })
    })

    await wrapper.vm.reverseGeocode(-34.6037, -58.3816)

    expect(global.fetch).toHaveBeenCalledWith(
      expect.stringContaining('nominatim.openstreetmap.org/reverse')
    )
  })

  it('obtiene la ubicación actual del usuario', async () => {
    const mockPosition = {
      coords: {
        latitude: -34.6037,
        longitude: -58.3816
      }
    }

    // Mock de getCurrentPosition
    navigator.geolocation.getCurrentPosition.mockImplementation((success) => {
      success(mockPosition)
    })

    await wrapper.vm.getCurrentLocation()

    expect(navigator.geolocation.getCurrentPosition).toHaveBeenCalled()
  })

  it('maneja errores de geolocalización', async () => {
    const error = new Error('Geolocation error')
    navigator.geolocation.getCurrentPosition.mockImplementation((success, error_callback) => {
      error_callback(error)
    })

    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})

    await wrapper.vm.getCurrentLocation()

    expect(consoleSpy).toHaveBeenCalled()
    consoleSpy.mockRestore()
  })

  it('limpia el estado al cerrar', async () => {
    wrapper.vm.searchQuery = 'test'
    wrapper.vm.selectedLocation = { lat: 1, lng: 2 }

    await wrapper.vm.closeModal()

    expect(wrapper.vm.searchQuery).toBe('')
    expect(wrapper.vm.selectedLocation).toBeNull()
  })

  it('deshabilita el botón de selección cuando no hay ubicación', () => {
    wrapper.vm.selectedLocation = null

    const selectButton = wrapper.find('[data-testid="select-button"]')
    // Buscar por texto si no hay data-testid
    const buttons = wrapper.findAll('button')
    const selectBtn = buttons.find(btn => btn.text().includes('common.select'))

    if (selectBtn) {
      expect(selectBtn.attributes('disabled')).toBeDefined()
    }
  })

  it('carga las librerías de Leaflet dinámicamente', async () => {
    const createElement = vi.spyOn(document, 'createElement')

    await wrapper.vm.loadLeaflet()

    expect(createElement).toHaveBeenCalledWith('link')
    expect(createElement).toHaveBeenCalledWith('script')
  })

  it('ajusta la altura del mapa según el dispositivo', () => {
    expect(wrapper.vm.mapHeight).toBeDefined()
    expect(typeof wrapper.vm.mapHeight).toBe('string')
  })
})