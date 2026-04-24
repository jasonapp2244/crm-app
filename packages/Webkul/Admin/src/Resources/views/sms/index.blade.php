<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.sms.index.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.sms.index.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.sms.templates.index') }}" class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    @lang('admin::app.sms.index.manage-templates')
                </a>

                <a href="{{ route('admin.sms.numbers.index') }}" class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    Manage Numbers
                </a>

                <button
                    type="button"
                    class="primary-button"
                    @click="$refs.composeSMS.toggleModal()"
                >
                    @lang('admin::app.sms.index.compose-btn')
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <div class="flex flex-col gap-1 rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">@lang('admin::app.sms.index.stats.total-sent')</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total_sent'] }}</p>
            </div>

            <div class="flex flex-col gap-1 rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">@lang('admin::app.sms.index.stats.total-received')</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total_received'] }}</p>
            </div>

            <div class="flex flex-col gap-1 rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">@lang('admin::app.sms.index.stats.total-failed')</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['total_failed'] }}</p>
            </div>

            <div class="flex flex-col gap-1 rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">@lang('admin::app.sms.index.stats.today-sent')</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['today_sent'] }}</p>
            </div>

            <div class="flex flex-col gap-1 rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium text-gray-600 dark:text-gray-300">@lang('admin::app.sms.index.stats.today-received')</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['today_received'] }}</p>
            </div>
        </div>

        <!-- SMS Compose + DataGrid -->
        <v-sms ref="composeSMS">
            <x-admin::shimmer.datagrid />
        </v-sms>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-sms-template"
        >
            <!-- DataGrid -->
            <x-admin::datagrid
                ref="datagrid"
                :src="route('admin.sms.index')"
            />

            <!-- Compose SMS Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, send)" ref="smsForm">
                    <x-admin::modal
                        ref="toggleComposeModal"
                        position="bottom-right"
                    >
                        <x-slot:header>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('admin::app.sms.index.compose.title')
                            </h3>
                        </x-slot>

                        <x-slot:content>
                            <!-- Template Selector -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.sms.index.compose.template')
                                </x-admin::form.control-group.label>

                                <select
                                    v-model="selectedTemplateId"
                                    @change="applyTemplate"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                >
                                    <option value="">-- @lang('admin::app.sms.index.compose.select-template') --</option>
                                    <option v-for="tpl in templates" :key="tpl.id" :value="tpl.id">
                                        @{{ tpl.name }} (@{{ tpl.channel }})
                                    </option>
                                </select>
                            </x-admin::form.control-group>

                            <!-- From Number -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    From Number
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="twilio_number_id"
                                    id="twilio_number_id"
                                    v-model="form.twilio_number_id"
                                    label="From Number"
                                >
                                    <option value="">-- Default (.env) --</option>
                                    @foreach($activeNumbers as $number)
                                        <option value="{{ $number->id }}">
                                            {{ $number->label }} ({{ $number->phone_number }})
                                            @if($number->is_whatsapp) [WhatsApp] @endif
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Channel -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.index.compose.channel')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="channel"
                                    id="channel"
                                    rules="required"
                                    v-model="form.channel"
                                    :label="trans('admin::app.sms.index.compose.channel')"
                                >
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="channel" />
                            </x-admin::form.control-group>

                            <!-- To (Multiple) -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.index.compose.to')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="to"
                                    id="to"
                                    rules="required"
                                    rows="3"
                                    v-model="form.to"
                                    :label="trans('admin::app.sms.index.compose.to')"
                                    placeholder="Enter phone numbers separated by commas, e.g.: +1234567890, +0987654321"
                                />

                                <x-admin::form.control-group.error control-name="to" />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Separate multiple numbers with commas. Each number will receive the message individually.
                                </p>
                            </x-admin::form.control-group>

                            <!-- Message Body -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.index.compose.message')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="body"
                                    id="body"
                                    rules="required"
                                    rows="5"
                                    v-model="form.body"
                                    :label="trans('admin::app.sms.index.compose.message')"
                                    :placeholder="trans('admin::app.sms.index.compose.message-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="body" />
                            </x-admin::form.control-group>

                            <!-- Character Count + Recipient Count -->
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ form.body.length }} / 1600 characters
                                </p>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @{{ recipientCount }} recipient(s)
                                </p>
                            </div>

                            <!-- Schedule -->
                            <x-admin::form.control-group class="mt-3">
                                <label class="flex cursor-pointer items-center gap-2.5 mb-2">
                                    <input
                                        type="checkbox"
                                        class="peer hidden"
                                        v-model="isScheduled"
                                    >
                                    <span class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-600 peer-checked:text-brandColor dark:text-gray-300"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.sms.index.compose.schedule-message')
                                    </span>
                                </label>

                                <div v-if="isScheduled">
                                    <x-admin::form.control-group.label class="required">
                                        @lang('admin::app.sms.index.compose.schedule-at')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="datetime-local"
                                        name="scheduled_at"
                                        v-model="form.scheduled_at"
                                        :rules="isScheduled ? 'required' : ''"
                                        :label="trans('admin::app.sms.index.compose.schedule-at')"
                                    />

                                    <x-admin::form.control-group.error control-name="scheduled_at" />

                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @lang('admin::app.sms.index.compose.schedule-note')
                                    </p>
                                </div>
                            </x-admin::form.control-group>
                        </x-slot>

                        <x-slot:footer>
                            <div class="flex w-full items-center justify-end gap-4">
                                <x-admin::button
                                    class="primary-button"
                                    type="submit"
                                    ::title="isScheduled ? '@lang('admin::app.sms.index.compose.schedule-btn')' : '@lang('admin::app.sms.index.compose.send-btn')'"
                                    ::loading="isSending"
                                    ::disabled="isSending"
                                />
                            </div>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-sms', {
                template: '#v-sms-template',

                data() {
                    return {
                        isSending: false,
                        isScheduled: false,
                        selectedTemplateId: '',
                        templates: [],

                        form: {
                            twilio_number_id: '',
                            channel: 'sms',
                            to: '',
                            body: '',
                            scheduled_at: '',
                            template_id: '',
                        },
                    };
                },

                computed: {
                    recipientCount() {
                        if (! this.form.to.trim()) return 0;

                        return this.form.to.split(',').filter(n => n.trim()).length;
                    },
                },

                mounted() {
                    this.loadTemplates();
                },

                methods: {
                    toggleModal() {
                        this.$refs.toggleComposeModal.toggle();
                    },

                    loadTemplates() {
                        this.$axios.get('{{ route("admin.sms.templates.active") }}')
                            .then(response => {
                                this.templates = response.data?.data || [];
                            })
                            .catch(() => {});
                    },

                    applyTemplate() {
                        if (! this.selectedTemplateId) {
                            this.form.template_id = '';
                            return;
                        }

                        const template = this.templates.find(t => t.id == this.selectedTemplateId);

                        if (template) {
                            this.form.body = template.body;
                            this.form.template_id = template.id;

                            if (template.channel !== 'both') {
                                this.form.channel = template.channel;
                            }
                        }
                    },

                    send(params, { resetForm, setErrors }) {
                        this.isSending = true;

                        // Clear scheduled_at if not scheduling
                        if (! this.isScheduled) {
                            this.form.scheduled_at = '';
                        }

                        this.$axios.post('{{ route("admin.sms.store") }}', this.form)
                            .then(response => {
                                this.$refs.datagrid.get();

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data?.message });

                                this.resetForm();
                                resetForm();
                            })
                            .catch(error => {
                                if (error?.response?.status == 422) {
                                    if (error.response.data.errors) {
                                        setErrors(error.response.data.errors);
                                    } else {
                                        this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                                    }
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Something went wrong' });
                                }
                            })
                            .finally(() => {
                                this.$refs.toggleComposeModal.close();
                                this.isSending = false;
                            });
                    },

                    resetForm() {
                        this.form = {
                            twilio_number_id: '',
                            channel: 'sms',
                            to: '',
                            body: '',
                            scheduled_at: '',
                            template_id: '',
                        };

                        this.isScheduled = false;
                        this.selectedTemplateId = '';
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
