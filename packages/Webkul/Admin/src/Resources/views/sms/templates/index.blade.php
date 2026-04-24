<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.sms.templates.title')
    </x-slot>

    <div class="flex flex-col gap-4">
        <!-- Header -->
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="text-xl font-bold dark:text-white">
                @lang('admin::app.sms.templates.title')
            </div>

            <div class="flex items-center gap-x-2.5">
                <a href="{{ route('admin.sms.index') }}" class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800">
                    @lang('admin::app.sms.templates.back-to-messages')
                </a>

                <button
                    type="button"
                    class="primary-button"
                    @click="$refs.manageTemplates.openCreate()"
                >
                    @lang('admin::app.sms.templates.add-btn')
                </button>
            </div>
        </div>

        <!-- Templates DataGrid + Modal -->
        <v-sms-templates ref="manageTemplates">
            <x-admin::shimmer.datagrid />
        </v-sms-templates>
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-sms-templates-template"
        >
            <!-- DataGrid -->
            <x-admin::datagrid
                ref="datagrid"
                :src="route('admin.sms.templates.index')"
            />

            <!-- Add/Edit Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, save)" ref="templateForm">
                    <x-admin::modal ref="templateModal" position="bottom-right">
                        <x-slot:header>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                @{{ isEditing ? '@lang('admin::app.sms.templates.edit-title')' : '@lang('admin::app.sms.templates.add-title')' }}
                            </h3>
                        </x-slot>

                        <x-slot:content>
                            <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                                v-model="form.id"
                            />

                            <!-- Name -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.templates.form.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="name"
                                    rules="required"
                                    v-model="form.name"
                                    :label="trans('admin::app.sms.templates.form.name')"
                                    :placeholder="trans('admin::app.sms.templates.form.name-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="name" />
                            </x-admin::form.control-group>

                            <!-- Channel -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.templates.form.channel')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="channel"
                                    rules="required"
                                    v-model="form.channel"
                                    :label="trans('admin::app.sms.templates.form.channel')"
                                >
                                    <option value="both">Both (SMS & WhatsApp)</option>
                                    <option value="sms">SMS Only</option>
                                    <option value="whatsapp">WhatsApp Only</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="channel" />
                            </x-admin::form.control-group>

                            <!-- Body -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.sms.templates.form.body')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="body"
                                    rules="required"
                                    rows="6"
                                    v-model="form.body"
                                    :label="trans('admin::app.sms.templates.form.body')"
                                    :placeholder="trans('admin::app.sms.templates.form.body-placeholder')"
                                />

                                <x-admin::form.control-group.error control-name="body" />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @{{ form.body.length }} / 1600 characters
                                </p>
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
                                        @lang('admin::app.sms.templates.form.active')
                                    </span>
                                </label>
                            </x-admin::form.control-group>
                        </x-slot>

                        <x-slot:footer>
                            <x-admin::button
                                class="primary-button"
                                type="submit"
                                :title="trans('admin::app.sms.templates.form.save-btn')"
                                ::loading="isSaving"
                                ::disabled="isSaving"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-sms-templates', {
                template: '#v-sms-templates-template',

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
                            name: '',
                            body: '',
                            channel: 'both',
                            is_active: 1,
                        };
                    },

                    openCreate() {
                        this.isEditing = false;
                        this.form = this.getEmptyForm();
                        this.$refs.templateModal.toggle();
                    },

                    openEdit(data) {
                        this.isEditing = true;
                        this.form = { ...data };
                        this.$refs.templateModal.toggle();
                    },

                    save(params, { resetForm, setErrors }) {
                        this.isSaving = true;

                        const url = this.form.id
                            ? '{{ route("admin.sms.templates.update", ":id") }}'.replace(':id', this.form.id)
                            : '{{ route("admin.sms.templates.store") }}';

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
                                this.$refs.templateModal.close();
                                this.isSaving = false;
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
