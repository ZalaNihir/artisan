<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 md:p-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ __('Dashboard') }}</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ match ($role) {
                    'admin' => __('System overview'),
                    'teacher' => __('Your teaching overview'),
                    default => __('Application overview'),
                } }}
            </p>
        </div>

        @if ($role === 'admin')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @can('teachers.view')
                    <x-dashboard.card :label="__('Total Teachers')" :count="$counts['teachers']" :description="__('Manage teacher accounts')" :href="route('admin.teachers.index')" icon="users" />
                @endcan
                @can('students.view')
                    <x-dashboard.card :label="__('Total Students')" :count="$counts['students']" :description="__('Browse all students')" :href="route('students.index')" icon="academic-cap" />
                @endcan
                @can('parents.view')
                    <x-dashboard.card :label="__('Total Parents')" :count="$counts['parents']" :description="__('Browse all parents and guardians')" :href="route('parents.index')" icon="user-group" />
                @endcan
            </div>
        @elseif ($role === 'teacher')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @can('students.view')
                    <x-dashboard.card :label="__('My Students')" :count="$counts['students']" :description="__('View your student list')" :href="route('students.index')" icon="academic-cap" />
                @endcan
                @can('parents.view')
                    <x-dashboard.card :label="__('My Parents')" :count="$counts['parents']" :description="__('View parents of your students')" :href="route('parents.index')" icon="user-group" />
                @endcan
                @can('announcements.view')
                    <x-dashboard.card :label="__('Unread Notifications')" :count="$counts['unread_notifications']" :description="__('Read Admin announcements for teachers')" :href="route('teacher.notifications.index')" icon="bell" />
                @endcan
            </div>
        @else
            <div class="rounded-xl border border-zinc-200 bg-white p-6 text-sm text-zinc-600 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                {{ __('Welcome to your dashboard.') }}
            </div>
        @endif
    </div>
</x-layouts::app>
