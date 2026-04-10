import '../css/chatbot-widget.css';
import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

(function () {
    'use strict';

    const scriptTag = document.currentScript;
    const scriptUrl = scriptTag ? new URL(scriptTag.src) : null;
    const baseUrl = scriptUrl ? scriptUrl.origin : '';

    const settings = window.eagleSettings || {};

    function generateSessionId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    function getSessionId() {
        let sessionId = localStorage.getItem('eagle_chatbot_session_id');
        if (!sessionId) {
            sessionId = generateSessionId();
            localStorage.setItem('eagle_chatbot_session_id', sessionId);
        }
        return sessionId;
    }

    const sessionId = getSessionId();
    let echoInstance = null;
    let reverbConfig = null;

    async function fetchConfig() {
        if (reverbConfig) return reverbConfig;

        const response = await fetch(`${baseUrl}/api/chatbot/config`, {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch chatbot config');
        }

        const data = await response.json();
        reverbConfig = data.reverb;
        return reverbConfig;
    }

    async function initEcho() {
        if (echoInstance) return echoInstance;

        const config = await fetchConfig();

        window.Pusher = Pusher;

        echoInstance = new Echo({
            broadcaster: 'reverb',
            key: config.key,
            wsHost: config.host,
            wsPort: config.port,
            wssPort: config.port,
            forceTLS: config.scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        return echoInstance;
    }

    const widgetHTML = `
    <div id="eagle-chatbot" x-data="eagleChatbot">
        <!-- Chat Bubble Button -->
        <button
            @click="toggle()"
            x-show="!open"
            class="ec-bubble"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
        </button>

        <!-- Chat Window -->
        <div
            x-show="open"
            x-transition
            class="ec-window ec-window-enter"
        >
            <!-- Header -->
            <div class="ec-header">
                <div>
                    <h3>Support</h3>
                    <p>Ask us anything</p>
                </div>
                <button @click="toggle()" class="ec-close">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Messages -->
            <div x-ref="messages" class="ec-messages">
                <template x-for="(msg, index) in messages" :key="index">
                    <div>
                        <div x-show="msg.role === 'user'" class="ec-msg-row ec-user">
                            <div class="ec-msg-bubble" x-text="msg.content"></div>
                        </div>
                        <div x-show="msg.role === 'assistant'" class="ec-msg-row ec-assistant">
                            <div class="ec-msg-bubble" x-html="formatMessage(msg.content)"></div>
                        </div>
                    </div>
                </template>

                <!-- Typing Indicator -->
                <div x-show="loading" class="ec-typing">
                    <div class="ec-typing-dots">
                        <div class="ec-dot"></div>
                        <div class="ec-dot"></div>
                        <div class="ec-dot"></div>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="ec-input-area">
                <form @submit.prevent="send()" class="ec-form">
                    <input
                        x-model="input"
                        type="text"
                        placeholder="Type your message..."
                        class="ec-input"
                        :disabled="loading"
                    />
                    <button
                        type="submit"
                        :disabled="loading || !input.trim()"
                        class="ec-send"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
    `;

    function injectWidget() {
        const container = document.createElement('div');
        container.innerHTML = widgetHTML;
        document.body.appendChild(container);
    }

    Alpine.data('eagleChatbot', () => ({
        open: false,
        messages: [],
        input: '',
        loading: false,
        conversationId: null,
        userName: settings.name || '',
        userEmail: settings.email || '',
        channelSubscribed: false,
        currentAssistantMessage: null,

        init() {
            this.subscribeToChannel();

            this.messages.push({
                role: 'assistant',
                content: 'Hi there! How can I help you today?',
            });
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async subscribeToChannel() {
            if (this.channelSubscribed) return;

            const echo = await initEcho();
            const channelName = `chatbot.${sessionId}`;

            echo.channel(channelName)
                .listen('.text_delta', (e) => {
                    if (!this.currentAssistantMessage) {
                        this.loading = false;
                        this.currentAssistantMessage = {
                            role: 'assistant',
                            content: '',
                        };
                        this.messages.push(this.currentAssistantMessage);
                    }
                    this.currentAssistantMessage.content += e.delta;
                    this.$nextTick(() => this.scrollToBottom());
                })
                .listen('.stream_end', () => {
                    this.currentAssistantMessage = null;
                    this.loading = false;
                })
                .listen('.error', () => {
                    this.loading = false;
                    this.currentAssistantMessage = null;
                    this.messages.push({
                        role: 'assistant',
                        content: 'Sorry, something went wrong. Please try again.',
                    });
                });

            this.channelSubscribed = true;
        },

        async send() {
            const message = this.input.trim();
            if (!message || this.loading) return;

            this.messages.push({
                role: 'user',
                content: message,
            });

            this.input = '';
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());

            try {
                const response = await fetch(`${baseUrl}/api/chatbot/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        conversation_id: this.conversationId,
                        session_id: sessionId,
                        name: this.userName || null,
                        email: this.userEmail || null,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const data = await response.json();

                if (data.conversation_id) {
                    this.conversationId = data.conversation_id;
                }
            } catch (error) {
                this.loading = false;
                this.messages.push({
                    role: 'assistant',
                    content: 'Sorry, something went wrong. Please try again.',
                });
            }
        },

        formatMessage(content) {
            if (!content) return '';

            let formatted = content
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Convert markdown links [text](url) to HTML
            formatted = formatted.replace(
                /\[([^\]]+)\]\(([^)]+)\)/g,
                '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>'
            );

            // Convert **bold** to <strong>
            formatted = formatted.replace(
                /\*\*([^*]+)\*\*/g,
                '<strong>$1</strong>'
            );

            // Convert newlines to <br>
            formatted = formatted.replace(/\n/g, '<br>');

            return formatted;
        },

        scrollToBottom() {
            if (this.$refs.messages) {
                this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
            }
        },
    }));

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            injectWidget();
            Alpine.start();
        });
    } else {
        injectWidget();
        Alpine.start();
    }
})();
