<template>
  <div class="notification-manager">
    <!-- Level Up Notification -->
    <LevelUpNotification
      :notification="levelUpNotification"
      :show="showLevelUp"
      @close="closeLevelUpNotification"
    />

    <!-- Achievement Notification -->
    <AchievementNotification
      :notification="achievementNotification"
      :show="showAchievement"
      @close="closeAchievementNotification"
    />

    <!-- Note Unlock Notification (similar to achievement but for unlocked notes) -->
    <AchievementNotification
      v-if="noteUnlockNotification"
      :notification="noteUnlockAsAchievement"
      :show="showNoteUnlock"
      @close="closeNoteUnlockNotification"
    />
  </div>
</template>

<script>
import { computed, watch, ref } from 'vue'
import { useLevelsStore } from '@/stores/levels'
import { useNotificationsStore } from '@/stores/notifications'
import LevelUpNotification from './LevelUpNotification.vue'
import AchievementNotification from './AchievementNotification.vue'

export default {
  name: 'NotificationManager',
  components: {
    LevelUpNotification,
    AchievementNotification
  },
  setup() {
    const levelsStore = useLevelsStore()
    const notificationsStore = useNotificationsStore()

    // Notification states
    const showLevelUp = ref(false)
    const showAchievement = ref(false)
    const showNoteUnlock = ref(false)

    // Get notifications from stores
    const levelUpNotification = computed(() => levelsStore.getLevelUpNotification)
    const achievementNotification = computed(() => levelsStore.getAchievementNotification)
    const noteUnlockNotification = computed(() => levelsStore.getNoteUnlockNotification)

    // Transform note unlock notification to achievement format
    const noteUnlockAsAchievement = computed(() => {
      if (!noteUnlockNotification.value) return null

      return {
        achievement: {
          name: 'New Notes Unlocked!',
          description: `You've unlocked ${noteUnlockNotification.value.unlockedNotes?.length || 0} new note types!`,
          icon: 'note_add',
          category: 'milestone'
        }
      }
    })

    // Watch for level up notifications
    watch(levelUpNotification, (notification) => {
      if (notification && notificationsStore.isNotificationEnabled('level_up')) {
        showLevelUp.value = true
      }
    })

    // Watch for achievement notifications
    watch(achievementNotification, (notification) => {
      if (notification && notificationsStore.isNotificationEnabled('achievement_unlocked')) {
        showAchievement.value = true
      }
    })

    // Watch for note unlock notifications
    watch(noteUnlockNotification, (notification) => {
      if (notification && notificationsStore.isNotificationEnabled('level_up')) {
        showNoteUnlock.value = true
      }
    })

    // Notification handlers
    const closeLevelUpNotification = () => {
      showLevelUp.value = false
      levelsStore.clearLevelUpNotification()
    }

    const closeAchievementNotification = () => {
      showAchievement.value = false
      levelsStore.clearAchievementNotification()
    }

    const closeNoteUnlockNotification = () => {
      showNoteUnlock.value = false
      levelsStore.clearNoteUnlockNotification()
    }

    return {
      // Notifications
      levelUpNotification,
      achievementNotification,
      noteUnlockNotification,
      noteUnlockAsAchievement,

      // Show states
      showLevelUp,
      showAchievement,
      showNoteUnlock,

      // Handlers
      closeLevelUpNotification,
      closeAchievementNotification,
      closeNoteUnlockNotification
    }
  }
}
</script>

<style scoped>
.notification-manager {
  pointer-events: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 9000;
}

.notification-manager > * {
  pointer-events: all;
}
</style>