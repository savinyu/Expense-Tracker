<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('messages.delete_account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.delete_account_desc') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('messages.delete_account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ __('messages.delete_account_confirm') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.delete_account_confirm_desc') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" :value="__('messages.password')" class="sr-only" />
                <input id="password" name="password" type="password"
                       placeholder="{{ __('messages.password') }}"
                       class="mt-1 w-3/4 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('messages.cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('messages.delete_account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
