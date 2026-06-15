<template>
  <AppBar />
  <HeroSection />
  <FeaturesSection />
  <AppFooter />
  <ChatWidget />
</template>

<script setup>
import { provide, ref } from 'vue'
import AppBar from './components/AppBar.vue'
import HeroSection from './components/HeroSection.vue'
import FeaturesSection from './components/FeaturesSection.vue'
import AppFooter from './components/AppFooter.vue'
import ChatWidget from './components/ChatWidget.vue'

const lang = ref('nl')
provide('lang', lang)
</script>