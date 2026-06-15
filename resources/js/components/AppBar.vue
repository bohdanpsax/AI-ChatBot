<template>
  <header class="ds-appbar">
    <div class="ds-appbar__left">
      <button class="ds-appbar__icon" aria-label="Home">
        <i class="mdi mdi-home-outline" />
      </button>
      <button class="ds-appbar__icon" aria-label="Apps">
        <i class="mdi mdi-apps" />
      </button>
      <div class="ds-appbar__search">
        <i class="mdi mdi-magnify" />
        <input type="search" :placeholder="t.search" />
      </div>
    </div>
    <div class="ds-appbar__right">
      <div class="lang-switcher">
        <button
          v-for="l in langs"
          :key="l"
          :class="['lang-btn', { 'lang-btn--active': lang === l }]"
          @click="lang = l"
        >
          {{ l.toUpperCase() }}
        </button>
      </div>
      <span class="appbar-brand">Camping.care</span>
      <button class="ds-appbar__icon" aria-label="Account">
        <i class="mdi mdi-account-outline" />
      </button>
    </div>
  </header>
</template>

<script setup>
import { inject, computed } from 'vue'

const lang = inject('lang')
const langs = ['nl', 'en', 'de']

const translations = {
  nl: { search: 'Zoek reserveringen...' },
  en: { search: 'Search reservations...' },
  de: { search: 'Reservierungen suchen...' },
}

const t = computed(() => translations[lang.value])
</script>

<style scoped>
.ds-appbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  height: 64px;
  padding: 0 16px;
  background: var(--color-header);
  color: #fff;
  font-family: var(--font-sans);
  position: sticky;
  top: 0;
  z-index: 100;
}
.ds-appbar__left { display: flex; align-items: center; gap: 4px; flex: 1; }
.ds-appbar__right { display: flex; align-items: center; gap: 12px; }

.ds-appbar__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: 0;
  border-radius: 999px;
  background: transparent;
  color: #fff;
  cursor: pointer;
  transition: background-color .15s ease;
}
.ds-appbar__icon .mdi { font-size: 24px; }
.ds-appbar__icon:hover { background: rgba(255,255,255,0.14); }

.ds-appbar__search {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 40px;
  padding: 0 12px;
  margin-left: 8px;
  flex: 1;
  max-width: 380px;
  background: rgba(255,255,255,0.14);
  border-radius: 8px;
}
.ds-appbar__search .mdi { font-size: 20px; color: rgba(255,255,255,0.85); }
.ds-appbar__search input {
  flex: 1;
  border: 0;
  background: transparent;
  outline: none;
  color: #fff;
  font-family: var(--font-sans);
  font-size: var(--text-base);
}
.ds-appbar__search input::placeholder { color: rgba(255,255,255,0.72); }

.appbar-brand {
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  opacity: 0.85;
  letter-spacing: 0.02em;
}

.lang-switcher {
  display: flex;
  gap: 4px;
}

.lang-btn {
  height: 28px;
  padding: 0 10px;
  border-radius: 4px;
  border: 1px solid rgba(255,255,255,0.3);
  background: transparent;
  color: rgba(255,255,255,0.7);
  font-family: var(--font-sans);
  font-size: var(--text-xs);
  font-weight: var(--weight-medium);
  cursor: pointer;
  transition: all .15s ease;
}
.lang-btn:hover {
  background: rgba(255,255,255,0.14);
  color: #fff;
}
.lang-btn--active {
  background: rgba(255,255,255,0.2);
  color: #fff;
  border-color: rgba(255,255,255,0.6);
}
</style>