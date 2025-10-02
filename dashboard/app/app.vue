<template>
  <header>
    <nav>
      <ul>
        <li><NuxtLink to="/">แดชบอร์ด</NuxtLink></li>
        <li><NuxtLink to="/admin">สำหรับผู้ดูแลระบบ</NuxtLink></li>
        <li><NuxtLink to="/admin/upload">อัพโหลด</NuxtLink></li>
      </ul>
    </nav>
  </header>

  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<style>
.layout-enter-active,
.layout-leave-active {
  transition: all 0.4s;
}
.layout-enter-from,
.layout-leave-to {
  filter: grayscale(1);
}
</style>
