<template>
  <q-page class="q-pa-md ">
    <div class="row justify-center">
      <div class="col-12 col-lg-8">
        <!-- Breadcrumbs -->
        <q-breadcrumbs class="q-mb-md">
          <q-breadcrumbs-el label="Templates" />
          <q-breadcrumbs-el label="Travel" />
        </q-breadcrumbs>

        <!-- Form Fields -->
        <q-card flat bordered>
          <q-card-section>
            <div class="q-gutter-lg">
              <!-- Nombre del viaje -->
              <q-input
                v-model="formData.tripName"
                :label="$t('notes.trip.tripName')"
                :placeholder="$t('notes.trip.tripNamePlaceholder')"
                outlined
                @update:model-value="saveChanges"
              />

              <!-- Destino -->
              <q-input
                v-model="formData.destination"
                :label="$t('notes.trip.destination')"
                :placeholder="$t('notes.trip.destinationPlaceholder')"
                outlined
                @update:model-value="saveChanges"
              />

              <!-- Fechas -->
              <q-input
                v-model="formData.dates"
                :label="$t('notes.trip.startDate')"
                :placeholder="$t('notes.trip.startDate')"
                outlined
                @update:model-value="saveChanges"
              />

              <!-- Lugares por visitar -->
              <div>
                <div class="row justify-between items-center q-mb-sm">
                  <div class="text-subtitle1 text-weight-medium">{{ $t('notes.trip.activities') }}</div>
                  <q-btn
                    @click="openPlaceModal"
                    color="positive"
                    :label="$t('notes.trip.addActivity')"
                    icon="add"
                    size="sm"
                  />
                </div>

                <!-- Lista de lugares -->
                <div class="q-gutter-xs">
                  <q-chip
                    v-for="(place, index) in formData.places"
                    :key="index"
                    removable
                    @remove="removePlace(index)"
                    color="positive"
                    text-color="white"
                  >
                    {{ place.name }}
                    <q-btn
                      @click="openInGoogleMaps(place)"
                      icon="place"
                      flat
                      round
                      dense
                      size="xs"
                      color="primary"
                      class="q-ml-xs"
                    />
                  </q-chip>
                </div>
              </div>

              <!-- Actividades -->
              <q-input
                v-model="formData.activities"
                :label="$t('notes.trip.activities')"
                :placeholder="$t('notes.trip.activityNotesPlaceholder')"
                type="textarea"
                rows="4"
                outlined
                @update:model-value="saveChanges"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Modal para agregar/editar lugar -->
    <q-dialog v-model="showPlaceModal" persistent>
      <q-card style="min-width: 500px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ $t('notes.trip.addActivity') }}</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="closePlaceModal" />
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-md">
            <q-input
              v-model="newPlace.mapUrl"
              :label="$t('notes.trip.activityLocation')"
              :placeholder="$t('notes.trip.activityLocationPlaceholder')"
              outlined
              @input="parseMapUrl"
            />

            <q-input
              v-model="newPlace.name"
              :label="$t('notes.trip.activityName')"
              :placeholder="$t('notes.trip.activityNamePlaceholder')"
              outlined
            />

            <q-input
              v-model="newPlace.address"
              :label="$t('notes.trip.activityNotes')"
              :placeholder="$t('notes.trip.activityNotesPlaceholder')"
              outlined
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="q-gutter-sm">
          <q-btn
            @click="closePlaceModal"
            :label="$t('notes.trip.cancel')"
            color="grey"
            outline
          />
          <q-btn
            @click="savePlace"
            :label="$t('notes.trip.save')"
            color="positive"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
export default {
  name: 'TempleTrip',
  props: {
    content: {
      type: String,
      default: '{}'
    }
  },
  emits: ['update:content'],
  data() {
    return {
      formData: {
        tripName: '',
        destination: '',
        dates: '',
        places: [],
        activities: ''
      },
      showPlaceModal: false,
      newPlace: {
        mapUrl: '',
        name: '',
        address: ''
      }
    };
  },
  watch: {
    content: {
      immediate: true,
      handler(newContent) {
        try {
          const parsed = newContent ? JSON.parse(newContent) : {};
          if (parsed.places && Array.isArray(parsed.places)) {
            this.formData.places = [...parsed.places];
          }
          this.formData.tripName = parsed.tripName || '';
          this.formData.destination = parsed.destination || '';
          this.formData.dates = parsed.dates || '';
          this.formData.activities = parsed.activities || '';
        } catch (e) {
          console.error('Error parsing content:', e);
        }
      }
    }
  },
  methods: {
    // Abrir modal para agregar lugar
    openPlaceModal() {
      this.newPlace = { mapUrl: '', name: '', address: '' };
      this.showPlaceModal = true;
    },

    // Cerrar modal
    closePlaceModal() {
      this.showPlaceModal = false;
    },

    // Parsear URL de Google Maps
    parseMapUrl() {
      const url = this.newPlace.mapUrl.trim();
      if (!url.includes('google.com/maps')) {
        return;
      }

      // Extraer nombre del lugar
      const nameMatch = url.match(/\/place\/(.*?)\//);
      let name = null;

      if (nameMatch) {
        name = decodeURIComponent(nameMatch[1].replace(/\+/g, ' '));
      }

      // Extraer coordenadas
      const coordsMatch = url.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
      const lat = coordsMatch ? parseFloat(coordsMatch[1]) : null;
      const lng = coordsMatch ? parseFloat(coordsMatch[2]) : null;

      // Actualizar nombre si se encontró desde la URL
      if (name) {
        this.newPlace.name = name;
      }

      // Guardar lat/lng si las hay
      this.newPlace.lat = lat;
      this.newPlace.lng = lng;
    },

    // Guardar lugar en la lista
    savePlace() {
      if (!this.newPlace.name) {
        this.$q.notify({
          type: 'negative',
          message: 'El lugar debe tener un nombre',
          position: 'top'
        });
        return;
      }

      const placeToAdd = {
        name: this.newPlace.name,
        address: this.newPlace.address,
        lat: this.newPlace.lat,
        lng: this.newPlace.lng,
        mapUrl: this.newPlace.mapUrl || null
      };

      this.formData.places.push(placeToAdd);
      this.saveChanges();
      this.closePlaceModal();
    },

    // Eliminar lugar
    removePlace(index) {
      this.formData.places.splice(index, 1);
      this.saveChanges();
    },

    // Abrir lugar en Google Maps
    openInGoogleMaps(place) {
      let query;

      if (place.mapUrl) {
        window.open(place.mapUrl, '_blank');
      } else if (place.lat && place.lng) {
        query = `${place.lat},${place.lng}`;
      } else {
        query = encodeURIComponent(`${place.name}, ${this.formData.destination}`);
      }

      if (query) {
        window.open(`https://www.google.com/maps/search/?api=1&query=${query}`, '_blank');
      }
    },

    // Guardar cambios y emitir evento
    saveChanges() {
      const newData = JSON.stringify(this.formData);
      this.$emit('update:content', newData);
    }
  }
};
</script>

<style scoped>
/* Quasar components already provide styling */
</style>