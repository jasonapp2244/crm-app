<!-- SMS/WhatsApp Messaging Stats -->
<v-dashboard-sms-stats>
    <div class="light-shimmer-bg dark:shimmer flex h-[180px] w-full rounded-lg"></div>
</v-dashboard-sms-stats>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-sms-stats-template"
    >
        <div class="rounded-lg border border-gray-300 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-300 p-4 dark:border-gray-800">
                <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                    @lang('admin::app.sms.index.title')
                </p>

                <a
                    href="{{ route('admin.sms.index') }}"
                    class="text-sm text-brandColor hover:underline"
                >
                    View All
                </a>
            </div>

            <div class="grid grid-cols-3 gap-4 p-4" v-if="! isLoading">
                <div class="flex flex-col items-center gap-1">
                    <p class="text-2xl font-bold text-green-600">@{{ stats.total_sent || 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">@lang('admin::app.sms.index.stats.total-sent')</p>
                </div>

                <div class="flex flex-col items-center gap-1">
                    <p class="text-2xl font-bold text-blue-600">@{{ stats.total_received || 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">@lang('admin::app.sms.index.stats.total-received')</p>
                </div>

                <div class="flex flex-col items-center gap-1">
                    <p class="text-2xl font-bold text-red-600">@{{ stats.total_failed || 0 }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">@lang('admin::app.sms.index.stats.total-failed')</p>
                </div>
            </div>

            <div class="flex items-center justify-center p-8" v-else>
                <x-admin::spinner />
            </div>

            <div class="border-t border-gray-300 p-4 dark:border-gray-800" v-if="! isLoading">
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        <span class="text-gray-600 dark:text-gray-300">Today Sent: @{{ stats.today_sent || 0 }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <span class="text-gray-600 dark:text-gray-300">Today Received: @{{ stats.today_received || 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-dashboard-sms-stats', {
            template: '#v-dashboard-sms-stats-template',

            data() {
                return {
                    isLoading: true,
                    stats: {},
                };
            },

            mounted() {
                this.loadStats();
            },

            methods: {
                loadStats() {
                    this.isLoading = true;

                    this.$axios.get('{{ route("admin.sms.stats") }}')
                        .then(response => {
                            this.stats = response.data;
                        })
                        .catch(error => {})
                        .finally(() => {
                            this.isLoading = false;
                        });
                },
            },
        });
    </script>
@endPushOnce
