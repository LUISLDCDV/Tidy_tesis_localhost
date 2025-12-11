// services/useConfirm.js

let resolverFunction = null;

export function useConfirm(message = '¿Estás seguro?') {
  return new Promise((resolve) => {
    resolverFunction = resolve;

    // Disparar evento global para abrir el modal
    const event = new CustomEvent('show-confirm-modal', {
      detail: { message, resolverFunction }
    });
    window.dispatchEvent(event);
  });
}

export function confirmYes() {
  if (resolverFunction) {
    resolverFunction(true);
    resolverFunction = null;
  }
}

export function confirmNo() {
  if (resolverFunction) {
    resolverFunction(false);
    resolverFunction = null;
  }
}

// TODO: IMPLEMENTAR EL MODAL EN TODOS LADOS COMO EN NOTAS CLAVES