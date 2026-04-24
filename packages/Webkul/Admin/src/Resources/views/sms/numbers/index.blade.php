<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.sms.numbers.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="text-xl font-bold dark:text-white">
                @lang('admin::app.sms.numbers.title')
            </div>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.sms.index') }}" class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    @lang('admin::app.sms.numbers.back-to-messages')
                </a>

                <button
                    type="button"
                    class="primary-button"
                    @click="$refs.manageNumbers.openCreate()"
                >
                    @lang('admin::app.sms.numbers.add-btn')
                </button>
            </div>
        </div>

        <!-- Numbers DataGrid + Modal -->
        <v-twilio-numbers ref="manageNumbers">
            <x-admin::shimmer.datagrid />
        </v-twilio-numbers>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-twilio-numbers-template"
        >
            <!-- DataGrid -->
            <x-admin::datagrid
                ref="datagrid"
                :src="route('admin.sms.numbers.index')"
            />

            <!-- Add/Edit Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, save)" ref="numberForm">
                    <x-admin::modal ref="numberModal" position="bottom-right">
                        <x-slot:header>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                @{{ isEditing ? '@lang('admin::app.sms.numbers.edit-title')' : '@lang('admin::app.sms.numbers.add-title')' }}
                            </h3>
                        </x-slot>

                        <x-slot:content>
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                                v-model="form.id"
                            />

                            <!-- Label -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.numbers.form.label')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="label"
                                    rules="required"
                                    v-model="form.label"
                                    :label="trans('admin::app.sms.numbers.form.label')"
                                    :placeholder="trans('admin::app.sms.numbers.form.label-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="label" />
                            </x-admin::form.control-group>

                            <!-- Phone Number -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.numbers.form.phone')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="phone_number"
                                    rules="required"
                                    v-model="form.phone_number"
                                    :label="trans('admin::app.sms.numbers.form.phone')"
                                    placeholder="+1234567890"
                                />

                                <x-admin::form.control-group.error control-name="phone_number" />
                            </x-admin::form.control-group>

                            <!-- Twilio Account SID -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.sms.numbers.form.sid')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="twilio_sid"
                                    v-model="form.twilio_sid"
                                    :label="trans('admin::app.sms.numbers.form.sid')"
                                    :placeholder="trans('admin::app.sms.numbers.form.sid-placeholder')"
                                />
                            </x-admin::form.control-group>

                            <!-- Twilio Auth Token -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.sms.numbers.form.token')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="password"
                                    name="twilio_token"
                                    v-model="form.twilio_token"
                                    :label="trans('admin::app.sms.numbers.form.token')"
                                    :placeholder="trans('admin::app.sms.numbers.form.token-placeholder')"
                                />
                            </x-admin::form.control-group>

                            <!-- WhatsApp Enabled -->
                            <x-admin::form.control-group>
                                <label class="flex cursor-pointer items-center gap-2.5">
                                    <input
                                        type="checkbox"
                                        name="is_whatsapp"
                                        class="peer hidden"
                                        v-model="form.is_whatsapp"
                                        :true-value="1"
                                        :false-value="0"
                                    >
                                    <span class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-600 peer-checked:text-brandColor dark:text-gray-300"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.sms.numbers.form.whatsapp-enabled')
                                    </span>
                                </label>
                            </x-admin::form.control-group>

                            <!-- Active -->
                            <x-admin::form.control-group v-if="isEditing">
                                <label class="flex cursor-pointer items-center gap-2.5">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        class="peer hidden"
                                        v-model="form.is_active"
                                        :true-value="1"
                                        :false-value="0"
                                    >
                                    <span class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-600 peer-checked:text-brandColor dark:text-gray-300"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.sms.numbers.form.active')
                                    </span>
                                </label>
                            </x-admin::form.control-group>

                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                @lang('admin::app.sms.numbers.form.sid-note')
                            </p>
                        </x-slot>

                        <x-slot:footer>
                            <x-admin::button
                                class="primary-button"
                                type="submit"
                                :title="trans('admin::app.sms.numbers.form.save-btn')"
                                ::loading="isSaving"
                                ::disabled="isSaving"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-twilio-numbers', {
                template: '#v-twilio-numbers-template',

                data() {
                    return {
                        isEditing: false,
                        isSaving: false,
                        form: this.getEmptyForm(),
                    };
                },

                methods: {
                    getEmptyForm() {
                        return {
                            id: null,
                            label: '',
                            phone_number: '',
                            twilio_sid: '',
                            twilio_token: '',
                            is_whatsapp: 0,
                            is_active: 1,
                        };
                    },

                    openCreate() {
                        this.isEditing = false;
                        this.form = this.getEmptyForm();
                        this.$refs.numberModal.toggle();
                    },

                    openEdit(data) {
                        this.isEditing = true;
                        this.form = { ...data, twilio_token: '' };
                        this.$refs.numberModal.toggle();
                    },

                    save(params, { resetForm, setErrors }) {
                        this.isSaving = true;

                        const url = this.form.id
                            ? '{{ route("admin.sms.numbers.update", ":id") }}'.replace(':id', this.form.id)
                            : '{{ route("admin.sms.numbers.store") }}';

                        const method = this.form.id ? 'put' : 'post';

                        this.$axios[method](url, this.form)
                            .then(response => {
                                this.$refs.datagrid.get();
                                this.$emitter.emit('add-flash', { type: 'success', message: response.data?.message });
                                resetForm();
                                this.form = this.getEmptyForm();
                            })
                            .catch(error => {
                                if (error?.response?.status == 422) {
                                    setErrors(error.response.data.errors);
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message });
                                }
                            })
                            .finally(() => {
                                this.$refs.numberModal.close();
                                this.isSaving = false;
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
