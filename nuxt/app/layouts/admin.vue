<template>
  <main class="p-4 m-4 rounded shadow">
    <slot />
  </main>
</template>

<style scoped>
@reference "tailwindcss";

main {
  @apply bg-emerald-500;
}
</style>
