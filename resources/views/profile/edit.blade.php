<x-app-layout>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 space-y-6">

            <div class="mb-6">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('messages.profile_settings') }}</h1>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">{{ __('messages.profile_settings_desc') }}</p>
            </div>
            <div class="p-6 sm:p-8 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="max-w-xl">
                    @include('profile.partials.roommate-connection')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-8 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
