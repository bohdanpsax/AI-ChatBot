<template>
  <button class="chat-fab" @click="toggleChat" aria-label="Open chat">
    <i :class="`mdi ${isOpen ? 'mdi-close' : 'mdi-chat-outline'}`" />
  </button>

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
        <template v-for="(msg, i) in messages" :key="i">
          <div :class="['chat-msg', msg.role === 'user' ? 'chat-msg--user' : 'chat-msg--bot']">
            <div class="chat-msg__bubble">{{ msg.content }}</div>
          </div>

          <!-- Post-answer actions after last bot message -->
          <div v-if="msg.role === 'bot' && i === messages.length - 1 && !loading && i > 0" class="post-actions">

            <!-- Feedback -->
            <div class="feedback-row" v-if="!msg.feedback">
              <span class="feedback-label">{{ ui.feedbackLabel }}</span>
              <button class="feedback-btn" @click="setFeedback(i, 'up')">👍</button>
              <button class="feedback-btn" @click="setFeedback(i, 'down')">👎</button>
            </div>
            <div v-else-if="msg.feedback === 'up'" class="feedback-thanks feedback-thanks--good">
              <i class="mdi mdi-check-circle-outline" /> {{ ui.feedbackGood }}
            </div>
            <div v-else-if="msg.feedback === 'down'" class="feedback-thanks feedback-thanks--bad">
              <i class="mdi mdi-alert-circle-outline" /> {{ ui.feedbackBad }}
            </div>

            <!-- Related questions from AI -->
            <div v-if="msg.relatedQuestions && msg.relatedQuestions.length > 0" class="related-section">
              <p class="related-label">{{ ui.relatedLabel }}</p>
              <button
                v-for="(q, qi) in msg.relatedQuestions"
                :key="qi"
                class="faq-btn"
                @click="selectFaq(q)"
              >
                {{ q }}
              </button>
            </div>

            <!-- Escalation -->
            <div v-if="msg.showEscalation" class="escalation-row">
              <button class="escalation-btn escalation-btn--resolve" @click="markResolved(i)">
                <i class="mdi mdi-check" /> {{ ui.resolved }}
              </button>
              <button class="escalation-btn escalation-btn--staff" @click="showStaffCard = true">
                <i class="mdi mdi-headset" /> {{ ui.needHelp }}
              </button>
            </div>

            <!-- Ask another -->
            <button class="ask-another-btn" @click="askAnother">
              <i class="mdi mdi-chat-plus-outline" /> {{ ui.another }}
            </button>
          </div>

          <!-- Resolved state -->
          <div v-if="msg.resolved" class="resolved-badge">
            <i class="mdi mdi-check-circle" /> {{ ui.resolvedBadge }}
          </div>
        </template>

        <!-- Typing indicator -->
        <div v-if="loading" class="chat-msg chat-msg--bot">
          <div class="chat-msg__bubble chat-msg__bubble--typing">
            <span /><span /><span />
          </div>
        </div>

        <!-- FAQ buttons (initial state) -->
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

        <!-- Staff contact card -->
        <div v-if="showStaffCard" class="staff-card">
          <div class="staff-card__header">
            <i class="mdi mdi-headset ds-icon--brand" />
            <span>{{ ui.staffTitle }}</span>
          </div>
          <div class="staff-card__body">
            <a href="tel:+31344612345" class="staff-card__link">
              <i class="mdi mdi-phone-outline" /> +31 344 612 345
            </a>
            <a href="mailto:info@campingdebetuwe.nl" class="staff-card__link">
              <i class="mdi mdi-email-outline" /> info@campingdebetuwe.nl
            </a>
          </div>
          <button class="staff-card__close" @click="showStaffCard = false">
            <i class="mdi mdi-close" />
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

const isOpen      = ref(false)
const inputText   = ref('')
const loading     = ref(false)
const messagesEl  = ref(null)
const sessionId   = ref(null)
const showFaq     = ref(true)
const faqs        = ref([])
const showStaffCard = ref(false)
const lang        = inject('lang')

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
  nl: {
    faqLabel:      'Veelgestelde vragen:',
    otherQuestion: 'Andere vraag stellen',
    feedbackLabel: 'Was dit antwoord nuttig?',
    feedbackGood:  'Fijn dat dit heeft geholpen!',
    feedbackBad:   'Sorry, we verbeteren dit!',
    relatedLabel:  'Misschien ook interessant:',
    resolved:      'Vraag beantwoord ✓',
    needHelp:      'Meer hulp nodig',
    another:       'Nog een vraag stellen',
    resolvedBadge: 'Vraag beantwoord',
    staffTitle:    'Contact receptie',
  },
  en: {
    faqLabel:      'Frequently asked questions:',
    otherQuestion: 'Ask another question',
    feedbackLabel: 'Was this answer helpful?',
    feedbackGood:  'Great, glad it helped!',
    feedbackBad:   'Sorry, we will improve this!',
    relatedLabel:  'You might also want to know:',
    resolved:      'Question answered ✓',
    needHelp:      'Need more help',
    another:       'Ask another question',
    resolvedBadge: 'Question answered',
    staffTitle:    'Contact reception',
  },
  de: {
    faqLabel:      'Häufige Fragen:',
    otherQuestion: 'Andere Frage stellen',
    feedbackLabel: 'War diese Antwort hilfreich?',
    feedbackGood:  'Schön, dass es geholfen hat!',
    feedbackBad:   'Entschuldigung, wir verbessern das!',
    relatedLabel:  'Vielleicht auch interessant:',
    resolved:      'Frage beantwortet ✓',
    needHelp:      'Mehr Hilfe benötigt',
    another:       'Weitere Frage stellen',
    resolvedBadge: 'Frage beantwortet',
    staffTitle:    'Rezeption kontaktieren',
  },
}

