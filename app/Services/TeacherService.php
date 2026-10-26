<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class TeacherService
{
    public function paginate(): LengthAwarePaginator
    {
        return User::query()
            ->role('Teacher')
            ->whereDoesntHave('roles', fn (Builder $query) => $query->where('name', 'Admin'))
            ->withCount('students')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15);
    }

    public function forEditing(User $teacher): User
    {
        $this->ensureManageable($teacher);

        return $teacher;
    }

    public function create(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $teacher = User::create(Arr::only($attributes, [
                'name',
                'email',
                'password',
                ...$this->profileFields(),
            ]));
            $teacher->assignRole(Role::findOrCreate('Teacher', 'web'));

            return $teacher;
        });
    }

    public function update(User $teacher, array $attributes): void
    {
        $this->ensureManageable($teacher);

        $teacher->fill(Arr::only($attributes, [
            'name',
            'email',
            ...$this->profileFields(),
        ]));

        if (filled($attributes['password'] ?? null)) {
            $teacher->password = $attributes['password'];
        }

        if ($teacher->isDirty('email')) {
            $teacher->email_verified_at = null;
        }

        $teacher->save();
    }

    public function delete(User $teacher): void
    {
        $this->ensureManageable($teacher);
        $teacher->delete();
    }

    private function ensureManageable(User $teacher): void
    {
        if ($teacher->hasRole('Admin')) {
            throw new AuthorizationException;
        }

        if (! $teacher->hasRole('Teacher')) {
            throw (new ModelNotFoundException)->setModel(User::class, [$teacher->getKey()]);
        }
    }

    private function profileFields(): array
    {
        return ['employee_id', 'phone', 'address', 'qualification', 'joining_date'];
    }
}
