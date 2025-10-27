// src/utils/eventosFormat.js
export const formatEvent = (evento) => {
  // Validamos que existan ambos campos
  if (!evento.fechaVencimiento || !evento.horaVencimiento) {
    console.warn('Evento sin fecha o hora válida:', evento);
    return null;
  }

  const startISO = `${evento.fechaVencimiento}T${evento.horaVencimiento}`;

  // Opcional: Ajustar zona horaria si es necesario (ej: UTC-3)
  const start = new Date(startISO);

  // Añadimos una hora más para end (ejemplo)
  const end = new Date(start.getTime() + 60 * 60 * 1000); // +1 hora

  return {
    title: evento.nombre,
    content: evento.informacion || '',
    start,
    end,
    color: evento.color || '#4caf50',
    id: evento.id,
    calendario_id: evento.calendario_id,
    elemento_id: evento.elemento_id,
    tipo: evento.tipo,
    gps: evento.gps,
    clima: evento.clima,
  };
};

export const formatEvents = (eventos) => {
  return eventos
    .map(event => formatEvent(event))
    .filter(Boolean); // Filtramos los eventos inválidos
};