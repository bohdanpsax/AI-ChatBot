<template>
  <!-- Floating button -->
  <button class="chat-fab" @click="toggleChat" aria-label="Open chat">
    <i :class="`mdi ${isOpen ? 'mdi-close' : 'mdi-chat-outline'}`" />
  </button>

  <!-- Chat window -->
  <Transition name="chat">
    <div v-if="isOpen" class="chat-window">
      <!-- Header -->
      <div class="chat-header">
        <div class="chat-header__info">
          <div class="chat-header__avatar">
            <i class="mdi mdi-robot-happy-outline" />
          </div>
          <div>
            <p class="chat-header__name">Camping Assistent</p>
            <p class="chat-header__status">
              <span class="chat-header__dot" />
              Online
            </p>
          </div>
        </div>
        <button class="chat-header__close" @click="toggleChat">
          <i class="mdi mdi-close" />
        </button>
      </div>

      <!-- Messages -->
      <div class="chat-messages" ref="messagesEl">
        <div
          v-for="(msg, i) in messages"
          :key="i"
          :class="['chat-msg', msg.role === 'user' ? 'chat-msg--user' : 'chat-msg--bot']"
        >
          <div class="chat-msg__bubble">{{ msg.content }}</div>
        </div>

        <!-- Typing indicator -->
        <div v-if="loading" class="chat-msg chat-msg--bot">
          <div class="chat-msg__bubble chat-msg__bubble--typing">
            <span /><span /><span />
          </div>
        </div>

        <!-- FAQ buttons -->
        <div v-if="showFaq && !loading && messages.length <= 1" class="faq-list">
          <p class="faq-list__label">{{ ui.faqLabel }}</p>
          <button
            v-for="faq in faqs"
            :key="faq.id"
            class="faq-btn"
            @click="selectFaq(faq.question)"
          >
            {{ faq.question }}
          </button>
          <button class="faq-btn faq-btn--other" @click="showFaq = false">
            <i class="mdi mdi-pencil-outline" />
            {{ ui.otherQuestion }}
          </button>
        </div>
      </div>

      <!-- Input -->
      <div class="chat-input" v-if="!showFaq || messages.length > 1">
        <input
          v-model="inputText"
          class="chat-input__field"
          :placeholder="placeholder"
          @keydown.enter="sendMessage"
          :disabled="loading"
        />
        <button
          class="chat-input__send"
          @click="sendMessage"
          :disabled="loading || !inputText.trim()"
        >
          <i class="mdi mdi-send" />
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, nextTick, inject, computed, watch } from 'vue'

const CAMPSITE_ID = 1

const isOpen = ref(false)
const inputText = ref('')
const loading = ref(false)
const messagesEl = ref(null)
const sessionId = ref(null)
const showFaq = ref(true)
const faqs = ref([])
const lang = inject('lang')

const welcomeMessages = {
  nl: 'Hallo! Ik ben de assistent van De Groene Weide. Hoe kan ik u helpen? 🏕️',
  en: 'Hello! I am the assistant of De Groene Weide. How can I help you? 🏕️',
  de: 'Hallo! Ich bin der Assistent von De Groene Weide. Wie kann ich Ihnen helfen? 🏕️',
}

const placeholders = {
  nl: 'Stel een vraag...',
  en: 'Ask a question...',
  de: 'Stellen Sie eine Frage...',
}

const uiTexts = {
  nl: { faqLabel: 'Veelgestelde vragen:', otherQuestion: 'Andere vraag stellen' },
  en: { faqLabel: 'Frequently asked questions:', otherQuestion: 'Ask another question' },
  de: { faqLabel: 'Häufige Fragen:', otherQuestion: 'Andere Frage stellen' },
}

const messages = ref([
  { role: 'bot', content: welcomeMessages['nl'] },
])

const placeholder = computed(() => placeholders[lang.value])
const ui = computed(() => uiTexts[lang.value])

async function loadFaqs() {
  try {
    const res = await fetch(`/api/campsites/${CAMPSITE_ID}/faqs?language=${lang.value}`)
    const data = await res.json()
    faqs.value = (data.data || []).slice(0, 5)
  } catch (e) {
    faqs.value = []
  }
}

watch(lang, async (newLang) => {
  messages.value = [{ role: 'bot', content: welcomeMessages[newLang] }]
  sessionId.value = null
  showFaq.value = true
  await loadFaqs()
})

function toggleChat() {
  isOpen.value = !isOpen.value
  if (isOpen.value && faqs.value.length === 0) {
    loadFaqs()
  }
}

async function selectFaq(question) {
  showFaq.value = false
  messages.value.push({ role: 'user', content: question })
  loading.value = true
  scrollToBottom()
  await sendToApi(question)
}

async function sendMessage() {
  const text = inputText.value.trim()
  if (!text || loading.value) return
  messages.value.push({ role: 'user', content: text })
  inputText.value = ''
  loading.value = true
  scrollToBottom()
  await sendToApi(text)
}

async function sendToApi(text) {
  try {
    const res = await fetch('/api/chat', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message: text,
        campsite_id: CAMPSITE_ID,
        session_id: sessionId.value,
        language: lang.value,
      }),
    })

    const data = await res.json()
    if (data.session_id) sessionId.value = data.session_id

    messages.value.push({
      role: 'bot',
      content: data.reply || 'Sorry, er ging iets mis.',
    })
  } catch (e) {
    messages.value.push({
      role: 'bot',
      content: 'Er is iets misgegaan. Probeer het opnieuw.',
    })
  } finally {
    loading.value = false
    scrollToBottom()
  }
}