const messages = ref([
  { role: 'bot', content: welcomeMessages['nl'] },
])

const placeholder = computed(() => placeholders[lang.value])
const ui          = computed(() => uiTexts[lang.value])

async function loadFaqs() {
  try {
    const res  = await fetch(`/api/campsites/${CAMPSITE_ID}/faqs?language=${lang.value}`)
    const data = await res.json()
    faqs.value = (data.data || []).slice(0, 5)
  } catch (e) {
    faqs.value = []
  }
}

async function translateBotMessages(newLang) {
  const botIndexes = messages.value
    .map((m, i) => ({ m, i }))
    .filter(({ m }) => m.role === 'bot' && m.i !== 0)

  if (botIndexes.length === 0) return

  for (const { m, i } of botIndexes) {
    if (i === 0) continue
    try {
      const res = await fetch('/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: `Translate this text to ${newLang}, return only the translation, no explanation: "${m.content}"`,
          campsite_id: CAMPSITE_ID,
          session_id: null,
          language: newLang,
        }),
      })
      const data = await res.json()
      if (data.reply) {
        messages.value[i] = { ...messages.value[i], content: data.reply }
      }
    } catch (e) {
      // keep original
    }
  }
}

watch(lang, async (newLang) => {
  messages.value[0] = { role: 'bot', content: welcomeMessages[newLang] }
  await translateBotMessages(newLang)
  await loadFaqs()
})

function toggleChat() {
  isOpen.value = !isOpen.value
  if (isOpen.value && faqs.value.length === 0) {
    loadFaqs()
  }
}

function setFeedback(index, value) {
  messages.value[index] = { ...messages.value[index], feedback: value }
}

function markResolved(index) {
  messages.value[index] = { ...messages.value[index], resolved: true, showEscalation: false }
}

function askAnother() {
  showFaq.value  = false
  showStaffCard.value = false
  inputText.value = ''
  nextTick(() => {
    const input = document.querySelector('.chat-input__field')
    if (input) input.focus()
  })
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
  loading.value   = true
  scrollToBottom()
  await sendToApi(text)
}

