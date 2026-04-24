<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.sms.conversation.title') - {{ $person->name }}
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.sms.index') }}" class="text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white">
                    <i class="icon-arrow-left text-2xl"></i>
                </a>

                <div class="flex flex-col gap-0.5">
                    <div class="text-xl font-bold dark:text-white">
                        {{ $person->name }}
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $messages->count() }} messages
                        @if($person->contact_numbers)
                            @php
                                $numbers = is_string($person->contact_numbers) ? json_decode($person->contact_numbers, true) : $person->contact_numbers;
                            @endphp
                            @if(is_array($numbers))
                                &middot; {{ collect($numbers)->pluck('value')->filter()->implode(', ') }}
                            @endif
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <v-conversation>
            <div class="flex items-center justify-center p-12">
                <x-admin::spinner />
            </div>
        </v-conversation>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-conversation-template"
        >
            <div class="flex flex-col rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
                <!-- Messages Area -->
                <div
                    class="flex flex-col gap-3 overflow-y-auto p-4"
                    style="max-height: 60vh; min-height: 300px;"
                    ref="messagesContainer"
                >
                    <template v-if="allMessages.length === 0">
                        <div class="flex flex-1 items-center justify-center">
                            <p class="text-gray-500 dark:text-gray-400">@lang('admin::app.sms.conversation.no-messages')</p>
                        </div>
                    </template>

                    <template v-else>
                        <div
                            v-for="msg in allMessages"
                            :key="msg.id"
                            class="flex"
                            :class="msg.direction === 'outbound' ? 'justify-end' : 'justify-start'"
                        >
                            <div
                                class="max-w-[70%] rounded-2xl px-4 py-2"
                                :class="msg.direction === 'outbound'
                                    ? 'bg-brandColor text-white rounded-br-sm'
                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 rounded-bl-sm'"
                            >
                                <!-- Message Body -->
                                <p class="text-sm whitespace-pre-wrap">@{{ msg.body }}</p>

                                <!-- Meta -->
                                <div
                                    class="mt-1 flex items-center gap-2"
                                    :class="msg.direction === 'outbound' ? 'justify-end text-blue-100' : 'text-gray-400 dark:text-gray-500'"
                                >
                                    <span class="text-[10px]">@{{ msg.created_at }}</span>
                                    <span class="text-[10px] uppercase">@{{ msg.channel }}</span>

                                    <span v-if="msg.direction === 'outbound'" class="text-[10px]">
                                        @{{ msg.status ? msg.status.charAt(0).toUpperCase() + msg.status.slice(1) : '' }}
                                    </span>

                                    <span v-if="msg.twilio_number_label" class="text-[10px]">
                                        via @{{ msg.twilio_number_label }}
                                    </span>
                                </div>

                                <p
                                    v-if="msg.status === 'failed' && msg.error_message"
                                    class="mt-1 text-[10px] text-red-300"
                                >
                                    @{{ msg.error_message }}
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Reply Box -->
                <div class="border-t border-gray-300 p-4 dark:border-gray-800">
                    <!-- Template Selector -->
                    <div v-if="templates.length" class="mb-3 flex items-center gap-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Template:</label>
                        <select
                            v-model="selectedTemplateId"
                            @change="applyTemplate"
                            class="flex-1 rounded-md border border-gray-300 px-2 py-1 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <option value="">-- Select Template --</option>
                            <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">
                                @{{ tpl.name }}
                            </option>
                        </select>
                    </div>

                    <form @submit.prevent="sendReply">
                        <div class="flex items-center gap-2">
                            <!-- From Number -->
                            <select
                                v-model="replyForm.twilio_number_id"
                                class="w-[180px] rounded-md border border-gray-300 px-2 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                            >
                                <option value="">Default</option>
                                @foreach($activeNumbers as $number)
                                    <option value="{{ $number->id }}">
                                        {{ $number->label }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Channel -->
                            <select
                                v-model="replyForm.channel"
                                class="w-[110px] rounded-md border border-gray-300 px-2 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                            >
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>

                            <!-- Message Input -->
                            <input
                                type="text"
                                v-model="replyForm.body"
                                class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                placeholder="@lang('admin::app.sms.conversation.type-message')"
                                @keydown.enter.prevent="sendReply"
                            >

                            <!-- Send -->
                            <button
                                type="submit"
                                class="primary-button px-4 py-2"
                                :disabled="isSending || ! replyForm.body.trim()"
                            >
                                <span v-if="isSending">...</span>
                                <span v-else>@lang('admin::app.sms.conversation.send-btn')</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-conversation', {
                template: '#v-conversation-template',

                data() {
                    return {
                        isSending: false,
                        selectedTemplateId: '',
                        templates: [],
                        pollingInterval: null,
                        lastMessageId: {{ $messages->last()?->id ?? 0 }},

                        allMessages: @json($messages->map(fn ($msg) => [
                            'id'                   => $msg->id,
                            'from'                 => $msg->from,
                            'to'                   => $msg->to,
                            'body'                 => $msg->body,
                            'direction'            => $msg->direction,
                            'status'               => $msg->status,
                            'channel'              => $msg->channel,
                            'created_at'           => $msg->created_at?->format('M d, h:i A'),
                            'error_message'        => $msg->error_message,
                            'twilio_number_label'  => $msg->twilioNumber?->label,
                        ])),

                        replyForm: {
                            twilio_number_id: '',
                            channel: 'sms',
                            to: '{{ collect(is_string($person->contact_numbers) ? json_decode($person->contact_numbers, true) : $person->contact_numbers)->pluck("value")->filter()->first() ?? "" }}',
                            body: '',
                            person_id: {{ $person->id }},
                        },
                    };
                },

                mounted() {
                    this.scrollToBottom();
                    this.loadTemplates();
                    this.startPolling();
                },

                beforeUnmount() {
                    this.stopPolling();
                },

                methods: {
                    scrollToBottom() {
                        this.$nextTick(() => {
                            const container = this.$refs.messagesContainer;

                            if (container) {
                                container.scrollTop = container.scrollHeight;
                            }
                        });
                    },

                    loadTemplates() {
                        this.$axios.get('{{ route("admin.sms.templates.active") }}')
                            .then(response => {
                                this.templates = response.data?.data || [];
                            })
                            .catch(() => {});
                    },

                    applyTemplate() {
                        if (! this.selectedTemplateId) return;

                        const template = this.templates.find(t => t.id == this.selectedTemplateId);

                        if (template) {
                            this.replyForm.body = template.body;

                            if (template.channel !== 'both') {
                                this.replyForm.channel = template.channel;
                            }
                        }
                    },

                    startPolling() {
                        this.pollingInterval = setInterval(() => {
                            this.fetchNewMessages();
                        }, 5000);
                    },

                    stopPolling() {
                        if (this.pollingInterval) {
                            clearInterval(this.pollingInterval);
                            this.pollingInterval = null;
                        }
                    },

                    fetchNewMessages() {
                        this.$axios.get('{{ route("admin.sms.conversation.poll", $person->id) }}', {
                            params: { after_id: this.lastMessageId }
                        })
                        .then(response => {
                            const newMessages = response.data?.data || [];

                            if (newMessages.length > 0) {
                                this.allMessages.push(...newMessages);
                                this.lastMessageId = newMessages[newMessages.length - 1].id;
                                this.scrollToBottom();
                            }
                        })
                        .catch(() => {});
                    },

                    sendReply() {
                        if (! this.replyForm.body.trim() || ! this.replyForm.to) return;

                        this.isSending = true;

                        this.$axios.post('{{ route("admin.sms.store") }}', this.replyForm)
                            .then(response => {
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data?.message });

                                this.replyForm.body = '';
                                this.selectedTemplateId = '';

                                // Fetch new messages immediately
                                this.fetchNewMessages();
                            })
                            .catch(error => {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || 'Failed to send'
                                });
                            })
                            .finally(() => {
                                this.isSending = false;
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
