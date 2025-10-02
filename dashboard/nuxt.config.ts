import tailwindcss from '@tailwindcss/vite'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: true,
  app: {
    baseURL: '/dashboard',
    head: {
      title:
        'Officer Dashboard | Legal Affairs Division - Office of the Permanent Secretary for Ministry of Public Health',
      meta: [
        {
          name: 'description',
          content:
            'แดชบอร์ดเจ้าหน้าที่ - กองกฎหมาย สำนักงานปลัดกระทรวงสาธารณสุข',
        },
      ],
    },
    layoutTransition: { name: 'layout', mode: 'out-in' },
  },
  router: {
    options: { hashMode: true },
  },
  routeRules: {
    '/': { prerender: true },
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxt/image', '@nuxt/ui'],
  css: ['~/assets/styles.css'],
  vite: {
    plugins: [tailwindcss()],
  },
  plugins: [
    // 'dayjs'
  ],
})
