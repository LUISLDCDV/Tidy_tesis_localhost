const MASK = 0b10101010; // Máscara de 8 bits (puedes cambiarla)

export const obfuscateId = (id) => {
  return (Number(id) ^ MASK).toString(16).padStart(2, '0');
};

export const deobfuscateId = (obfuscated) => {
  try {
    const numericValue = parseInt(obfuscated, 16);
    return (numericValue ^ MASK).toString();
  } catch (e) {
    return null;
  }
};