async function scrollToBottom() {
  await nextTick()
  if (messagesEl.value) {
    messagesEl.value.scrollTop = messagesEl.value.scrollHeight
  }
}
</script>

<style scoped>
.chat-fab {
  position: fixed;
  bottom: 28px;
  right: 28px;
  width: 56px;
  height: 56px;
  border-radius: 999px;
  background: var(--color-brand);
  color: #fff;
  border: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(162, 82, 147, 0.4);
  z-index: 200;
  transition: transform .2s ease, box-shadow .2s ease;
}
.chat-fab:hover {
  transform: scale(1.08);
  box-shadow: 0 6px 20px rgba(162, 82, 147, 0.5);
}
.chat-fab .mdi { font-size: 26px; }

.chat-window {
  position: fixed;
  bottom: 96px;
  right: 28px;
  width: 360px;
  height: 540px;
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  box-shadow: 0 8px 40px rgba(0,0,0,0.18);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 199;
}

.chat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  background: var(--color-header);
  color: #fff;
}
.chat-header__info {
  display: flex;
  align-items: center;
  gap: 10px;
}
.chat-header__avatar {
  width: 36px;
  height: 36px;
  border-radius: 999px;
  background: rgba(255,255,255,0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}
.chat-header__avatar .mdi { font-size: 20px; }
.chat-header__name {
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  margin: 0;
}
.chat-header__status {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: var(--text-xs);
  opacity: 0.8;
  margin: 2px 0 0;
}
.chat-header__dot {
  width: 7px;
  height: 7px;
  border-radius: 999px;
  background: #4ade80;
  display: inline-block;
}
.chat-header__close {
  background: transparent;
  border: 0;
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 999px;
  transition: background .15s;
}
.chat-header__close:hover { background: rgba(255,255,255,0.15); }
.chat-header__close .mdi { font-size: 20px; }

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: var(--color-bg);
}

.chat-msg { display: flex; }
.chat-msg--user { justify-content: flex-end; }
.chat-msg--bot { justify-content: flex-start; }

.chat-msg__bubble {
  max-width: 80%;
  padding: 10px 14px;
  border-radius: 16px;
  font-size: var(--text-sm);
  line-height: 1.5;
}
.chat-msg--user .chat-msg__bubble {
  background: var(--color-brand);
  color: #fff;
  border-bottom-right-radius: 4px;
}
.chat-msg--bot .chat-msg__bubble {
  background: var(--color-surface);
  color: var(--color-ink);
  border: 1px solid var(--color-line);
  border-bottom-left-radius: 4px;
}

.chat-msg__bubble--typing {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 12px 16px;
}
.chat-msg__bubble--typing span {
  width: 7px;
  height: 7px;
  border-radius: 999px;
  background: var(--color-muted);
  animation: typing 1.2s infinite ease-in-out;
}
.chat-msg__bubble--typing span:nth-child(2) { animation-delay: 0.2s; }
.chat-msg__bubble--typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30% { transform: translateY(-5px); opacity: 1; }
}

/* FAQ buttons */
.faq-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 4px;
}
.faq-list__label {
  font-size: var(--text-xs);
  color: var(--color-muted);
  margin: 0 0 4px;
  font-weight: var(--weight-medium);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.faq-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 10px 14px;
  background: var(--color-surface);
  border: 1px solid var(--color-line);
  border-radius: var(--radius-md);
  font-family: var(--font-sans);
  font-size: var(--text-sm);
  color: var(--color-ink);
  cursor: pointer;
  text-align: left;
  transition: border-color .15s, background .15s;
}
.faq-btn:hover {
  border-color: var(--color-brand);
  background: var(--color-brand-soft);
  color: var(--color-brand);
}
.faq-btn--other {
  color: var(--color-muted);
  border-style: dashed;
}
.faq-btn--other:hover {
  color: var(--color-brand);
  border-color: var(--color-brand);
  background: var(--color-brand-soft);
}
.faq-btn .mdi { font-size: 16px; }

.chat-input {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border-top: 1px solid var(--color-line);
  background: var(--color-surface);
}
.chat-input__field {
  flex: 1;
  height: 40px;
  padding: 0 14px;
  border: 1px solid var(--color-line);
  border-radius: 999px;
  font-family: var(--font-sans);
  font-size: var(--text-sm);
  color: var(--color-ink);
  outline: none;
  transition: border-color .15s;
}
.chat-input__field:focus { border-color: var(--color-brand); }
.chat-input__field::placeholder { color: var(--color-muted); }
.chat-input__field:disabled { opacity: 0.6; }

.chat-input__send {
  width: 40px;
  height: 40px;
  border-radius: 999px;
  background: var(--color-brand);
  color: #fff;
  border: 0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity .15s;
  flex-shrink: 0;
}
.chat-input__send:hover { opacity: 0.88; }
.chat-input__send:disabled { opacity: 0.4; cursor: not-allowed; }
.chat-input__send .mdi { font-size: 18px; }

.chat-enter-active,
.chat-leave-active {
  transition: opacity .2s ease, transform .2s ease;
}
.chat-enter-from,
.chat-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.97);
}
</style>