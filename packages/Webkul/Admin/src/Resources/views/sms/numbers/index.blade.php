<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.sms.numbers.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <div class="text-xl font-bold dark:text-white">
                    @lang('admin::app.sms.numbers.title')
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.sms.index') }}" class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    @lang('admin::app.sms.numbers.back-to-messages')
                </a>

                <button
                    type="button"
                    class="primary-button"
                    @click="$refs.numberSettings.openModal()"
                >
                    @lang('admin::app.sms.numbers.add-btn')
                </button>
            </div>
        </div>

        <v-number-settings ref="numberSettings">
            <x-admin::shimmer.datagrid />
        </v-number-settings>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="number-settings-template"
        >
            <x-admin::datagrid
                :src="route('admin.sms.numbers.index')"
                ref="datagrid"
            >
                <template #body="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <template v-if="isLoading">
                        <x-admin::shimmer.datagrid.table.body />
                    </template>

                    <template v-else>
                        <div
                            v-for="record in available.records"
                            class="row grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"
                            :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                        >
                            <p>@{{ record.id }}</p>
                            <p>@{{ record.label }}</p>
                            <p>@{{ record.phone_number }}</p>
                            <p v-html="record.is_whatsapp"></p>
                            <p v-html="record.is_active"></p>
                            <p>@{{ record.messages_count }}</p>
                            <p>@{{ record.created_at }}</p>

                            <div class="flex justify-end">
                                <a @click="selectedRecord=true; editModal(record.actions.find(action => action.index === 'edit')?.url)">
                                    <span :class="record.actions.find(action => action.index === 'edit')?.icon" class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></span>
                                </a>

                                <a @click="performAction(record.actions.find(action => action.index === 'delete'))">
                                    <span :class="record.actions.find(action => action.index === 'delete')?.icon" class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></span>
                                </a>
                            </div>
                        </div>
                    </template>
                </template>
            </x-admin::datagrid>

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form @submit="handleSubmit($event, updateOrCreate)">
                    <x-admin::modal ref="numberModal">
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @{{ selectedRecord ? "@lang('admin::app.sms.numbers.edit-title')" : "@lang('admin::app.sms.numbers.add-title')" }}
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <x-admin::form.control-group.control type="hidden" name="id" />

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.numbers.form.label')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="text" id="label" name="label" rules="required" :label="trans('admin::app.sms.numbers.form.label')" :placeholder="trans('admin::app.sms.numbers.form.label-placeholder')" />
                                <x-admin::form.control-group.error control-name="label" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.numbers.form.phone')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="text" id="phone_number" name="phone_number" rules="required" :label="trans('admin::app.sms.numbers.form.phone')" placeholder="+1234567890" />
                                <x-admin::form.control-group.error control-name="phone_number" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>@lang('admin::app.sms.numbers.form.sid')</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="text" id="twilio_sid" name="twilio_sid" :label="trans('admin::app.sms.numbers.form.sid')" :placeholder="trans('admin::app.sms.numbers.form.sid-placeholder')" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>@lang('admin::app.sms.numbers.form.token')</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="password" id="twilio_token" name="twilio_token" :label="trans('admin::app.sms.numbers.form.token')" :placeholder="trans('admin::app.sms.numbers.form.token-placeholder')" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>@lang('admin::app.sms.numbers.form.whatsapp-enabled')</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="select" id="is_whatsapp" name="is_whatsapp" :label="trans('admin::app.sms.numbers.form.whatsapp-enabled')">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">@lang('admin::app.sms.numbers.form.sid-note')</p>
                        </x-slot>

                        <x-slot:footer>
                            <x-admin::button button-type="submit" class="primary-button justify-center" :title="trans('admin::app.sms.numbers.form.save-btn')" ::loading="isProcessing" ::disabled="isProcessing" />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-number-settings', {
                template: '#number-settings-template',

                data() {
                    return {
                        isProcessing: false,
                        selectedRecord: false,
                    };
                },

                computed: {
                    gridsCount() {
                        if (! this.$refs.datagrid?.available) return 1;

                        let count = this.$refs.datagrid.available.columns.length;

                        if (this.$refs.datagrid.available.actions.length) {
                            ++count;
                        }

                        if (this.$refs.datagrid.available.massActions.length) {
                            ++count;
                        }

                        return count;
                    },
                },

                methods: {
                    openModal() {
                        this.selectedRecord = false;
                        this.$refs.numberModal.toggle();
                    },

                    updateOrCreate(params, {resetForm, setErrors}) {
                        this.isProcessing = true;

                        this.$axios.post(params.id ? "{{ route('admin.sms.numbers.update', ':id') }}".replace(':id', params.id) : "{{ route('admin.sms.numbers.store') }}", {
                            ...params,
                            _method: params.id ? 'put' : 'post'
                        }).then(response => {
                            this.isProcessing = false;
                            this.$refs.numberModal.toggle();
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                            this.$refs.datagrid.get();
                            resetForm();
                        }).catch(error => {
                            this.isProcessing = false;
                            if (error.response.status === 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                    },

                    editModal(url) {
                        this.$axios.get(url)
                            .then(response => {
                                this.$refs.modalForm.setValues(response.data.data);
                                this.$refs.numberModal.toggle();
                            })
                            .catch(error => {});
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
