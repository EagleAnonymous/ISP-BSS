<x-subscriber-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ai Chatbot') }}
            </h2>
            <p class="mt-1 text-sm text-gray-500">AI Customer Support Assistant — I'll help you troubleshoot your connection.</p>
        </div>
    </x-slot>

    <div class="py-2">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-[70vh] min-h-[460px]"
                 x-data="chatFlow()">
                {{-- Chat header --}}
                <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 5V20.7929C3 21.2383 3.53857 21.4614 3.85355 21.1464L7.70711 17.2929C7.89464 17.1054 8.149 17 8.41421 17H19C20.1046 17 21 16.1046 21 15V5C21 3.89543 20.1046 3 19 3H5C3.89543 3 3 3.89543 3 5Z" />
                            <path d="M15 12C14.2005 12.6224 13.1502 13 12 13C10.8498 13 9.79952 12.6224 9 12" />
                            <path d="M9 8.01953V8" />
                            <path d="M15 8.01953V8" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Smart ISP AI Assistant</p>
                        <p class="text-xs text-green-600 font-medium">● Online</p>
                    </div>
                </div>

                {{-- Messages --}}
                <div x-ref="messages"
                     class="flex-1 min-h-0 px-6 py-6 space-y-4 overflow-y-auto bg-gray-50">
                    <template x-for="(msg, i) in messages" :key="i">
                        <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm shadow-sm whitespace-pre-line"
                                 :class="msg.role === 'user' ? 'bg-blue-600 text-white rounded-br-md' : 'bg-white text-gray-800 border border-gray-200 rounded-bl-md'">
                                <p x-text="msg.text"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Quick reply buttons --}}
                    <template x-if="showYesNo">
                        <div class="flex gap-2">
                            <button type="button" @click="answerYesNo(true)"
                                class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition">
                                Yes, it worked
                            </button>
                            <button type="button" @click="answerYesNo(false)"
                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 transition">
                                No, still having issues
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Input --}}
                <div class="px-6 py-4 border-t border-gray-200 flex items-center gap-3">
                    <input type="text"
                           x-ref="chatInput"
                           x-model="input"
                           @keydown.enter="send()"
                           x-bind:disabled="busy"
                           x-bind:placeholder="inputPlaceholder"
                           class="flex-1 rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 disabled:bg-gray-100">
                    <button type="button" @click="send()"
                        x-bind:disabled="busy"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!busy">Send</span>
                        <span x-show="busy" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" d="M12 3a9 9 0 109 9" />
                            </svg>
                            Creating...
                        </span>
                    </button>
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
                contact: {{ Js::from($subscriber?->contact) }},
                subscriberName: {{ Js::from($user->name) }},
                collectingAddress: false,
                pendingDetails: null,

                get inputPlaceholder() {
                    if (this.collectingAddress) return 'Enter your complete service address...';
                    if (this.step === 'start') return 'e.g. We have no internet connection';
                    return 'Type your question...';
                },

                init() {
                    this.messages.push({ role: 'bot', text: this.greeting() });
                    this.$nextTick(() => this.scrollDown());
                },

                greeting() {
                    return 'Hello {{ $user->name }}! 👋 Welcome to Smart ISP Customer Support.\n\n' +
                        'I am here to help you with any internet connection issues. What seems to be the problem?';
                },

                // ---- Groq AI integration ----

                history() {
                    // Last 12 messages (role + content) so Groq has context,
                    // capped to keep the request small. Bot bubbles are sent
                    // as "assistant" because the AI API only accepts
                    // system / user / assistant roles.
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
                            throw new Error(data.message || 'Could not reach the AI assistant');
                        }

                        return data.reply;
                    } catch (err) {
                        return 'I am sorry, I ran into a problem reaching my AI brain right now. 🙏 ' +
                            'Please try again in a moment, or reply "ticket" and I can escalate your concern to our team.';
                    } finally {
                        this.busy = false;
                    }
                },

                async sendToAI(text) {
                    const reply = await this.askAI(text);
                    this.pushBot(reply);
                    this.scrollDown();
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
                    this.showYesNo = false;
                    this.scrollDown();
                },

                send() {
                    const text = this.input.trim();
                    if (!text || this.busy) return;

                    if (this.collectingAddress) {
                        this.submitTicket(this.pendingDetails, text);
                        return;
                    }

                    this.pushUser(text);
                    this.input = '';

                    if (this.step === 'idle') {
                        this.startFlow(text);
                    } else {
                        this.genericReply(text);
                    }
                },

                startFlow(text) {
                    const t = text.toLowerCase();
                    let category;

                    if (/(no internet|no connection|offline|no signal|los|wala.*internet|disconnect)/.test(t)) {
                        category = 'no_connection';
                    } else if (/(slow|lag|laging|buffering|speed|mabagal)/.test(t)) {
                        category = 'slow_connection';
                    } else if (/(billing|bill|invoice|payment|charge|bayad)/.test(t)) {
                        category = 'billing_concern';
                    } else if (/(install|new connection|pakabit|lipat)/.test(t)) {
                        category = 'installation_request';
                    } else if (/(router|modem|wifi|equipment|device|hardware)/.test(t)) {
                        category = 'equipment_issue';
                    } else {
                        category = 'other';
                    }

                    this.category = category;

                    if (category === 'no_connection') {
                        this.step = 'troubleshooting';
                        this.pushBot("I'm sorry to hear that you're having trouble with your internet connection. Let's work through a few simple steps together.\n\n" +
                            "📶 **Step 1 — Check the power**\nMake sure your modem/router is plugged in securely and the power outlet is switched on.\n\n" +
                            "💡 **Step 2 — Check the indicator lights**\nThe Power light should be solid. The Internet/Online light should be steady (usually green) and not red or blinking.\n\n" +
                            "🔌 **Step 3 — Restart your modem/router**\nUnplug the power, wait 30 seconds, then plug it back in. Wait 2–3 minutes for it to fully reconnect.\n\n" +
                            "📞 **Step 4 — Check cables**\nMake sure the fiber/coaxial cable and LAN cables are firmly connected at both ends.\n\n" +
                            'Please try these steps and let me know if your connection is working now.');
                        this.$nextTick(() => this.pushYesNo());
                    } else if (category === 'slow_connection') {
                        this.step = 'troubleshooting';
                        this.pushBot("Thank you for reporting that. Let me help you speed things up with a few quick checks:\n\n" +
                            "1️⃣ **Restart your modem/router** — unplug it for 30 seconds and plug it back in.\n" +
                            "2️⃣ **Move closer to the router** — walls and distance can weaken the signal.\n" +
                            "3️⃣ **Limit devices** — too many devices at once can slow the connection.\n" +
                            "4️⃣ **Run a speed test** — visit speedtest.net and note the results.\n\n" +
                            'Please try these and let me know if your speed improves.');
                        this.$nextTick(() => this.pushYesNo());
                    } else if (category === 'billing_concern') {
                        this.step = 'idle';
                        this.pushBot('You can check your invoices and outstanding balance under the **Billing** section of your account.\n\n' +
                            'If you have a question about a specific charge, I would be happy to escalate it to our billing team. Would you like me to create a support ticket for you? (Just reply "yes" to escalate.)');
                    } else if (category === 'installation_request') {
                        this.step = 'idle';
                        this.pushBot('Thank you! For installation requests, our field team will need to schedule a visit.\n\n' +
                            'Would you like me to create a support ticket so our team can contact you to arrange the installation? (Reply "yes" to escalate.)');
                    } else if (category === 'equipment_issue') {
                        this.step = 'troubleshooting';
                        this.pushBot('Let me help you with your equipment.\n\n' +
                            "🔌 **Check the power adapter** — make sure it's firmly connected to the modem and the wall outlet.\n" +
                            "🔄 **Restart the device** — unplug for 30 seconds, then power back on.\n" +
                            "📶 **Check the lights** — the Power and Internet lights should be solid.\n\n" +
                            'Please give that a try and let me know if it helps.');
                        this.$nextTick(() => this.pushYesNo());
                    } else {
                        // Free-form / unmatched question -> hand off to Groq AI.
                        this.step = 'idle';
                        this.sendToAI(text);
                    }
                },

                pushYesNo() {
                    this.showYesNo = true;
                },

                answerYesNo(resolved) {
                    this.showYesNo = false;
                    this.pushUser(resolved ? 'Yes, it worked!' : 'No, still having issues');

                    if (resolved) {
                        this.step = 'idle';
                        this.pushBot('Excellent! 🎉 I am glad the troubleshooting steps helped get your connection back up.\n\n' +
                            'Is there anything else I can help you with?');
                    } else {
                        this.escalate();
                    }
                },

                escalate() {
                    this.step = 'escalating';
                    this.pushBot("I'm sorry the issue is still there. No worries — I will escalate this right away.\n\n" +
                        'Thank you, **' + this.subscriberName + '**. We have received your complaint and your concern is now being escalated to our technical support team.\n\n' +
                        'To complete the ticket, I just need your **complete service address** so our field technicians can locate you. Please type your full address below.');
                    this.collectingAddress = true;
                    this.pendingDetails = {
                        category: this.category,
                        subject: this.subjectFor(this.category),
                        description: this.descriptionFor(this.category),
                    };
                    this.$nextTick(() => this.$refs.chatInput && this.$refs.chatInput.focus());
                },

                subjectFor(category) {
                    const map = {
                        no_connection: 'No Internet Connection — escalated from AI assistant',
                        slow_connection: 'Slow Internet Connection — escalated from AI assistant',
                        billing_concern: 'Billing Concern — escalated from AI assistant',
                        installation_request: 'Installation Request — escalated from AI assistant',
                        equipment_issue: 'Equipment Issue — escalated from AI assistant',
                        other: 'Technical Issue — escalated from AI assistant',
                    };
                    return map[category] || map.other;
                },

                descriptionFor(category) {
                    const base = 'Reported via the AI customer support assistant after basic troubleshooting did not resolve the issue.';
                    const map = {
                        no_connection: 'No internet connection. ' + base + ' Technician visit requested.',
                        slow_connection: 'Internet connection is slow. ' + base + ' Technician visit requested.',
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

                    const description = details.description + '\n\n' +
                        '--- Service Details ---\n' +
                        'Subscriber: ' + this.subscriberName + '\n' +
                        'Contact Number: ' + (this.contact || 'Not provided') + '\n' +
                        'Complete Address: ' + address + '\n' +
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
                            '✅ **Your support ticket has been created!**\n\n' +
                            'Thank you, **' + this.subscriberName + '**. We have received your complaint and it has been escalated to our technical support team.\n\n' +
                            '📋 **Ticket Number:** ' + t.ticket_number + '\n' +
                            '📌 **Status:** Open / Pending Technical Visit\n' +
                            '⏱️ **Date & Time Reported:** ' + t.reported_at + '\n' +
                            '🎯 **Priority:** ' + t.priority.charAt(0).toUpperCase() + t.priority.slice(1) + '\n\n' +
                            'Our field technicians have been notified and will contact you soon. Please keep your contact number available. Is there anything else I can help you with?'
                        );
                    } catch (err) {
                        this.step = 'idle';
                        this.collectingAddress = false;
                        this.pushBot('I am sorry, I ran into a problem creating your ticket. Please try again in a moment, or contact our office directly. 🙏');
                    } finally {
                        this.busy = false;
                    }
                },

                genericReply(text) {
                    const t = text.toLowerCase();

                    if (this.step === 'idle' && /(yes|yeah|ticket|escalate|create)/.test(t)) {
                        this.escalate();
                        return;
                    }

                    if (/(no internet|no connection|offline|los)/.test(t)) {
                        this.step = 'troubleshooting';
                        this.pushBot("Let's start with a quick restart:\n\n" +
                            "1. Unplug your modem/router from power.\n" +
                            "2. Wait 30 seconds.\n" +
                            "3. Plug it back in and wait 2–3 minutes.\n" +
                            'Did this restore your connection?');
                        this.$nextTick(() => this.pushYesNo());
                        return;
                    }

                    if (/(slow|buffering|mabagal|lag)/.test(t)) {
                        this.step = 'troubleshooting';
                        this.pushBot("For a slow connection, try:\n\n" +
                            "1. Restarting your modem/router.\n" +
                            "2. Moving closer to the router or removing obstacles.\n" +
                            "3. Limiting the number of connected devices.\n" +
                            'Did your speed improve?');
                        this.$nextTick(() => this.pushYesNo());
                        return;
                    }

                    // Any other follow-up -> let Groq AI answer it.
                    this.sendToAI(text);
                },
            };
        }
    </script>
</x-subscriber-layout>

