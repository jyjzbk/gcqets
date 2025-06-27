<!-- <script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
</script> -->

<template>
  <div id="app">
    <router-view />
  </div>
</template>

<style>
#app {
  height: 100vh;
  font-family: 'Helvetica Neue', Helvetica, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', '微软雅黑', Arial, sans-serif;
}

* {
  box-sizing: border-box;
}

body {
  margin: 0;
  padding: 0;
  background-color: #f0f2f5;
}

/* 滚动条样式 */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'

const authStore = useAuthStore()

onMounted(async () => {
  // 暂时注释掉自动获取用户信息，避免性能问题
  /*
  // 如果有token，尝试获取用户信息
  if (authStore.token) {
    try {
      await authStore.getUser()
    } catch (error) {
      console.error('Get user error:', error)
      // 如果获取用户信息失败，清除token
      authStore.logout()
    }
  }
  */
})
</script>
