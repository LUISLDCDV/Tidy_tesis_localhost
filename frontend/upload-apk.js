#!/usr/bin/env node

/**
 * Script para subir el APK de la aplicación a Firebase Storage
 * Uso: node upload-apk.js [ruta-al-apk]
 */

const admin = require('firebase-admin');
const fs = require('fs');
const path = require('path');

// Inicializar Firebase Admin con las credenciales del archivo de servicio
const serviceAccount = require('./serviceAccountKey.json');

admin.initializeApp({
  credential: admin.credential.cert(serviceAccount),
  storageBucket: 'tidy-1d736.appspot.com'
});

const bucket = admin.storage().bucket();

async function uploadAPK(apkPath) {
  try {
    // Validar que el archivo existe
    if (!fs.existsSync(apkPath)) {
      console.error('❌ Error: El archivo APK no existe:', apkPath);
      process.exit(1);
    }

    // Obtener información del archivo
    const fileName = path.basename(apkPath);
    const fileStats = fs.statSync(apkPath);
    const fileSizeInMB = (fileStats.size / (1024 * 1024)).toFixed(2);

    console.log(`📦 Subiendo APK: ${fileName}`);
    console.log(`📊 Tamaño: ${fileSizeInMB} MB`);

    // Nombre del archivo en Firebase Storage
    const destination = `apk/tidy-app-latest.apk`;

    // Subir el archivo
    await bucket.upload(apkPath, {
      destination: destination,
      metadata: {
        contentType: 'application/vnd.android.package-archive',
        metadata: {
          uploadedAt: new Date().toISOString(),
          originalName: fileName,
          size: fileStats.size
        }
      },
      public: true // Hacer el archivo público
    });

    // Obtener la URL pública
    const file = bucket.file(destination);
    const [url] = await file.getSignedUrl({
      action: 'read',
      expires: '03-01-2500' // Fecha muy lejana para que sea permanente
    });

    // También podemos usar la URL pública directa
    const publicUrl = `https://storage.googleapis.com/${bucket.name}/${destination}`;

    console.log('✅ APK subido exitosamente!');
    console.log('📍 Ubicación:', destination);
    console.log('🔗 URL pública:', publicUrl);
    console.log('');
    console.log('💡 Puedes usar esta URL en tu aplicación para la descarga del APK');

    // Guardar la URL en un archivo de configuración
    const configPath = path.join(__dirname, 'apk-url.json');
    fs.writeFileSync(configPath, JSON.stringify({
      url: publicUrl,
      uploadedAt: new Date().toISOString(),
      fileName: fileName,
      size: fileStats.size
    }, null, 2));

    console.log('📝 URL guardada en:', configPath);

  } catch (error) {
    console.error('❌ Error al subir el APK:', error);
    process.exit(1);
  }
}

// Obtener la ruta del APK desde los argumentos de línea de comandos
const apkPath = process.argv[2] || './src-capacitor/android/app/build/outputs/apk/release/app-release.apk';

console.log('🚀 Iniciando subida de APK a Firebase Storage...\n');
uploadAPK(apkPath)
  .then(() => {
    console.log('\n✨ Proceso completado!');
    process.exit(0);
  })
  .catch(error => {
    console.error('\n❌ Error fatal:', error);
    process.exit(1);
  });
