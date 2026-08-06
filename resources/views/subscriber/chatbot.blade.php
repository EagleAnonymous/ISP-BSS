@php $initial = strtoupper(substr($user->name, 0, 1)); @endphp

<x-subscriber-layout>
    <x-slot name="header">
        <div class="flex flex-col items-start">
            <div class="flex w-full items-center gap-3">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('AI Support Chatbot') }}
                </h2>
                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                    Powered by AI
                </span>
            </div>
            <p class="mt-1 max-w-2xl text-left text-sm text-gray-500">
                Get instant help with your concerns. Our AI assistant answers inquiries,
                analyzes issues, recommends solutions, and creates a ticket with your confirmation if unsolved.
            </p>
        </div>
    </x-slot>

    <div class="flex flex-col">
            <div class="max-w-7xl mx-auto w-full h-[580px]">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 xl:grid-cols-4 h-full">
                 {{-- Left column: chat container --}}
                 <div class="lg:col-span-2 xl:col-span-3 h-full">
                     <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col min-h-0 h-full"
                          x-data="chatFlow()">
                          {{-- Messages --}}
                          <div x-ref="messages"
                               role="log"
                               aria-live="polite"
                               aria-relevant="additions"
                               class="flex-1 min-h-0 overflow-y-auto px-3 py-3 space-y-2 bg-gray-50 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 focus:outline-none">
                              <template x-for="(msg, i) in messages" :key="i">
                                  <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                                       {{-- AI message with bot avatar --}}
                                      <div x-cloak
                                             x-show="msg.role === 'bot'"
                                             class="chat-msg-bot flex max-w-[80%] items-start gap-2 rounded-2xl rounded-tl-none bg-white border border-gray-200 px-3 py-2 text-sm text-gray-800 shadow-sm">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white">
                                                <img src="{{ asset('image/chatbot icons/bot-svgrepo-com.svg') }}" alt="AI Assistant" class="h-4 w-4">
                                            </div>
                                            <div class="flex-1">
                                                <div x-html="formatMessage(msg.text)"></div>
                                            </div>
                                             <button type="button"
                                                     @click="readMessage(msg.text)"
                                                     :class="speakingFor() ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                                     class="shrink-0 rounded-lg p-1 transition"
                                                     aria-label="Read this message aloud">
                                                 <img src="{{ asset('image/chatbot icons/speaker-2-svgrepo-com.svg') }}" alt="Read aloud" class="h-4 w-4">
                                             </button>
                                       </div>

                                      {{-- User message --}}
                                       <div x-cloak
                                            x-show="msg.role === 'user'"
                                            class="chat-msg-user max-w-[80%]">
                                           <div class="rounded-2xl rounded-br-none bg-blue-600 px-3 py-2 text-sm text-white shadow-sm">
                                               <p x-text="msg.text"></p>
                                           </div>
                                           <div class="mt-1 flex items-center justify-end gap-1 text-xs text-gray-500">
                                               <span x-text="timeNow()"></span>
                                               <img src="{{ asset('image/chatbot icons/check-double-svgrepo-com.svg') }}" alt="Sent" class="h-3 w-3 text-blue-400">
                                           </div>
                                       </div>
                                  </div>
                              </template>

                              {{-- Typing indicator --}}
                              <div x-cloak x-show="busy" class="flex justify-start">
                                  <div class="chat-msg-bot flex max-w-[80%] items-center gap-2 rounded-2xl rounded-tl-none bg-white border border-gray-200 px-3 py-2 text-sm text-gray-800 shadow-sm">
                                      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white">
                                          <img src="{{ asset('image/chatbot icons/bot-svgrepo-com.svg') }}" alt="AI Assistant" class="h-4 w-4">
                                      </div>
                                      <div class="flex items-center gap-1.5">
                                          <div class="h-2.5 w-2.5 animate-bounce rounded-full bg-gray-400 [animation-delay:0ms]"></div>
                                          <div class="h-2.5 w-2.5 animate-bounce rounded-full bg-gray-400 [animation-delay:150ms]"></div>
                                          <div class="h-2.5 w-2.5 animate-bounce rounded-full bg-gray-400 [animation-delay:300ms]"></div>
                                      </div>
                                  </div>
                              </div>

                               {{-- Yes/No quick reply buttons --}}
                               <template x-if="showYesNo">
                                   <div class="flex flex-col gap-2 px-3 sm:flex-row">
                                       <button type="button" @click="answerYesNo(true)"
                                           class="flex-1 rounded-xl bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                           x-text="awaitingTicketConfirmation ? '✅ Oo, gumawa ng ticket' : '✅ Opo, gumana na!'">
                                       </button>
                                       <button type="button" @click="answerYesNo(false)"
                                           class="flex-1 rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                           x-text="awaitingTicketConfirmation ? '❌ Hindi pa' : '❌ Hindi pa rin, kailangan ng tulong'">
                                       </button>
                                   </div>
                               </template>
                           </div>

                             {{-- Quick Help (visible when idle) --}}
                             <div x-show="step === 'idle' && !busy"
                                  x-cloak
                                  class="border-t border-gray-200 bg-gray-50 py-1.5 shrink-0">
                                 <div class="px-1">
                                     <p class="mb-1 text-[9px] font-semibold uppercase tracking-wider text-gray-500">
                                         Mga Tagong Tanong / Quick Help
                                     </p>
                                     <div class="grid grid-cols-2 gap-1 sm:grid-cols-3 lg:grid-cols-5">
                                         <template x-for="card in quickHelpCards" :key="card.label">
                                             <button type="button"
                                                     @click="sendQuickHelp(card.prompt)"
                                                     :class="{
                                                         'border-red-200 bg-red-50 hover:border-red-400 hover:bg-red-100 focus:ring-red-500': card.color === 'red',
                                                         'border-amber-200 bg-amber-50 hover:border-amber-400 hover:bg-amber-100 focus:ring-amber-500': card.color === 'amber',
                                                         'border-orange-200 bg-orange-50 hover:border-orange-400 hover:bg-orange-100 focus:ring-orange-500': card.color === 'orange',
                                                         'border-blue-200 bg-blue-50 hover:border-blue-400 hover:bg-blue-100 focus:ring-blue-500': card.color === 'blue',
                                                         'border-gray-200 bg-gray-50 hover:border-gray-400 hover:bg-gray-100 focus:ring-gray-500': card.color === 'gray',
                                                     }"
                                                     class="flex flex-col items-center gap-0.5 rounded-lg border px-1.5 py-1.5 text-center shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-1">
                                                 <span class="text-lg" x-text="card.icon"></span>
                                                 <span class="text-[10px] font-semibold text-gray-800" x-text="card.label"></span>
                                                 <span class="text-[8px] text-gray-600" x-text="card.subtext"></span>
                                             </button>
                                         </template>
                                     </div>
                                 </div>
                             </div>

                           {{-- Input bar --}}
                           <div class="border-t border-gray-200 bg-white px-3 py-2 shrink-0">
                              <div class="relative">
                                  <input type="text"
                                         x-ref="chatInput"
                                         x-model="input"
                                         @keydown.enter="send()"
                                         :disabled="busy"
                                         :placeholder="inputPlaceholder"
                                         aria-label="Type your question here"
                                         class="chat-input block w-full rounded-full border border-gray-300 px-3 py-2 pr-12 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 disabled:bg-gray-100">
                                  {{-- Send button --}}
                                  <button type="button" @click="send()"
                                      :disabled="busy"
                                      class="absolute right-1 top-1/2 -translate-y-1/2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                      <span x-show="!busy">
                                          <img src="{{ asset('image/chatbot icons/send-email-svgrepo-com.svg') }}" alt="Send" class="h-4 w-4">
                                      </span>
                                       <span x-show="busy" class="flex items-center">
                                           <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                               <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a9 9 0 109 9" />
                                           </svg>
                                       </span>
                                  </button>
                              </div>
                          </div>
                    </div>
                </div>

                 {{-- Right sidebar: widgets column --}}
                 <div class="lg:col-span-1 xl:col-span-1 h-full flex flex-col overflow-hidden">
                     {{-- My Support Tickets Panel --}}
                     <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex-1 flex flex-col min-h-0">
                         <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between shrink-0">
                             <h3 class="text-sm font-semibold text-gray-900">Aking Support Tickets</h3>
                             <a href="{{ route('subscriber.dashboard') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Tingnan Lahat</a>
                         </div>
                         <div class="py-2 overflow-y-auto flex-1 min-h-0">
                             @if ($recentTickets->isEmpty())
                                 <p class="px-4 py-3 text-sm text-gray-500">Wala pang support tickets.</p>
                             @else
                                 @foreach ($recentTickets as $ticket)
                                     <div class="px-4 py-3">
                                         <div class="flex items-start justify-between gap-2">
                                             <div class="flex-1 min-w-0">
                                                 <p class="text-xs font-semibold text-gray-500">Ticket #{{ $ticket->ticket_number }}</p>
                                                 <p class="mt-0.5 text-sm text-gray-700 truncate">{{ $ticket->subject }}</p>
                                                 <p class="mt-0.5 text-xs text-gray-500">{{ $ticket->created_at->format('M d, Y') }}</p>
                                             </div>
                                             <span @class([
                                                 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium whitespace-nowrap',
                                                 'bg-blue-50 text-blue-700' => $ticket->status === 'open',
                                                 'bg-amber-50 text-amber-700' => $ticket->status === 'in_progress',
                                                 'bg-green-50 text-green-700' => $ticket->status === 'resolved',
                                                 'bg-gray-100 text-gray-600' => $ticket->status === 'closed',
                                             ])>
                                                 {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                             </span>
                                         </div>
                                     </div>
                                 @endforeach
                             @endif
                         </div>
                         @if (!$recentTickets->isEmpty())
                             <div class="border-t border-gray-200 px-4 py-2 shrink-0">
                                 <a href="{{ route('subscriber.dashboard') }}"
                                    class="block text-center text-xs font-medium text-gray-600 hover:text-blue-700 transition">
                                     Pumunta sa Support Tickets
                                 </a>
                             </div>
                         @endif
                     </div>

                     {{-- How It Works Panel --}}
                     <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex-1 flex flex-col min-h-0 mt-4">
                         <div class="px-4 py-3 border-b border-gray-200 shrink-0">
                             <h3 class="text-sm font-semibold text-gray-900">Paano Ito Gumagana</h3>
                         </div>
                         <div class="py-2 overflow-y-auto flex-1 min-h-0">
                             <div class="flex items-start gap-3 px-4 py-3">
                                 <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-green-50">
                                     <img src="{{ asset('image/chatbot icons/message-circle-dots-svgrepo-com.svg') }}" alt="Ask" class="h-5 w-5 text-green-600">
                                 </span>
                                 <div>
                                     <p class="text-sm font-semibold text-gray-900">1. Magtanong Ka</p>
                                     <p class="mt-0.5 text-sm text-gray-600">I-describe ang iyong issue at ipadala sa AI assistant.</p>
                                 </div>
                             </div>
                             <div class="flex items-start gap-3 px-4 py-3">
                                 <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-50">
                                     <img src="{{ asset('image/chatbot icons/help-circle-svgrepo-com.svg') }}" alt="Help" class="h-5 w-5 text-blue-600">
                                 </span>
                                 <div>
                                     <p class="text-sm font-semibold text-gray-900">2. Makakuha ng AI Tulong</p>
                                     <p class="mt-0.5 text-sm text-gray-600">Nagbibigay ang AI ng agarang troubleshooting steps at recommendations.</p>
                                 </div>
                             </div>
                             <div class="flex items-start gap-3 px-4 py-3">
                                 <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-orange-50">
                                     <img src="{{ asset('image/chatbot icons/ticket-2-svgrepo-com.svg') }}" alt="Ticket" class="h-5 w-5 text-orange-600">
                                 </span>
                                 <div>
                                     <p class="text-sm font-semibold text-gray-900">3. Gumawa ng Ticket kung Di Naresolba</p>
                                     <p class="mt-0.5 text-sm text-gray-600">Kung hindi maisolve ng AI, awtomatikong gumagawa ng support ticket.</p>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
            </div>
        </div>
    </div>

    <script>
        function chatFlow() {
            return {
                 messages: [],
                 input: '',
                 busy: false,
                 step: 'idle',
                 showYesNo: false,
                 category: null,
                 contact: @json($subscriber?->contact),
                 subscriberName: @json($user->name),
                 serviceAddress: @json($subscriber?->service_address),
                 collectingAddress: false,
                  pendingDetails: null,
                  lastUserText: '',
                  speaking: false,
                  utterance: null,
                  awaitingTicketConfirmation: false,

                    // Quick Help cards — merged connectivity + general, compact layout.
                    quickHelpCards: [
                        { icon: '📡', label: 'No Connection', subtext: 'Internet totally down', color: 'red', prompt: 'Walang internet connection' },
                        { icon: '🐢', label: 'Slow Internet', subtext: 'Buffering, lag, low speeds', color: 'amber', prompt: 'Mahina ang internet speed ko, mabagal at nagbabuffer' },
                        { icon: '🔌', label: 'Intermittent', subtext: 'Frequent drops', color: 'orange', prompt: 'Madalas na dumadaong internet ko' },
                        { icon: '💳', label: 'Billing Inquiry', subtext: 'Bill & payments', color: 'blue', prompt: 'May tanong ako tungkol sa billing at payment' },
                        { icon: '🎫', label: 'Need a Person', subtext: 'Talk to an agent', color: 'gray', prompt: 'Kailangan ko ng tulong mula sa isang tao, gumawa ng ticket' },
                    ],

                  // —— Centralized command registry ——
                  // Each command has flexible natural-language matchers
                  // (English + Tagalog) and bilingual handlers.
                  commands: [
                      {
                          id: 'no_connection',
                          category: 'no_connection',
                          pattern: /(no internet|no connection|offline|no signal|lost signal|los|walang internet|walang koneksyon|hindi na kumakonekta|hindi gumana|connection lost|no net|disconnect|wala.*internet|hindi na internet|ayaw gumana|na-disconnect|no connection at all)/i,
                          runEn(ctx) {
                              ctx.step = 'troubleshooting';
                              ctx.pushBot("I'm sorry you're having connection trouble. Let's troubleshoot together:\n\n" +
                                  "📶 **Step 1 — Check the power**\nMake sure your modem/router is plugged in and the outlet is switched on.\n\n" +
                                  "💡 **Step 2 — Check the indicator lights**\nThe Power light should be solid. The Internet/Online light should be steady (green) and not red or blinking.\n\n" +
                                  "🔌 **Step 3 — Restart the modem/router**\nUnplug the power, wait 30 seconds, plug it back in, and wait 2–3 minutes to reconnect.\n\n" +
                                  "📞 **Step 4 — Check the cables**\nMake sure the fiber/coaxial and LAN cables are securely connected on both ends.\n\n" +
                                  "Please try these steps and let me know if the connection is restored.");
                              ctx.$nextTick(() => ctx.pushYesNo());
                          },
                          runTl(ctx) {
                              ctx.step = 'troubleshooting';
                              ctx.pushBot("Paumanhin po sa abalang koneksyon. Subukan nating ayusin ito nang magkasama:\n\n" +
                                  "📶 **Hakbang 1 — Tingnan ang kuryente**\nSiguraduhing naka-plug ang modem/router at naka-on ang outlet.\n\n" +
                                  "💡 **Hakbang 2 — Tingnan ang mga ilaw**\nAng Power light ay dapat solid. Ang Internet light ay dapat steady (green) at hindi red o blinking.\n\n" +
                                  "🔌 **Hakbang 3 — I-restart ang modem/router**\nI-unplug ang kuryente, maghintay 30 segundo, i-plug pabalik, at maghintay 2–3 minuto.\n\n" +
                                  "📞 **Hakbang 4 — Suriin ang mga kable**\nSiguraduhing tama ang fiber/coaxial at LAN cables sa magkabilang dulo.\n\n" +
                                  "Subukan po ang mga hakbang na ito at sabihin kung gumana na.");
                              ctx.$nextTick(() => ctx.pushYesNo());
                          },
                      },
                      {
                          id: 'slow_connection',
                          category: 'slow_connection',
                          pattern: /(slow|lag|mabagal|bagal|bumabagal|buffering|speed|mabilis|throttling|mabagal na|mabilis pa|nababalita|loading is slow|keeps buffering|very slow)/i,
                          runEn(ctx) {
                              ctx.step = 'troubleshooting';
                              ctx.pushBot("Thanks for letting me know. Let's try some quick checks to improve your speed:\n\n" +
                                  "1️⃣ **Restart the modem/router** — unplug for 30 seconds, plug back in.\n" +
                                  "2️⃣ **Move closer to the router** — walls and distance can weaken the signal.\n" +
                                  "3️⃣ **Limit connected devices** — too many devices using the network at once can slow things down.\n" +
                                  "4️⃣ **Run a speed test** — visit speedtest.net and note the results.\n\n" +
                                  "Please try these and let me know if the speed has improved.");
                              ctx.$nextTick(() => ctx.pushYesNo());
                          },
                          runTl(ctx) {
                              ctx.step = 'troubleshooting';
                              ctx.pushBot("Salamat sa pag-uulat. Tulungan kitang pabilisin ang internet:\n\n" +
                                  "1️⃣ **I-restart ang modem/router** — i-unplug 30 segundo, i-plug pabalik.\n" +
                                  "2️⃣ **Lumapit sa router** — ang pader at layo ay maaaring hinaan ang signal.\n" +
                                  "3️⃣ **Limitahan ang devices** — masyadong maraming device na sabay-sabay ay bumabagal.\n" +
                                  "4️⃣ **Gumawa ng speed test** — bisitahin ang speedtest.net at tandaan ang resulta.\n\n" +
                                  "Subukan po at sabihin kung mabilis na.");
                              ctx.$nextTick(() => ctx.pushYesNo());
                          },
                      },
                      {
                          id: 'equipment_issue',
                          category: 'equipment_issue',
                          pattern: /(router|modem|wifi|wi-fi|equipment|device|hardware|aparato|wifi not showing|cannot connect to wifi|no wifi|wifi name missing|signal|antenna|wifi signal|hindi nakikita ang wifi|connection drop|drops connection)/i,
                          runEn(ctx) {
                              ctx.step = 'troubleshooting';
                              ctx.pushBot("Let's troubleshoot your equipment together:\n\n" +
                                  "🔌 **Check the power adapter** — make sure it's securely connected to the modem and the wall outlet.\n" +
                                  "🔄 **Restart the device** — unplug for 30 seconds, plug back in.\n" +
                                  "📶 **Check the lights** — the Power and Internet lights should be solid.\n" +
                                  "📡 **Check the antenna** — if your router has external antennas, make sure they are vertical and upright.\n\n" +
                                  "Please try these steps and let me know if they helped.");
                              ctx.$nextTick(() => ctx.pushYesNo());
                          },
                          runTl(ctx) {
                              ctx.step = 'troubleshooting';
                              ctx.pushBot("Tulungan kita sa iyong equipment:\n\n" +
                                  "🔌 **Tingnan ang power adapter** — siguraduhing naka-tight sa modem at sa wall outlet.\n" +
                                  "🔄 **I-restart ang device** — i-unplug 30 segundo, i-plug pabalik.\n" +
                                  "📶 **Tingnan ang mga ilaw** — ang Power at Internet lights ay dapat solid.\n" +
                                  "📡 **Tingnan ang antenna** — kung may antenna ang router, patunton ito.\n\n" +
                                  "Subukan po at sabihin kung nakatulong.");
                              ctx.$nextTick(() => ctx.pushYesNo());
                          },
                      },
                      {
                          id: 'billing_concern',
                          category: 'billing_concern',
                          pattern: /(billing|bill|invoice|payment|charge|bayad|singil|how much|outstanding balance|balance|due date|billing date|late fee|when is my bill due|refund|discount|promo|payment method|how can i pay|palitan|bayaran ko|how to pay|paano magbabayad|magbabayad)/i,
                          runEn(ctx) {
                              ctx.step = 'idle';
                              ctx.pushBot("You can view your invoices and remaining balance in the **Billing** section of your account.\n\n" +
                                  "If you have a specific question about a charge or your balance, I can create a support ticket for you. " +
                                  'Just say "create a ticket" and I will open one right away.');
                          },
                          runTl(ctx) {
                              ctx.step = 'idle';
                              ctx.pushBot("Pwede mong tingnan ang iyong mga invoice at natitirang balance sa **Billing** section.\n\n" +
                                  "Kung may tanong ka tungkol sa isang charge, maaari kong gumawa ng support ticket para sa iyo. " +
                                  'Sabihin mo lang "gumawa ng ticket" at gagawin ko agad.');
                          },
                      },
                      {
                          id: 'installation_request',
                          category: 'installation_request',
                          pattern: /(install|new connection|pakabit|lipat|bagong koneksyon|installation|apply for internet|transfer service|new service|relocate|new line|request installation|gusto kong i-request|magsign up|bagong linya|pa‑install|i‑install)/i,
                          runEn(ctx) {
                              ctx.step = 'idle';
                              ctx.pushBot("Thank you! For installation requests, our field team will need to schedule a visit.\n\n" +
                                  'Would you like me to create a support ticket so our team can contact you? ' +
                                  'Just say "yes" and I will open one right away.');
                          },
                          runTl(ctx) {
                              ctx.step = 'idle';
                              ctx.pushBot("Salamat! Para sa installation request, kailangan ng field team nating i-schedule ang pagbisita.\n\n" +
                                  "Gusto mo bang gumawa ako ng support ticket para ma-contact ka? Sabihin mo lang \"oo\" at gagawin ko agad.");
                          },
                      },
                      {
                          id: 'equipment_plan',
                          category: 'other',
                          pattern: /(plan|package|upgrade|downgrade|change plan|switch plan|plan detail|mabilis|speed|1500|cost|price|monthly|subscription|plan ko|ano ang plan|bundle|what plan|my plan|subscribe)/i,
                          runEn(ctx) {
                              ctx.step = 'idle';
                              ctx.pushBot("You can see your current plan details in the **My Account** section of your dashboard.\n\n" +
                                  "If you'd like to change, upgrade, or downgrade your plan, I can create a ticket for our sales team. " +
                                  'Just say "yes" if you would like me to open a ticket.');
                          },
                          runTl(ctx) {
                              ctx.step = 'idle';
                              ctx.pushBot("Pwede mong makita ang detalye ng iyong plan sa **My Account** section.\n\n" +
                                  "Kung gusto mong palitan, i-upgrade, o i-downgrade ang plan, maaari akong gumawa ng ticket para sa sales team. " +
                                  'Sabihin mo lang "oo" kung gusto mong i-open ang isang ticket.');
                          },
                      },
                  ],

                   get inputPlaceholder() {
                       if (this.collectingAddress) return 'I-type ang buong service address...';
                       if (this.step === 'troubleshooting') return 'e.g., "Opo, gumana na" or "Hindi pa rin"';
                       return 'Sabihin ang tanong mo dito...';
                   },

                init() {
                    this.messages.push({ role: 'bot', text: this.greeting() });
                    this.$nextTick(() => this.scrollDown());
                },

                 greeting() {
                     return 'Hello, {{ $user->name }}! 👋 Welcome to **ISP BSS** Customer Support.\n\n' +
                         "I'm here to help you with your internet connection, billing, installation, or equipment.\n\n" +
                         'Please type your question below and press Enter or click Send. ' +
                         'What can I help you with today?';
                 },

                timeNow() {
                    const now = new Date();
                    let h = now.getHours();
                    const m = now.getMinutes().toString().padStart(2, '0');
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    return h + ':' + m + ' ' + ampm;
                },

                formatMessage(text) {
                    // Convert newlines to <br> and bold markdown (**text**)
                    let html = text
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\n/g, '<br>');
                    return html;
                },

                clearChatAndSend(text) {
                    this.messages = [];
                    this.step = 'idle';
                    this.$nextTick(() => {
                        this.input = text;
                        this.send();
                    });
                },

                 sendQuickHelp(prompt) {
                     if (!prompt || this.busy) return;
                     this.pushUser(prompt);
                     this.input = '';

                     if (this.isEscalateRequest(prompt)) {
                         this.escalate();
                         return;
                     }

                     const cmd = this.matchCommand(prompt);
                     if (cmd) {
                         this.executeCommand(cmd, prompt);
                     } else {
                         this.sendToAI(prompt);
                     }
                     this.$nextTick(() => this.scrollDown());
                 },

                // ---- Groq AI integration ----

                history() {
                    return this.messages.slice(-12).map(m => ({
                        role: m.role === 'bot' ? 'assistant' : 'user',
                        content: m.text,
                    }));
                },

                async askAI(text) {
                    this.busy = true;

                    try {
                        const res = await fetch('{{ route('subscriber.chatbot.chat') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                message: text,
                                history: this.history(),
                            }),
                        });

                        const data = await res.json();

                         if (!res.ok || !data.success) {
                             throw new Error(data.message || 'Paumanhin, hindi ako makakakuha ng sagot ngayon');
                         }

                         return data.reply;
                     } catch (err) {
                         return 'Paumanhin po, may maliit kong problema sa pag-connect. 🙏 ' +
                             'Pakisubukan muli, o sabihin mo lang ang "ticket" para i-escalate ang iyong tanong sa aming team.';
                     } finally {
                        this.busy = false;
                    }
                },

                  async sendToAI(text) {
                      const reply = await this.askAI(text);
                      this.pushBot(reply);
                      this.step = 'idle';
                      this.scrollDown();

                      // If the AI response indicates the issue cannot be resolved
                      // locally, ask the user for explicit confirmation before
                      // creating a support ticket.
                      const lower = reply.toLowerCase();
                      const escalationPatterns = [
                          /cannot resolve/i, /unable to/i, /not resolved/i,
                          /not fixed/i, /still have.*probl/i, /need a technician/i,
                          /escalat/i, /create.*ticket/i, /open.*ticket/i,
                          /contact.*support/i, /not solve/i, /cannot fix/i,
                          /technical visit/i, /field visit/i, /outside.*scope/i,
                      ];
                      if (this.step !== 'escalating' && this.step !== 'done' &&
                          escalationPatterns.some(p => p.test(lower))) {
                          this.$nextTick(() => {
                              this.awaitingTicketConfirmation = true;
                              this.pushBot(
                                  'I see that your concern may need further assistance from our support team.\n\n' +
                                  'Would you like me to create a support ticket so a technical support specialist can investigate? ' +
                                  'Please reply "yes" to confirm or "no" to continue.',
                                  true
                              );
                          });
                      }
                  },

                scrollDown() {
                    this.$nextTick(() => {
                        const el = this.$refs.messages;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },

                pushBot(text, withYesNo = false) {
                    this.messages.push({ role: 'bot', text: text });
                    this.showYesNo = withYesNo;
                    this.scrollDown();
                },

                pushUser(text) {
                    this.messages.push({ role: 'user', text: text });
                    this.lastUserText = text;
                    this.showYesNo = false;
                    this.scrollDown();
                },

                  send() {
                      const text = this.input.trim();
                      if (!text || this.busy) return;

                      if (this.collectingAddress) {
                          this.pushUser(text);
                          this.input = '';
                          this.submitTicket(this.pendingDetails, text);
                          return;
                      }

                      // During troubleshooting, a typed yes/no answers the confirmation question
                      // (before pushUser resets showYesNo).
                      if (this.showYesNo && this.isYesNoAnswer(text)) {
                          this.input = '';
                          this.answerYesNo(this.isYesAnswer(text));
                          return;
                      }

                      this.pushUser(text);
                      this.input = '';

                      // If the subscriber explicitly asks to create a ticket / escalate,
                      // start the ticket escalation flow (collect address, then create ticket).
                      if (this.isEscalateRequest(text)) {
                          this.escalate();
                          return;
                      }

                      // Try a local command quick-reply first (fast, no API round-trip).
                      const cmd = this.matchCommand(text);
                      if (cmd) {
                          this.executeCommand(cmd, text);
                          this.$nextTick(() => this.scrollDown());
                          return;
                      }

                      // Route everything else to the AI assistant for an accurate answer.
                      this.sendToAI(text);
                  },

                  isEscalateRequest(text) {
                      const t = text.toLowerCase();
                      return /\bticket\b/.test(t) || /escalate/i.test(t) ||
                          /(gumawa.*ticket|mag.*ticket|gawaan.*ticket|magpasakont|create.*ticket|gusto.*ticket)/i.test(t) ||
                          /(tulong.*tao|tulong.*person|talk.*to.*person|talk.*to.*agent|need.*help.*now|kailangan.*tulong)/i.test(t);
                  },

                  matchCommand(text) {
                      return this.commands.find(cmd => cmd.pattern.test(text));
                  },

                  executeCommand(cmd, text) {
                      this.category = cmd.category;
                      if (this.isTagalog(text)) {
                          cmd.runTl(this);
                      } else {
                          cmd.runEn(this);
                      }
                   },

                 pushYesNo() {
                    this.showYesNo = true;
                },

                  answerYesNo(resolved) {
                     this.showYesNo = false;

                     // Ticket-creation confirmation flow
                     if (this.awaitingTicketConfirmation) {
                         this.awaitingTicketConfirmation = false;
                         this.pushUser(resolved ? 'Yes, please create a ticket' : 'No, thank you');

                         if (resolved) {
                             this.escalate();
                         } else {
                             this.step = 'idle';
                             this.pushBot('Okay, I won\'t create a ticket for now. ' +
                                 'If you need further assistance, just type your question anytime.');
                         }
                         return;
                     }

                     // Standard troubleshooting Yes/No flow
                     this.pushUser(resolved ? 'Oo, gumana na!' : 'Hindi, may problema pa rin');

                     if (resolved) {
                         this.step = 'idle';
                         this.pushBot('Magaling! 🎉 Naging mabuti na ang koneksyon mo. Maraming salamat sa pag-verify.\n\n' +
                             'Kung may iba ka pang katanungan, huwag mag-atubiling tanungin ako.');
                     } else {
                         this.escalate();
                     }
                 },

                 escalate() {
                     this.step = 'escalating';
                     this.pushBot(
                         'Salamat po talaga, **' + this.subscriberName + '**. Sisiguraduhin naming lutasan ang iyong koneksyon problem dito.\n\n' +
                         'Binubuo ko ngayon ang iyong suporta ticket at itinatalaga sa aming mga technical support specialist upang suriin ito.\n\n' +
                         'Upang maaari kong makumpleto ito, kailangan ko lang po ang iyong **ganap na service address**. Paki-type po ang buong address sa ibaba.'
                     );
                     this.collectingAddress = true;
                     // Default the category to 'other' if the AI flow hasn't set one.
                     const category = this.category || this.detectCategory(this.lastUserText) || 'other';
                     this.pendingDetails = {
                         category: category,
                         subject: this.subjectFor(category),
                         description: this.descriptionFor(category),
                     };
                      this.$nextTick(() => this.$refs.chatInput && this.$refs.chatInput.focus());
                  },

                  detectCategory(text) {
                     const t = (text || '').toLowerCase();
                     if (/(no internet|no connection|offline|no signal|los|wala.*internet|walang koneksyon|disconnect)/.test(t)) return 'no_connection';
                     if (/(slow|lag|laging|buffering|speed|mabagal|bagal)/.test(t)) return 'slow_connection';
                     if (/(billing|bill|invoice|payment|charge|bayad|singil)/.test(t)) return 'billing_concern';
                     if (/(install|new connection|pakabit|lipat|bagong koneksyon|installation)/.test(t)) return 'installation_request';
                     if (/(router|modem|wifi|equipment|device|hardware|aparato)/.test(t)) return 'equipment_issue';
                     return 'other';
                 },

                   isTagalog(text) {
                      const markers = [
                          'po', 'opo', 'salamat', 'paumanhin', 'kailangan', 'tulong',
                          'problema', 'gumawa', 'tanong', 'bayad', 'singil', 'gusto',
                          'hindi', 'oo', 'salamat', 'bagong', 'mabilis', 'mabagal',
                          'bagal', 'mahina', 'koneksyon', 'wala', 'meron', 'mayroon',
                          'ano', 'saan', 'bakit', 'paano', 'kumusta', 'maganda',
                          'umaga', 'gabi', 'hapon', 'nawala', 'nagana', 'palitan',
                          'magbabayad', 'nakakonekta', 'sundo', 'bigyan', 'paki',
                          'dala', 'dapat', 'maka', 'paraan', 'ano-ano',
                      ];
                       const lower = text.toLowerCase();
                       return markers.some(m => lower.includes(m));
                   },

                subjectFor(category) {
                    const map = {
                        no_connection: 'Walang Koneksyon — iniatas mula sa AI assistant',
                        slow_connection: 'Mahinang Koneksyon — iniatas mula sa AI assistant',
                        billing_concern: 'Billing Concern — iniatas mula sa AI assistant',
                        installation_request: 'Installation Request — iniatas mula sa AI assistant',
                        equipment_issue: 'Equipment Issue — iniatas mula sa AI assistant',
                        other: 'Technical Issue — iniatas mula sa AI assistant',
                    };
                    return map[category] || map.other;
                },

                descriptionFor(category) {
                    const base = 'Iniatas via AI customer support assistant pagkatapos ng basic troubleshooting na di pa rin niresolba.';
                    const map = {
                        no_connection: 'Walang internet connection. ' + base + ' Kailangan ng technician visit.',
                        slow_connection: 'Mabagal na internet connection. ' + base + ' Kailangan ng technician visit.',
                        billing_concern: 'Billing concern. ' + base,
                        installation_request: 'Installation request. ' + base,
                        equipment_issue: 'Equipment issue. ' + base,
                        other: 'Uncategorized technical issue. ' + base,
                    };
                    return map[category] || base;
                },

                 async submitTicket(details, address) {
                     this.busy = true;
                     this.collectingAddress = false;
                     this.input = '';

                     const addressSection = this.serviceAddress
                         ? '\n\n--- Service Address (from profile) ---\n' + this.serviceAddress
                         : '';

                     const description = details.description + '\n\n' +
                         '--- Subscriber Details ---\n' +
                         'Name: ' + this.subscriberName + '\n' +
                         'Contact Number: ' + (this.contact || 'Not provided') + '\n' +
                         'Service Address: ' + (address || 'Not provided') + addressSection + '\n' +
                         'Reported via: AI Chatbot';

                     const payload = {
                         category: details.category,
                         subject: details.subject,
                         description: description,
                     };

                     try {
                         const res = await fetch('{{ route('subscriber.chatbot.ticket') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'Accept': 'application/json',
                                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                             },
                             body: JSON.stringify(payload),
                         });

                         const data = await res.json();
                         if (!res.ok || !data.success) {
                             throw new Error(data.message || 'Could not create ticket');
                         }

                         this.step = 'done';
                         const t = data.ticket;
                          this.pushBot(
                              '✅ **Naibuo na ang iyong suporta ticket!**\n\n' +
                              'Maraming salamat, **' + this.subscriberName + '**. Iniulat na sa aming technical support team ang iyong concern.\n\n' +
                              '📋 **Numero ng Ticket:** ' + t.ticket_number + '\n' +
                              '📌 **Katayuan:** Open — nasa pila para sa teknikal na pagbisita\n' +
                              '⏱️ **Petsa at Oras:** ' + t.reported_at + '\n' +
                              '🎯 **Priority:** ' + t.priority.charAt(0).toUpperCase() + t.priority.slice(1) + '\n\n' +
                              'Inaasahan mong makikipag-ugnayan ang aming field technicians sa iyo sa lalong madaling panahon. Panatilihin po ang inyong contact number. May iba ka pa bang kailangan ng tulong?'
                          );
                     } catch (err) {
                         this.step = 'idle';
                         this.collectingAddress = false;
                         this.pushBot('Paumanhin po, may problema ako sa paggawa ng ticket mo. Subukan mo muli mamaya, o kontakin mo po direktang ang aming office. 🙏');
                     } finally {
                         this.busy = false;
                     }
                  },

                  // —— Quick Help: send a guided prompt from a card button ——
                  sendQuickHelp(prompt) {
                      if (!prompt || this.busy) return;
                      this.pushUser(prompt);
                      this.input = '';

                      if (this.isEscalateRequest(prompt)) {
                          this.escalate();
                          return;
                      }

                      const cmd = this.matchCommand(prompt);
                      if (cmd) {
                          this.executeCommand(cmd, prompt);
                      } else {
                          this.sendToAI(prompt);
                      }
                      this.$nextTick(() => this.scrollDown());
                  },

                  // —— Text-to-speech (Web Speech API) ——
                  readMessage(text) {
                      this.stopReading();
                      if (typeof speechSynthesis === 'undefined') return;

                      const utter = new SpeechSynthesisUtterance(text.replace(/\*\*/g, ''));
                      utter.lang = 'tl-PH';
                      utter.rate = 0.9;
                      utter.onend = () => { this.speaking = false; };
                      this.utterance = utter;
                      this.speaking = true;
                      speechSynthesis.speak(utter);
                  },

                  stopReading() {
                      if (typeof speechSynthesis !== 'undefined') {
                          speechSynthesis.cancel();
                      }
                      this.speaking = false;
                      this.utterance = null;
                  },

                  speakingFor() {
                      return this.speaking;
                  },

                  // —— Natural-language yes / no detection ——
                  isYesNoAnswer(text) {
                      const t = text.toLowerCase().trim();
                      return /^(oo|opo|yes|yeah|sure|done|natapos|gumana|gumana na|resolved|fixed|okay|ok|tama|sapat|pareho)\b/.test(t) ||
                             /^(hindi|no|not yet|hindi pa|not really|hindi pa rin|fail|hindi gumana|wala pa)\b/.test(t);
                  },

                  isYesAnswer(text) {
                      return /^(oo|opo|yes|yeah|sure|done|natapos|gumana|gumana na|resolved|fixed|okay|ok|tama|sapat|pareho)\b/i.test(
                          text.toLowerCase().trim()
                      );
                  },
             };
        }
    </script>
</x-subscriber-layout>
