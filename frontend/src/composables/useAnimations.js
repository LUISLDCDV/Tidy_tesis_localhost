import { ref, nextTick } from 'vue'
import { gsap } from 'gsap'

export function useAnimations() {
  const isAnimating = ref(false)

  // Animación de entrada para elementos de lista
  const animateListItemEntry = (element, index = 0, delay = 0.1) => {
    if (!element) return

    gsap.set(element, {
      opacity: 0,
      y: 30,
      scale: 0.95
    })

    gsap.to(element, {
      opacity: 1,
      y: 0,
      scale: 1,
      duration: 0.6,
      delay: index * delay,
      ease: "back.out(1.7)"
    })
  }

  // Animación de salida para elementos
  const animateItemExit = (element, callback) => {
    if (!element) return

    isAnimating.value = true

    gsap.to(element, {
      opacity: 0,
      scale: 0.8,
      y: -20,
      duration: 0.4,
      ease: "power2.in",
      onComplete: () => {
        isAnimating.value = false
        if (callback) callback()
      }
    })
  }

  // Animación de bounce para botones
  const bounceButton = (element) => {
    if (!element) return

    gsap.to(element, {
      scale: 0.95,
      duration: 0.1,
      ease: "power2.out",
      yoyo: true,
      repeat: 1
    })
  }

  // Animación de pulso para notificaciones
  const pulseNotification = (element) => {
    if (!element) return

    gsap.fromTo(element,
      { scale: 1 },
      {
        scale: 1.05,
        duration: 0.5,
        ease: "power2.inOut",
        yoyo: true,
        repeat: 3
      }
    )
  }

  // Animación de fade in/out
  const fadeIn = (element, duration = 0.5, delay = 0) => {
    if (!element) return

    gsap.set(element, { opacity: 0 })
    gsap.to(element, {
      opacity: 1,
      duration,
      delay,
      ease: "power2.out"
    })
  }

  const fadeOut = (element, duration = 0.3, callback) => {
    if (!element) return

    gsap.to(element, {
      opacity: 0,
      duration,
      ease: "power2.in",
      onComplete: callback
    })
  }

  // Animación de slide para elementos
  const slideInLeft = (element, duration = 0.6, delay = 0) => {
    if (!element) return

    gsap.set(element, { x: -100, opacity: 0 })
    gsap.to(element, {
      x: 0,
      opacity: 1,
      duration,
      delay,
      ease: "power3.out"
    })
  }

  const slideInRight = (element, duration = 0.6, delay = 0) => {
    if (!element) return

    gsap.set(element, { x: 100, opacity: 0 })
    gsap.to(element, {
      x: 0,
      opacity: 1,
      duration,
      delay,
      ease: "power3.out"
    })
  }

  const slideOutLeft = (element, duration = 0.4, callback) => {
    if (!element) return

    gsap.to(element, {
      x: -100,
      opacity: 0,
      duration,
      ease: "power2.in",
      onComplete: callback
    })
  }

  const slideOutRight = (element, duration = 0.4, callback) => {
    if (!element) return

    gsap.to(element, {
      x: 100,
      opacity: 0,
      duration,
      ease: "power2.in",
      onComplete: callback
    })
  }

  // Animación de carga suave
  const loadingPulse = (element) => {
    if (!element) return

    return gsap.to(element, {
      opacity: 0.5,
      duration: 1,
      ease: "power2.inOut",
      yoyo: true,
      repeat: -1
    })
  }

  // Animación de shake para errores
  const shakeError = (element) => {
    if (!element) return

    gsap.to(element, {
      x: -10,
      duration: 0.1,
      ease: "power2.inOut",
      yoyo: true,
      repeat: 5
    })
  }

  // Animación de escalado para modales
  const scaleInModal = (element, duration = 0.5) => {
    if (!element) return

    gsap.set(element, { scale: 0.8, opacity: 0 })
    gsap.to(element, {
      scale: 1,
      opacity: 1,
      duration,
      ease: "back.out(1.7)"
    })
  }

  const scaleOutModal = (element, duration = 0.3, callback) => {
    if (!element) return

    gsap.to(element, {
      scale: 0.8,
      opacity: 0,
      duration,
      ease: "power2.in",
      onComplete: callback
    })
  }

  // Animación de rotación para iconos de carga
  const rotateLoading = (element) => {
    if (!element) return

    return gsap.to(element, {
      rotation: 360,
      duration: 1,
      ease: "none",
      repeat: -1
    })
  }

  // Animación de hover para tarjetas
  const hoverCard = (element, isHovering) => {
    if (!element) return

    gsap.to(element, {
      y: isHovering ? -5 : 0,
      boxShadow: isHovering
        ? "0 10px 25px rgba(0,0,0,0.15)"
        : "0 2px 8px rgba(0,0,0,0.1)",
      duration: 0.3,
      ease: "power2.out"
    })
  }

  // Animación de progreso
  const animateProgress = (element, from = 0, to = 100, duration = 1) => {
    if (!element) return

    const obj = { value: from }

    return gsap.to(obj, {
      value: to,
      duration,
      ease: "power2.out",
      onUpdate: () => {
        element.style.width = `${obj.value}%`
      }
    })
  }

  // Animación de escritura (typewriter)
  const typewriterEffect = (element, text, speed = 50) => {
    if (!element) return

    element.textContent = ''

    return gsap.to({ length: 0 }, {
      length: text.length,
      duration: text.length / speed,
      ease: "none",
      onUpdate: function() {
        element.textContent = text.substring(0, Math.floor(this.targets()[0].length))
      }
    })
  }

  // Animación de revelar texto
  const revealText = (element, duration = 0.8, delay = 0) => {
    if (!element) return

    gsap.set(element, {
      opacity: 0,
      y: 20,
      clipPath: "inset(100% 0 0 0)"
    })

    gsap.to(element, {
      opacity: 1,
      y: 0,
      clipPath: "inset(0% 0 0 0)",
      duration,
      delay,
      ease: "power3.out"
    })
  }

  // Animación de flotación
  const floatAnimation = (element, intensity = 10, duration = 2) => {
    if (!element) return

    return gsap.to(element, {
      y: `-=${intensity}`,
      duration,
      ease: "power1.inOut",
      yoyo: true,
      repeat: -1
    })
  }

  // Animación de parpadeo
  const blinkAnimation = (element, duration = 0.5, count = 3) => {
    if (!element) return

    return gsap.to(element, {
      opacity: 0,
      duration: duration / 2,
      ease: "power2.inOut",
      yoyo: true,
      repeat: (count * 2) - 1
    })
  }

  // Animación de stagger para listas
  const staggerListItems = (elements, delay = 0.1, fromDirection = 'bottom') => {
    if (!elements || elements.length === 0) return

    const fromProps = {
      bottom: { y: 50, opacity: 0 },
      top: { y: -50, opacity: 0 },
      left: { x: -50, opacity: 0 },
      right: { x: 50, opacity: 0 }
    }

    gsap.set(elements, fromProps[fromDirection])

    return gsap.to(elements, {
      y: 0,
      x: 0,
      opacity: 1,
      duration: 0.6,
      ease: "power3.out",
      stagger: delay
    })
  }

  // Animación de confetti (partículas)
  const createConfetti = (container, count = 50) => {
    if (!container) return

    const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7']
    const tl = gsap.timeline()

    for (let i = 0; i < count; i++) {
      const confetti = document.createElement('div')
      confetti.style.cssText = `
        position: absolute;
        width: 10px;
        height: 10px;
        background-color: ${colors[Math.floor(Math.random() * colors.length)]};
        border-radius: 50%;
        pointer-events: none;
        z-index: 1000;
      `

      container.appendChild(confetti)

      tl.fromTo(confetti,
        {
          x: Math.random() * container.offsetWidth,
          y: -20,
          rotation: 0,
          scale: Math.random() * 0.5 + 0.5
        },
        {
          y: container.offsetHeight + 20,
          rotation: Math.random() * 720 - 360,
          duration: Math.random() * 2 + 2,
          ease: "power2.in",
          onComplete: () => confetti.remove()
        },
        i * 0.05
      )
    }

    return tl
  }

  return {
    isAnimating,
    animateListItemEntry,
    animateItemExit,
    bounceButton,
    pulseNotification,
    fadeIn,
    fadeOut,
    slideInLeft,
    slideInRight,
    slideOutLeft,
    slideOutRight,
    loadingPulse,
    shakeError,
    scaleInModal,
    scaleOutModal,
    rotateLoading,
    hoverCard,
    animateProgress,
    typewriterEffect,
    revealText,
    floatAnimation,
    blinkAnimation,
    staggerListItems,
    createConfetti
  }
}