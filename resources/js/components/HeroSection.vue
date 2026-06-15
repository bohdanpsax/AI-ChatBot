<template>
  <section class="hero">
    <div class="hero__overlay" />
    <div class="hero__content">
      <p class="hero__label">{{ t.label }}</p>
      <h1 class="hero__title">{{ t.title }}</h1>
      <p class="hero__sub">{{ t.sub }}</p>
      <div class="hero__actions">
        <button class="ds-btn ds-btn--flat">
          <i class="mdi mdi-calendar-check" />
          {{ t.btn1 }}
        </button>
        <button class="ds-btn ds-btn--outlined hero__btn-outline">
          {{ t.btn2 }}
        </button>
      </div>
    </div>
  </section>
</template>

<script setup>
import { inject, computed } from 'vue'

const lang = inject('lang')

const translations = {
  nl: {
    label: 'Welkom bij',
    title: 'De Groene Weide',
    sub: 'Rust, natuur en comfort in het hart van de Veluwe',
    btn1: 'Reserveer nu',
    btn2: 'Bekijk het park',
  },
  en: {
    label: 'Welcome to',
    title: 'De Groene Weide',
    sub: 'Peace, nature and comfort in the heart of the Veluwe',
    btn1: 'Book now',
    btn2: 'Explore the park',
  },
  de: {
    label: 'Willkommen bei',
    title: 'De Groene Weide',
    sub: 'Ruhe, Natur und Komfort im Herzen der Veluwe',
    btn1: 'Jetzt buchen',
    btn2: 'Den Park entdecken',
  },
}

const t = computed(() => translations[lang.value])
</script>

<style scoped>
.hero {
  position: relative;
  height: 520px;
  background: linear-gradient(135deg, #00355f 0%, #a25293 100%);
  display: flex;
  align-items: center;
  overflow: hidden;
}

.hero__overlay {
  position: absolute;
  inset: 0;
  background: url('https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?w=1400&q=80') center/cover no-repeat;
  opacity: 0.25;
}

.hero__content {
  position: relative;
  z-index: 1;
  padding: 0 var(--space-8);
  max-width: 640px;
}

.hero__label {
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  color: rgba(255,255,255,0.75);
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin: 0 0 var(--space-2);
}

.hero__title {
  font-size: 3rem;
  font-weight: var(--weight-bold);
  color: #fff;
  margin: 0 0 var(--space-3);
  line-height: 1.1;
  letter-spacing: -0.02em;
}

.hero__sub {
  font-size: var(--text-lg);
  color: rgba(255,255,255,0.85);
  margin: 0 0 var(--space-8);
  line-height: 1.5;
}

.hero__actions {
  display: flex;
  gap: var(--space-2);
  flex-wrap: wrap;
}

.ds-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 44px;
  padding: 0 22px;
  border-radius: 4px;
  font-family: var(--font-sans);
  font-size: var(--text-base);
  font-weight: var(--weight-medium);
  cursor: pointer;
  border: 1px solid transparent;
  transition: opacity .2s ease;
}
.ds-btn:hover { opacity: 0.88; }
.ds-btn .mdi { font-size: 18px; }

.ds-btn--flat {
  background: var(--color-brand);
  color: #fff;
  border-color: var(--color-brand);
}

.ds-btn--outlined {
  background: transparent;
  color: #fff;
  border-color: rgba(255,255,255,0.6);
}
</style>