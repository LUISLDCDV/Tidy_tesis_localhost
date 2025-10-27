// Compatibilidad temporal con Vuex para evitar errores de compilación
export const mapState = () => ({})
export const mapGetters = () => ({})
export const mapActions = () => ({})
export const mapMutations = () => ({})

// Store mock para evitar referencias directas
export const useStore = () => ({
  state: {},
  getters: {},
  commit: () => {},
  dispatch: () => {}
})