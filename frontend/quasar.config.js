import { configure } from 'quasar/wrappers'
import path from 'path'

export default configure(function (ctx) {
  return {
    // Boot files
    boot: [
      'capacitor'
    ],

    // Quasar plugins
    framework: {
      config: {
        // Configure Google Sans Text as default font
        brand: {
          primary: '#135E62',
          secondary: '#26A69A',
          accent: '#9C27B0',
          positive: '#21BA45',
          negative: '#C10015',
          info: '#31CCEC',
          warning: '#F2C037'
        }
      },
      plugins: [
        'Notify',
        'Dialog',
        'Loading',
        'LoadingBar',
        'LocalStorage',
        'SessionStorage'
      ]
    },

    // Build config
    build: {
      target: {
        browser: ['es2019', 'edge88', 'firefox78', 'chrome87', 'safari13.1'],
        node: 'node16'
      },

      vueRouterMode: 'history',

      extendViteConf (viteConf, { isServer, isClient }) {
        // Configure Vite here
        viteConf.resolve = viteConf.resolve || {}
        viteConf.resolve.alias = {
          ...viteConf.resolve.alias,
          '@': path.resolve('./src')
        }

        // Fix duplicate q-app div in production build
        if (ctx.prod) {
          viteConf.plugins = viteConf.plugins || []
          viteConf.plugins.push({
            name: 'remove-duplicate-qapp',
            transformIndexHtml(html) {
              // Remove the duplicate <div id=q-app> that gets added during build
              return html.replace(/<div id=q-app><\/div>/g, '')
            }
          })
        }
      },

      // Vue devtools in production
      vueDevtools: ctx.dev,
      
      // Modern build
      modern: true,
      
      // Source maps
      sourceMap: ctx.dev,
      
      // Minify
      minify: ctx.prod,
      
      // Bundle Analyzer
      bundleAnalyzer: {
        open: false
      }
    },

    // Dev server
    devServer: {
      server: {
        type: 'http'
      },
      port: 8080,
      open: false,
      host: '0.0.0.0'
    },

    // Capacitor config (for mobile)
    capacitor: {
      hideSplashscreen: true,
      iosStatusBarPadding: true
    },

    // PWA config
    pwa: {
      workboxPluginMode: 'GenerateSW',
      workboxOptions: {},
      chainWebpackCustomSW (chain) {
        // Configure service worker
      },
      manifest: {
        name: 'Tidy App',
        short_name: 'Tidy',
        description: 'Aplicación de productividad personal',
        display: 'standalone',
        orientation: 'portrait',
        background_color: '#ffffff',
        theme_color: '#164D5F',
        icons: [
          {
            src: 'icons/icon-128x128.png',
            sizes: '128x128',
            type: 'image/png'
          },
          {
            src: 'icons/icon-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'icons/icon-256x256.png',
            sizes: '256x256',
            type: 'image/png'
          },
          {
            src: 'icons/icon-384x384.png',
            sizes: '384x384',
            type: 'image/png'
          },
          {
            src: 'icons/icon-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    }
  }
})