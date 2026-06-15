<template>
  <section class="features">
    <div class="features__container">
      <h2 class="features__title">{{ t.title }}</h2>
      <p class="features__sub">{{ t.sub }}</p>
      <div class="features__grid">
        <div v-for="feature in t.features" :key="feature.title" class="ds-card ds-card--outlined feature-card">
          <div class="feature-card__icon">
            <i :class="`mdi ${feature.icon}`" />
          </div>
          <div class="ds-card__header">
            <div class="ds-card__header-text">
              <h3 class="ds-card__title">{{ feature.title }}</h3>
              <p class="ds-card__subtitle">{{ feature.subtitle }}</p>
            </div>
          </div>
          <p class="ds-card__text">{{ feature.text }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { inject, computed } from 'vue'

const lang = inject('lang')

const translations = {
  nl: {
    title: 'Wat bieden wij aan?',
    sub: 'Alles voor een onvergetelijk verblijf',
    features: [
      { icon: 'mdi-tent', title: 'Kampeerplaatsen', subtitle: 'Tent, caravan & camper', text: 'Ruime plaatsen met elektra, water en riolering. Omgeven door natuur en toch alle comfort.' },
      { icon: 'mdi-home-outline', title: 'Chalets & Lodges', subtitle: 'Volledig ingericht', text: 'Onze chalets zijn voorzien van alle gemakken. Ideaal voor gezinnen die willen genieten zonder te kamperen.' },
      { icon: 'mdi-waves', title: 'Zwembad', subtitle: 'Buiten & binnen', text: 'Verfrissend buitenzwembad en een verwarmd binnenbad. Voor jong en oud het hele seizoen.' },
      { icon: 'mdi-bicycle', title: 'Fietsverhuur', subtitle: 'Verken de omgeving', text: 'Huur een fiets en ontdek de mooiste routes over de Veluwe. Voor elk niveau een passende route.' },
      { icon: 'mdi-food-fork-drink', title: 'Restaurant', subtitle: 'Lokale producten', text: 'Geniet van seizoensgebonden gerechten in ons sfeervolle restaurant. Elke dag vers bereid.' },
      { icon: 'mdi-wifi', title: 'Gratis WiFi', subtitle: 'Op het hele park', text: 'Snel internet beschikbaar op alle plaatsen en in alle accommodaties. Blijf altijd verbonden.' },
    ],
  },
  en: {
    title: 'What do we offer?',
    sub: 'Everything for an unforgettable stay',
    features: [
      { icon: 'mdi-tent', title: 'Camping pitches', subtitle: 'Tent, caravan & camper', text: 'Spacious pitches with electricity, water and sewage. Surrounded by nature with all the comforts.' },
      { icon: 'mdi-home-outline', title: 'Chalets & Lodges', subtitle: 'Fully equipped', text: 'Our chalets are equipped with all amenities. Ideal for families who want to enjoy without camping.' },
      { icon: 'mdi-waves', title: 'Swimming pool', subtitle: 'Outdoor & indoor', text: 'Refreshing outdoor pool and a heated indoor pool. For young and old throughout the season.' },
      { icon: 'mdi-bicycle', title: 'Bike rental', subtitle: 'Explore the area', text: 'Rent a bike and discover the most beautiful routes across the Veluwe. A route for every level.' },
      { icon: 'mdi-food-fork-drink', title: 'Restaurant', subtitle: 'Local products', text: 'Enjoy seasonal dishes in our cozy restaurant. Freshly prepared every day.' },
      { icon: 'mdi-wifi', title: 'Free WiFi', subtitle: 'Across the entire park', text: 'Fast internet available at all pitches and accommodations. Stay connected at all times.' },
    ],
  },
  de: {
    title: 'Was bieten wir an?',
    sub: 'Alles für einen unvergesslichen Aufenthalt',
    features: [
      { icon: 'mdi-tent', title: 'Campingplätze', subtitle: 'Zelt, Wohnwagen & Camper', text: 'Geräumige Stellplätze mit Strom, Wasser und Kanalisation. Umgeben von Natur mit allem Komfort.' },
      { icon: 'mdi-home-outline', title: 'Chalets & Lodges', subtitle: 'Voll ausgestattet', text: 'Unsere Chalets sind mit allen Annehmlichkeiten ausgestattet. Ideal für Familien, die ohne Zelten genießen möchten.' },
      { icon: 'mdi-waves', title: 'Schwimmbad', subtitle: 'Außen & innen', text: 'Erfrischendes Außenbecken und ein beheiztes Hallenbad. Für Jung und Alt die ganze Saison.' },
      { icon: 'mdi-bicycle', title: 'Fahrradverleih', subtitle: 'Erkunden Sie die Umgebung', text: 'Mieten Sie ein Fahrrad und entdecken Sie die schönsten Routen über die Veluwe. Für jedes Niveau.' },
      { icon: 'mdi-food-fork-drink', title: 'Restaurant', subtitle: 'Lokale Produkte', text: 'Genießen Sie saisonale Gerichte in unserem gemütlichen Restaurant. Jeden Tag frisch zubereitet.' },
      { icon: 'mdi-wifi', title: 'Kostenloses WLAN', subtitle: 'Im gesamten Park', text: 'Schnelles Internet an allen Stellplätzen und Unterkünften verfügbar. Bleiben Sie immer verbunden.' },
    ],
  },
}

const t = computed(() => translations[lang.value])
</script>

<style scoped>
.features {
  padding: var(--space-10) var(--space-8);
  background: var(--color-bg);
}

.features__container {
  max-width: 1100px;
  margin: 0 auto;
}

.features__title {
  font-size: var(--text-2xl);
  font-weight: var(--weight-bold);
  color: var(--color-ink);
  margin: 0 0 var(--space-2);
  letter-spacing: -0.01em;
}

.features__sub {
  font-size: var(--text-base);
  color: var(--color-muted);
  margin: 0 0 var(--space-8);
}

.features__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: var(--space-4);
}

.ds-card {
  background: var(--color-surface);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.ds-card--outlined {
  border: 1px solid var(--color-line);
}

.ds-card__header {
  padding: 0 16px;
}

.ds-card__title {
  font-size: var(--text-lg);
  font-weight: var(--weight-semibold);
  margin: 0;
}

.ds-card__subtitle {
  font-size: var(--text-sm);
  color: var(--color-muted);
  margin: 4px 0 0;
}

.ds-card__text {
  padding: 12px 16px 16px;
  font-size: var(--text-sm);
  color: var(--color-muted);
  line-height: 1.55;
  margin: 0;
}

.feature-card__icon {
  padding: 16px 16px 8px;
  color: var(--color-brand);
}

.feature-card__icon .mdi {
  font-size: 32px;
}
</style>