async function sendToApi(text) {
  try {
    const res = await fetch('/api/chat', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message:     text,
        campsite_id: CAMPSITE_ID,
        session_id:  sessionId.value,
        language:    lang.value,
      }),
    })

    const data = await res.json()
    if (data.session_id) sessionId.value = data.session_id

    messages.value.push({
      role:             'bot',
      content:          data.reply || 'Sorry, er ging iets mis.',
      relatedQuestions: data.related_questions || [],
      showEscalation:   data.show_escalation || false,
      feedback:         null,
      resolved:         false,
    })
  } catch (e) {
    messages.value.push({
      role:             'bot',
      content:          'Er is iets misgegaan. Probeer het opnieuw.',
      relatedQuestions: [],
      showEscalation:   false,
      feedback:         null,
      resolved:         false,
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
.chat-fab:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(162, 82, 147, 0.5); }
.chat-fab .mdi { font-size: 26px; }

.chat-window {
  position: fixed;
  bottom: 96px;
  right: 28px;
  width: 360px;
  height: 560px;
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
  flex-shrink: 0;
}
.chat-header__info { display: flex; align-items: center; gap: 10px; }
.chat-header__avatar {
  width: 36px; height: 36px;
  border-radius: 999px;
  background: rgba(255,255,255,0.15);
  display: flex; align-items: center; justify-content: center;
}
.chat-header__avatar .mdi { font-size: 20px; }
.chat-header__name { font-size: var(--text-sm); font-weight: var(--weight-medium); margin: 0; }
.chat-header__status { display: flex; align-items: center; gap: 5px; font-size: var(--text-xs); opacity: 0.8; margin: 2px 0 0; }
.chat-header__dot { width: 7px; height: 7px; border-radius: 999px; background: #4ade80; display: inline-block; }
.chat-header__close {
  background: transparent; border: 0; color: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 999px; transition: background .15s;
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
.chat-msg--bot  { justify-content: flex-start; }

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
  display: flex; align-items: center; gap: 5px; padding: 12px 16px;
}
.chat-msg__bubble--typing span {
  width: 7px; height: 7px; border-radius: 999px;
  background: var(--color-muted);
  animation: typing 1.2s infinite ease-in-out;
}
.chat-msg__bubble--typing span:nth-child(2) { animation-delay: 0.2s; }
.chat-msg__bubble--typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
  0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
  30% { transform: translateY(-5px); opacity: 1; }
}

/* Post actions */
.post-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 4px 0;
}

.feedback-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.feedback-label {
  font-size: var(--text-xs);
  color: var(--color-muted);
  flex: 1;
}
.feedback-btn {
  background: none;
  border: 1px solid var(--color-line);
  border-radius: 999px;
  width: 32px; height: 32px;
  cursor: pointer;
  font-size: 14px;
  display: flex; align-items: center; justify-content: center;
  transition: background .15s;
}
.feedback-btn:hover { background: var(--color-brand-soft); border-color: var(--color-brand); }

.feedback-thanks {
  font-size: var(--text-xs);
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  border-radius: var(--radius-sm);
}
.feedback-thanks--good { background: var(--color-success-soft); color: var(--color-success); }
.feedback-thanks--bad  { background: var(--color-error-soft);   color: var(--color-error); }

.related-section { display: flex; flex-direction: column; gap: 6px; }
.related-label {
  font-size: var(--text-xs);
  color: var(--color-muted);
  font-weight: var(--weight-medium);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0;
}

.escalation-row {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.escalation-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border-radius: var(--radius-sm);
  font-family: var(--font-sans);
  font-size: var(--text-xs);
  font-weight: var(--weight-medium);
  cursor: pointer;
  border: 1px solid transparent;
  transition: opacity .15s;
}
.escalation-btn:hover { opacity: 0.8; }
.escalation-btn .mdi { font-size: 14px; }
.escalation-btn--resolve {
  background: var(--color-success-soft);
  color: var(--color-success);
  border-color: var(--color-success);
}
.escalation-btn--staff {
  background: var(--color-info-soft);
  color: var(--color-info);
  border-color: var(--color-info);
}

.ask-another-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: var(--radius-sm);
  font-family: var(--font-sans);
  font-size: var(--text-xs);
  font-weight: var(--weight-medium);
  cursor: pointer;
  border: 1px dashed var(--color-line);
  background: transparent;
  color: var(--color-muted);
  transition: all .15s;
  align-self: flex-start;
}
.ask-another-btn:hover {
  border-color: var(--color-brand);
  color: var(--color-brand);
  background: var(--color-brand-soft);
}
.ask-another-btn .mdi { font-size: 14px; }

.resolved-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: var(--text-xs);
  color: var(--color-success);
  padding: 4px 10px;
  background: var(--color-success-soft);
  border-radius: 999px;
  align-self: flex-start;
}

/* FAQ */
.faq-list { display: flex; flex-direction: column; gap: 8px; margin-top: 4px; }
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
.faq-btn:hover { border-color: var(--color-brand); background: var(--color-brand-soft); color: var(--color-brand); }
.faq-btn--other { color: var(--color-muted); border-style: dashed; }
.faq-btn--other:hover { color: var(--color-brand); border-color: var(--color-brand); background: var(--color-brand-soft); }
.faq-btn .mdi { font-size: 16px; }

/* Staff card */
.staff-card {
  position: relative;
  background: var(--color-surface);
  border: 1px solid var(--color-line);
  border-radius: var(--radius-md);
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.staff-card__header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: var(--text-sm);
  font-weight: var(--weight-medium);
  color: var(--color-ink);
}
.staff-card__header .mdi { font-size: 20px; }
.staff-card__body { display: flex; flex-direction: column; gap: 8px; }
.staff-card__link {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: var(--text-sm);
  color: var(--color-brand);
  text-decoration: none;
}
.staff-card__link:hover { text-decoration: underline; }
.staff-card__link .mdi { font-size: 16px; }
.staff-card__close {
  position: absolute;
  top: 8px; right: 8px;
  background: none;
  border: 0;
  cursor: pointer;
  color: var(--color-muted);
  display: flex; align-items: center; justify-content: center;
  width: 24px; height: 24px;
  border-radius: 999px;
}
.staff-card__close:hover { background: var(--color-bg); }
.staff-card__close .mdi { font-size: 16px; }

/* Input */
.chat-input {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border-top: 1px solid var(--color-line);
  background: var(--color-surface);
  flex-shrink: 0;
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
  width: 40px; height: 40px;
  border-radius: 999px;
  background: var(--color-brand);
  color: #fff;
  border: 0;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: opacity .15s;
  flex-shrink: 0;
}
.chat-input__send:hover { opacity: 0.88; }
.chat-input__send:disabled { opacity: 0.4; cursor: not-allowed; }
.chat-input__send .mdi { font-size: 18px; }

/* Transition */
.chat-enter-active, .chat-leave-active { transition: opacity .2s ease, transform .2s ease; }
.chat-enter-from, .chat-leave-to { opacity: 0; transform: translateY(12px) scale(0.97); }
</style>