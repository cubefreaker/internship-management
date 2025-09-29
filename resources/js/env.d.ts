/// <reference types="vite/client" />

declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

// Declare NodeJS namespace for timeout types
declare namespace NodeJS {
  interface Timeout {}
}

declare module '@/components/ui/tabs' {
  import type { DefineComponent } from 'vue'
  export const Tabs: DefineComponent<{}, {}, any>
  export const TabsList: DefineComponent<{}, {}, any>
  export const TabsTrigger: DefineComponent<{}, {}, any>
  export const TabsContent: DefineComponent<{}, {}, any>
}