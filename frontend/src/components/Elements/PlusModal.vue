<template>
  <div class="plus-modal-container">
    <!-- Botón flotante con Quasar -->
    <q-btn
      fab
      icon="add"
      color="primary"
      size="lg"
      class="plus-btn"
      @click="toggleModal"
      :class="{
        'plus-btn-open': isModalOpen && !$q.screen.lt.md,
        'plus-btn-hidden': (isModalOpen || otherModalOpen) && $q.screen.lt.md
      }"
      :style="{ display: ((isModalOpen || otherModalOpen) && $q.screen.lt.md) ? 'none' : 'flex' }"
    />

    <!-- Modal con Quasar Dialog -->
    <q-dialog 
      v-model="isModalOpen" 
      position="bottom"
      :maximized="$q.screen.lt.sm"
    >
      <q-card class="plus-modal-card">
        <!-- Header -->
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">
            <q-icon name="add_circle" class="q-mr-sm" />
            {{ showOnlyNotes ? $t('plusModal.noteTypes') : $t('plusModal.createNew') }}
          </div>
          <q-space />
          <q-btn icon="close" flat round dense @click="toggleModal" />
        </q-card-section>

        <q-separator />

        <!-- Contenido principal -->
        <q-card-section class="q-pa-none">
          <!-- Menú principal -->
          <div v-if="!showOnlyNotes && showmenu">
            <q-list>
              <q-item
                v-for="(item, index) in contextualMenuItems"
                :key="index"
                clickable
                v-ripple
                @click="handleItemClick(item)"
                class="menu-item"
              >
                <q-item-section avatar>
                  <q-icon :name="getItemIcon(item.type)" color="primary" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-medium">
                    {{ $t(`plusModal.menuItems.${item.type}`) }}
                  </q-item-label>
                  <!-- Mostrar límite para objetivos (solo si es premium) -->
                  <q-item-label
                    v-if="item.type === 'objective' && getObjectiveLimitText()"
                    caption
                    class="text-positive"
                  >
                    {{ getObjectiveLimitText() }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon
                    :name="item.type === 'objective' && !objectivesStore.canCreateMoreMetas ? 'block' : 'chevron_right'"
                    :color="item.type === 'objective' && !objectivesStore.canCreateMoreMetas ? 'warning' : undefined"
                  />
                </q-item-section>
              </q-item>
            </q-list>
          </div>

          <!-- Submenú para Notas -->
          <div v-if="showNoteSubmenu || (showOnlyNotes && isModalOpen)">
            <q-card-section class="q-pt-md">
              <div class="text-subtitle1 text-weight-medium q-mb-sm">
                <q-icon name="note" class="q-mr-xs" />
                {{ $t('plusModal.selectNoteType') }}
              </div>
            </q-card-section>
            
            <q-scroll-area style="height: 300px">
              <!-- Progress indicator -->
              <q-card-section class="progress-section q-pa-sm">
                <div class="text-caption text-grey-6 q-mb-xs">
                  Tipos desbloqueados: {{ unlockProgress.unlocked }}/{{ unlockProgress.total }} ({{ unlockProgress.percentage }}%)
                </div>
                <q-linear-progress
                  :value="unlockProgress.percentage / 100"
                  color="primary"
                  size="4px"
                  rounded
                />
              </q-card-section>
              
              <q-list>
                <!-- Available note types -->
                <q-item-label header class="text-positive">Disponibles</q-item-label>
                <q-item
                  v-for="noteType in availableNoteTypes"
                  :key="noteType.id"
                  clickable
                  v-ripple
                  @click="handleNoteTypeClick(noteType.id)"
                  class="note-type-item available-item"
                >
                  <q-item-section avatar>
                    <q-avatar 
                      :color="getNoteTypeColor(noteType.id - 1)" 
                      text-color="white" 
                      size="sm"
                    >
                      {{ noteType.id }}
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>
                      {{ noteType.name }}
                    </q-item-label>
                    <q-item-label caption class="text-grey-6">
                      {{ noteType.description }}
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-icon name="create" size="sm" color="positive" />
                  </q-item-section>
                </q-item>

                <!-- Locked note types -->
                <q-item-label header class="text-grey-6 q-mt-md" v-if="lockedNoteTypes.length > 0">
                  Bloqueadas (Requieren nivel más alto)
                </q-item-label>
                <q-item
                  v-for="noteType in lockedNoteTypes.slice(0, 5)"
                  :key="'locked-' + noteType.id"
                  class="note-type-item locked-item"
                  @click="showLockMessage(noteType)"
                >
                  <q-item-section avatar>
                    <q-avatar 
                      color="grey-4" 
                      text-color="grey-6" 
                      size="sm"
                    >
                      <q-icon name="lock" size="xs" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-grey-6">
                      {{ noteType.name }}
                    </q-item-label>
                    <q-item-label caption class="text-grey-5">
                      <span v-if="noteType.requiresPremium">Requiere Premium 👑</span>
                      <span v-else>Requiere nivel {{ noteType.level }}</span>
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-badge v-if="noteType.requiresPremium" color="purple" text-color="white" label="Premium" />
                    <q-badge v-else color="orange" text-color="white" :label="'Nv. ' + noteType.level" />
                  </q-item-section>
                </q-item>

                <!-- Show more locked types button -->
                <q-item 
                  v-if="lockedNoteTypes.length > 5"
                  clickable
                  class="show-more-item"
                  @click="showAllLocked = !showAllLocked"
                >
                  <q-item-section avatar>
                    <q-icon name="more_horiz" color="grey-5" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-grey-6">
                      {{ showAllLocked ? 'Mostrar menos' : `Ver ${lockedNoteTypes.length - 5} más bloqueadas` }}
                    </q-item-label>
                  </q-item-section>
                </q-item>

                <!-- Additional locked types when expanded -->
                <q-item
                  v-for="noteType in lockedNoteTypes.slice(5)"
                  :key="'locked-extra-' + noteType.id"
                  v-show="showAllLocked"
                  class="note-type-item locked-item"
                  @click="showLockMessage(noteType)"
                >
                  <q-item-section avatar>
                    <q-avatar 
                      color="grey-4" 
                      text-color="grey-6" 
                      size="sm"
                    >
                      <q-icon name="lock" size="xs" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-grey-6">
                      {{ noteType.name }}
                    </q-item-label>
                    <q-item-label caption class="text-grey-5">
                      <span v-if="noteType.requiresPremium">Requiere Premium 👑</span>
                      <span v-else>Requiere nivel {{ noteType.level }}</span>
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-badge v-if="noteType.requiresPremium" color="purple" text-color="white" label="Premium" />
                    <q-badge v-else color="orange" text-color="white" :label="'Nv. ' + noteType.level" />
                  </q-item-section>
                </q-item>
              </q-list>
            </q-scroll-area>

            <!-- Botón volver si hay submenú -->
            <q-card-section v-if="showNoteSubmenu && !showOnlyNotes" class="q-pt-none">
              <q-btn 
                flat 
                icon="arrow_back" 
                :label="$t('common.back')"
                @click="showNoteSubmenu = false"
                class="full-width"
              />
            </q-card-section>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script>
import { useLevelsStore } from '@/stores/levels';
import { useObjectivesStore } from '@/stores/objectives';
import { useAuthStore } from '@/stores/auth';
import { usePaymentsStore } from '@/stores/payments';
import noteLevelService from '@/services/noteLevelService';

export default {
  props: {
    showOnlyNotes: {
      type: Boolean,
      default: false
    }
  },
  setup() {
    const levelsStore = useLevelsStore();
    const objectivesStore = useObjectivesStore();
    const authStore = useAuthStore();
    const paymentsStore = usePaymentsStore();

    return {
      levelsStore,
      objectivesStore,
      authStore,
      paymentsStore
    };
  },
  data() {
    return {
      showmenu: true,
      isModalOpen: false,
      showNoteSubmenu: false,
      showAllLocked: false,
      otherModalOpen: false,
      menuItems: [
        { name: "Nota", type: "note" },
        { name: "Alarma", type: "alarm" },
        { name: "Evento", type: "event" },
        { name: "Objetivo", type: "objective" },
        { name: "Meta", type: "goal" },
        { name: "Calendario", type: "calendar" },
      ]
    };
  },
  computed: {
    userLevel() {
      return this.levelsStore.getCurrentLevel;
    },
    limitedNoteTypes() {
      return Array.from({ length: 16 }, (_, i) => i + 1);
    },
    availableNoteTypes() {
      const isPremium = this.paymentsStore.hasActivePremium;
      return noteLevelService.getAvailableNoteTypes(this.userLevel, isPremium);
    },
    lockedNoteTypes() {
      const isPremium = this.paymentsStore.hasActivePremium;
      return noteLevelService.getLockedNoteTypes(this.userLevel, isPremium);
    },
    unlockProgress() {
      return noteLevelService.getUnlockProgress(this.userLevel);
    },
    contextualMenuItems() {
      if (this.showOnlyNotes) {
        return this.menuItems.filter(item => item.type === 'note');
      }
      
      // Filtrar según la ruta actual
      const currentRoute = this.$route.path;
      
      if (currentRoute.includes('/Calendars')) {
        return this.menuItems.filter(item => ['event', 'calendar'].includes(item.type));
      } else if (currentRoute.includes('/Alarms')) {
        return this.menuItems.filter(item => item.type === 'alarm');
      } else if (currentRoute.includes('/Objectives')) {
        return this.menuItems.filter(item => ['objective', 'goal'].includes(item.type));
      } else if (currentRoute.includes('/Notes')) {
        return this.menuItems.filter(item => item.type === 'note');
      } else {
        // En inicio o cualquier otra ruta, mostrar todo
        return this.menuItems;
      }
    }
  },
  methods: {
    toggleModal() {
      const currentRoute = this.$route.path;

      // Si estamos en la página de objetivos, llamar directamente al método addObjective
      if (currentRoute.includes('/Objectives')) {
        this.openObjectiveModalDirectly();
        return;
      }

      // Si solo hay un item contextual y no es notes, ir directamente a crear
      if (this.contextualMenuItems.length === 1 && !this.showOnlyNotes) {
        const singleItem = this.contextualMenuItems[0];
        if (singleItem.type === 'note') {
          this.showNoteSubmenu = true;
          this.isModalOpen = true;
        } else {
          this.handleOtherItemType(singleItem);
          return;
        }
      } else {
        this.isModalOpen = !this.isModalOpen;
        this.showNoteSubmenu = false;
      }
    },
    
    handleItemClick(item) {
      if (item.type === "note") {
        this.showNoteSubmenu = true;
      } else {
        this.isModalOpen = false;
        this.handleOtherItemType(item);
      }
    },

    handleNoteTypeClick(tipoId) {
      // Verificar si el usuario puede acceder a este tipo de nota
      const validation = noteLevelService.validateNoteCreation(tipoId, this.userLevel);
      
      if (!validation.allowed) {
        this.$q.notify({
          type: 'warning',
          message: validation.message,
          position: 'top',
          timeout: 4000,
          actions: [
            {
              label: 'Ver progreso',
              color: 'white',
              handler: () => {
                this.isModalOpen = false;
                this.$router.push('/levels');
              }
            }
          ]
        });
        return;
      }

      // Si puede acceder, proceder normalmente
      this.$router.push({
        name: 'DynamicNote',
        params: { 
          type: tipoId,
          modo: 'crear'
        }
      });
      
      this.isModalOpen = false;
      this.showNoteSubmenu = false;
    },

    showLockMessage(noteType) {
      const message = noteLevelService.getAccessDeniedMessage(noteType.id, this.userLevel);
      
      this.$q.dialog({
        title: `${noteType.name} - Bloqueada`,
        message,
        cancel: {
          label: 'Cerrar',
          color: 'grey',
          flat: true
        },
        ok: {
          label: 'Ver mi progreso',
          color: 'primary'
        },
        persistent: false
      }).onOk(() => {
        this.isModalOpen = false;
        this.$router.push('/levels');
      });
    },

    handleOtherItemType(item) {
      const currentRoute = this.$route.path;

      // Si estamos en la página de calendarios y el item es 'event' o 'calendar', abrir modal directamente
      if (currentRoute.includes('/Calendars') && (item.type === 'event' || item.type === 'calendar')) {
        this.openModalInCurrentPage(item.type);
        this.isModalOpen = false;
        return;
      }

      // Para otros casos, navegar como antes
      const routeMap = {
        alarm: 'AlarmasList',
        event: 'CalendarList',
        objective: 'ObjetivesList',
        goal: 'StepsHome',
        calendar: 'CalendarList'
      };

      if (routeMap[item.type]) {
        if (item.type === 'calendar') {
          // Para calendario, agregamos un parámetro especial para abrir el modal
          this.$router.push({
            name: routeMap[item.type],
            query: { modo: 'crear', openModal: 'true' }
          });
        } else {
          this.$router.push({
            name: routeMap[item.type],
            query: { modo: 'crear' }
          });
        }
      } else {
        console.warn('Ruta no configurada para:', item.type);
      }

      this.isModalOpen = false;
    },

    openModalInCurrentPage(type) {
      console.log('openModalInCurrentPage called with type:', type);

      // Emitir eventos para que el componente padre abra el modal correspondiente
      if (type === 'event') {
        // Buscar el componente CalendarView y llamar a su método openAddEvent
        const calendarView = this.findCalendarViewComponent();
        console.log('CalendarView component found:', !!calendarView);
        if (calendarView) {
          calendarView.openAddEvent();
          console.log('Called openAddEvent on CalendarView');
        }
      } else if (type === 'calendar') {
        // Buscar el componente CalendarList y llamar a su método addCalendar
        const calendarList = this.findCalendarListComponent();
        console.log('CalendarList component found:', !!calendarList);
        if (calendarList) {
          calendarList.addCalendar();
          console.log('Called addCalendar on CalendarList');
        }
      }
    },

    findCalendarViewComponent() {
      // Buscar el componente CalendarView en el árbol de componentes
      let parent = this.$parent;
      while (parent) {
        if (parent.$options.name === 'CalendarList') {
          // Buscar CalendarView dentro de CalendarList
          const calendarView = parent.$refs.calendarView ||
                              (parent.$children && parent.$children.find(child =>
                                child.$options.name === 'CalendarView'));
          return calendarView;
        }
        parent = parent.$parent;
      }
      return null;
    },

    findCalendarListComponent() {
      // Buscar el componente CalendarList en el árbol de componentes
      let parent = this.$parent;
      while (parent) {
        if (parent.$options.name === 'CalendarList') {
          return parent;
        }
        parent = parent.$parent;
      }
      return null;
    },

    openObjectiveModalDirectly() {
      // Verificar límite de objetivos antes de crear
      if (!this.objectivesStore.canCreateMoreObjectives) {
        const limitInfo = this.objectivesStore.objectivesLimitInfo;

        this.$q.notify({
          type: 'warning',
          message: limitInfo.isPremium
            ? 'Error inesperado: Usuario premium no puede crear más objetivos'
            : `Has alcanzado el límite de ${limitInfo.max} objetivos para tu nivel ${limitInfo.level}`,
          caption: limitInfo.isPremium
            ? 'Contacta soporte'
            : 'Completa algunos objetivos o mejora a Premium para crear más',
          icon: 'warning',
          position: 'top',
          timeout: 4000,
          actions: !limitInfo.isPremium ? [
            {
              label: 'Ver Premium',
              color: 'yellow',
              handler: () => {
                // Aquí podrías redirigir a la página de premium
                console.log('Redirect to premium page');
              }
            }
          ] : []
        });

        this.toggleModal(); // Cerrar modal
        return;
      }

      // Buscar el componente MainContent y llamar a su método addObjective
      const mainContent = this.findMainContentComponent();
      if (mainContent && typeof mainContent.addObjective === 'function') {
        mainContent.addObjective();
        console.log('Called addObjective directly on MainContent');
      } else {
        console.warn('MainContent component or addObjective method not found');
        // Fallback: usar navegación con query parameter
        this.$router.push({
          name: 'ObjetivesList',
          query: { modo: 'crear' }
        });
      }
    },

    findMainContentComponent() {
      // Buscar el componente MainContent en el árbol de componentes
      let parent = this.$parent;
      while (parent) {
        if (parent.$options.name === 'MainContent') {
          return parent;
        }
        // También buscar en los hijos del componente actual
        if (parent.$children) {
          const mainContent = this.findComponentInChildren(parent.$children, 'MainContent');
          if (mainContent) return mainContent;
        }
        parent = parent.$parent;
      }
      return null;
    },

    findComponentInChildren(children, componentName) {
      for (let child of children) {
        if (child.$options.name === componentName) {
          return child;
        }
        // Búsqueda recursiva en los hijos
        if (child.$children && child.$children.length > 0) {
          const found = this.findComponentInChildren(child.$children, componentName);
          if (found) return found;
        }
      }
      return null;
    },

    getItemIcon(type) {
      const iconMap = {
        note: 'edit_note',
        alarm: 'alarm',
        event: 'event',
        objective: 'flag',
        goal: 'track_changes',
        calendar: 'calendar_today'
      };
      return iconMap[type] || 'help';
    },

    getNoteTypeColor(index) {
      const colors = [
        'primary', 'secondary', 'accent', 'positive', 'negative',
        'info', 'warning', 'purple', 'pink', 'indigo',
        'teal', 'orange', 'brown', 'blue-grey', 'deep-orange', 'light-green'
      ];
      return colors[index % colors.length];
    },

    getObjectiveLimitText() {
      const limitInfo = this.objectivesStore.objectivesLimitInfo;

      if (limitInfo.isPremium) {
        return 'Objetivos ilimitados (Premium)';
      }

      // No mostrar contadores de espacios
      return null;
    },

    // Métodos para manejar eventos globales de modales
    handleOtherModalOpened(event) {
      if (event.detail && event.detail.source !== 'plus-modal') {
        this.otherModalOpen = true;
      }
    },

    handleOtherModalClosed(event) {
      if (event.detail && event.detail.source !== 'plus-modal') {
        this.otherModalOpen = false;
      }
    }
  },
  watch: {
    showOnlyNotes(newVal) {
      if (newVal) {
        this.showmenu = false;
        this.showNoteSubmenu = true;
      } else {
        this.showmenu = true;
        this.showNoteSubmenu = false;
      }
    },
    isModalOpen(newVal) {
      // En mobile, agregar/quitar clase al body para prevenir scroll
      if (this.$q.screen.lt.md) {
        if (newVal) {
          document.body.classList.add('modal-open');
        } else {
          document.body.classList.remove('modal-open');
        }
      }
    }
  },
  mounted() {
    if (this.showOnlyNotes) {
      this.showmenu = false;
      this.showNoteSubmenu = true;
    }

    // Escuchar eventos globales de modales
    window.addEventListener('modal-opened', this.handleOtherModalOpened);
    window.addEventListener('modal-closed', this.handleOtherModalClosed);
  },
  beforeUnmount() {
    // Limpiar event listeners
    window.removeEventListener('modal-opened', this.handleOtherModalOpened);
    window.removeEventListener('modal-closed', this.handleOtherModalClosed);
  }
}
</script>

<style scoped>
.plus-modal-container {
  position: fixed;
  bottom: 80px;
  right: 24px;
  z-index: 9999;
}

.plus-btn {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.plus-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
}

.plus-btn-open {
  transform: rotate(45deg);
}

.plus-btn-hidden {
  opacity: 0;
  visibility: hidden;
  transform: scale(0);
}

.plus-modal-card {
  min-width: 300px;
  max-width: 500px;
  border-radius: 16px;
}

/* Mobile responsive */
@media (max-width: 599px) {
  .plus-modal-container {
    bottom: 80px;
    left: 50%;
    transform: translateX(-50%);
    right: auto;
  }
  
  .plus-modal-card {
    border-radius: 20px 20px 0 0;
    margin: 0;
    max-height: 75vh;
    min-height: 250px;
    width: 100vw;
    max-width: 100vw;
  }

  /* Header más compacto en mobile */
  :deep(.q-card__section--vert) {
    padding: 16px 20px 12px 20px;
  }

  /* Título más pequeño en mobile */
  .text-h6 {
    font-size: 1.1rem;
    font-weight: 600;
  }

  /* Items del menú más grandes para touch */
  .menu-item {
    margin: 6px 12px;
    border-radius: 12px;
    min-height: 56px;
  }

  .menu-item :deep(.q-item__section) {
    padding: 8px 0;
  }

  /* Iconos más grandes en mobile */
  .menu-item :deep(.q-icon) {
    font-size: 1.5rem;
  }

  /* Texto más legible */
  .menu-item :deep(.q-item__label) {
    font-size: 1rem;
    font-weight: 500;
  }

  /* Note types más compactos pero táctiles */
  .note-type-item {
    margin: 4px 12px;
    border-radius: 10px;
    min-height: 48px;
  }
  
  .available-item {
    background: rgba(76, 175, 80, 0.05);
    border-left: 3px solid #4caf50;
  }
  
  .locked-item {
    background: rgba(158, 158, 158, 0.05);
    border-left: 3px solid #bdbdbd;
    opacity: 0.7;
    cursor: pointer;
  }
  
  .progress-section {
    background: rgba(25, 118, 210, 0.05);
    border-radius: 8px;
    margin: 8px 12px;
  }
  
  .show-more-item {
    background: rgba(0, 0, 0, 0.02);
    border-radius: 6px;
    margin: 4px 12px;
  }

  /* Scroll area más suave en mobile */
  :deep(.q-scrollarea) {
    height: 280px !important;
  }

  /* Avatar de tipos de nota más grande */
  .note-type-item :deep(.q-avatar) {
    width: 32px;
    height: 32px;
    font-size: 0.9rem;
  }
}

/* Mobile pequeño */
@media (max-width: 480px) {
  .plus-modal-container {
    bottom: 75px;
    left: 50%;
    transform: translateX(-50%);
    right: auto;
  }

  .plus-modal-card {
    max-height: 80vh;
    border-radius: 16px 16px 0 0;
  }

  /* Header aún más compacto */
  :deep(.q-card__section--vert) {
    padding: 12px 16px 8px 16px;
  }

  .text-h6 {
    font-size: 1rem;
  }

  /* Menu items más compactos pero accesibles */
  .menu-item {
    margin: 4px 8px;
    min-height: 52px;
  }

  .note-type-item {
    margin: 3px 8px;
    min-height: 44px;
  }

  /* Iconos ligeramente más pequeños en pantallas muy pequeñas */
  .menu-item :deep(.q-icon) {
    font-size: 1.3rem;
  }

  /* Scroll area más pequeña */
  :deep(.q-scrollarea) {
    height: 260px !important;
  }
}

/* Desktop positioning */
@media (min-width: 600px) {
  :deep(.q-dialog__inner) {
    padding: 24px;
  }
  
  .plus-modal-card {
    position: fixed;
    bottom: 150px;
    right: 24px;
    margin: 0;
  }
}

/* Menu items styling */
.menu-item {
  border-radius: 8px;
  margin: 4px 8px;
  transition: all 0.2s ease;
}

.menu-item:hover {
  background-color: rgba(var(--q-primary), 0.1);
}

.note-type-item {
  border-radius: 8px;
  margin: 2px 8px;
  transition: all 0.2s ease;
}

.note-type-item:hover {
  background-color: rgba(var(--q-primary), 0.05);
}

/* Custom scroll area styling */
:deep(.q-scrollarea__thumb--v) {
  width: 6px;
  border-radius: 3px;
  background: rgba(0, 0, 0, 0.2);
}

:deep(.q-scrollarea__thumb--v:hover) {
  background: rgba(0, 0, 0, 0.4);
}

/* Header styling */
:deep(.q-card__section--vert) {
  padding: 16px 20px;
}

/* Animation improvements */
:deep(.q-dialog__backdrop) {
  backdrop-filter: blur(2px);
}

:deep(.q-dialog) {
  .q-card {
    animation: slideUp 0.3s ease-out;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Better FAB positioning for mobile landscape */
@media (max-height: 500px) and (orientation: landscape) {
  .plus-modal-container {
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    right: auto;
  }

  .plus-modal-card {
    max-height: 85vh;
    max-width: 400px;
    border-radius: 16px;
  }

  /* Menu items más compactos en landscape */
  .menu-item {
    min-height: 44px;
    margin: 3px 8px;
  }

  .note-type-item {
    min-height: 40px;
    margin: 2px 8px;
  }

  /* Scroll area adaptada a landscape */
  :deep(.q-scrollarea) {
    height: 200px !important;
  }
}

/* Mejoras para dispositivos táctiles */
@media (pointer: coarse) {
  /* FAB más grande para touch */
  .plus-btn {
    width: 60px;
    height: 60px;
  }

  /* Eliminar hover effects en touch devices */
  .menu-item:hover,
  .note-type-item:hover {
    background-color: transparent;
  }

  /* Feedback táctil en tap */
  .menu-item:active {
    background-color: rgba(var(--q-primary), 0.15);
    transform: scale(0.98);
  }

  .note-type-item:active {
    background-color: rgba(var(--q-primary), 0.1);
    transform: scale(0.98);
  }

  /* Botón de cerrar más grande */
  :deep(.q-btn[icon="close"]) {
    width: 44px;
    height: 44px;
  }
}

/* Dark mode mobile optimizations */
@media (max-width: 599px) {
  .body--dark .plus-modal-card {
    background: #1e1e1e;
    border-top: 1px solid #333;
  }

  .body--dark .menu-item {
    border: 1px solid #333;
  }

  .body--dark .note-type-item {
    border: 1px solid #2a2a2a;
  }
}

/* Accessibility improvements for mobile */
@media (max-width: 599px) {
  /* Asegurar contraste adecuado */
  .menu-item :deep(.q-item__label) {
    color: #1a1a1a;
  }

  .body--dark .menu-item :deep(.q-item__label) {
    color: #e0e0e0;
  }

  /* Focus visible en mobile */
  .menu-item:focus-visible,
  .note-type-item:focus-visible {
    outline: 2px solid var(--q-primary);
    outline-offset: 2px;
  }
}

/* Modal open state in mobile - global styles */
:deep(.modal-open) {
  overflow: hidden;
}

/* Button animation improvements */
.plus-btn {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.plus-btn-hidden {
  transition: all 0.2s ease-in-out;
}
</style>