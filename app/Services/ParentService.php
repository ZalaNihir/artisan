<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParentService
{
    public function paginate(User $user): LengthAwarePaginator
    {
        $this->authorizeParentAction($user, 'parents.view');

        return $this->parentQuery($user)
            ->withCount(['students' => fn (Builder $query) => $this->scopeStudents($query, $user)])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();
    }

    public function studentsForUser(User $user, string $permission): Collection
    {
        $this->authorizeParentAction($user, $permission);

        return $this->scopeStudents(Student::query(), $user)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name', 'teacher_id']);
    }

    public function findForUser(StudentParent $parent, User $user, string $permission = 'parents.view'): StudentParent
    {
        $this->authorizeParentAction($user, $permission);

        return $this->parentQuery($user)
            ->with([
                'creator:id,name',
                'students' => function (BelongsToMany $query) use ($user): void {
                    if (! $user->hasRole('Admin')) {
                        $query->where('students.teacher_id', $user->getKey());
                    }

                    $query->select(['students.id', 'students.first_name', 'students.last_name', 'students.teacher_id']);
                },
            ])
            ->whereKey($parent->getKey())
            ->firstOrFail();
    }

    public function create(array $attributes, User $user): StudentParent
    {
        $this->authorizeParentAction($user, 'parents.create');

        $studentInput = $attributes['students'] ?? [];
        $associations = $this->associations(is_array($studentInput) ? $studentInput : []);
        $this->ensureStudentsAreManageable($associations, $user);
        $this->ensureTeacherHasAChild($associations, $user);

        return DB::transaction(function () use ($attributes, $associations, $user): StudentParent {
            $parent = StudentParent::create([
                ...Arr::only($attributes, $this->parentFields()),
                'created_by' => $user->getKey(),
            ]);

            $parent->students()->sync($associations);

            return $parent;
        });
    }

    public function update(StudentParent $parent, array $attributes, User $user): StudentParent
    {
        $this->authorizeParentAction($user, 'parents.update');
        $parent = $this->parentQuery($user)->whereKey($parent->getKey())->firstOrFail();
        $studentInput = $attributes['students'] ?? null;
        $associations = array_key_exists('students', $attributes)
            ? $this->associations(is_array($studentInput) ? $studentInput : [])
            : null;

        if ($associations !== null) {
            $this->ensureStudentsAreManageable($associations, $user);
            $this->ensureTeacherHasAChild($associations, $user);
        }

        return DB::transaction(function () use ($parent, $attributes, $associations, $user): StudentParent {
            $parent->fill(Arr::only($attributes, $this->parentFields()))->save();

            if ($associations !== null) {
                $parent->students()->sync($this->associationsForUser($parent, $associations, $user));
            }

            return $parent;
        });
    }

    public function delete(StudentParent $parent, User $user): void
    {
        $this->authorizeParentAction($user, 'parents.delete');
        $parent = $this->parentQuery($user)->whereKey($parent->getKey())->firstOrFail();

        $parent->delete();
    }

    private function parentQuery(User $user): Builder
    {
        return StudentParent::query()->when(
            ! $user->hasRole('Admin'),
            fn (Builder $query) => $query->whereHas('students', fn (Builder $students) => $this->scopeStudents($students, $user))
        );
    }

    private function scopeStudents(Builder $query, User $user): Builder
    {
        return $query->when(! $user->hasRole('Admin'), fn (Builder $students) => $students->where('teacher_id', $user->getKey()));
    }

    private function ensureStudentsAreManageable(array $associations, User $user): void
    {
        if ($associations === []) {
            return;
        }

        $manageableCount = $this->scopeStudents(Student::query(), $user)
            ->whereKey(array_keys($associations))
            ->count();

        if ($manageableCount !== count($associations)) {
            throw new AuthorizationException;
        }
    }

    private function ensureTeacherHasAChild(array $associations, User $user): void
    {
        if (! $user->hasRole('Admin') && $associations === []) {
            throw ValidationException::withMessages(['students' => __('Select at least one student.')]);
        }
    }

    private function associations(array $students): array
    {
        $associations = [];

        foreach ($students as $studentId => $details) {
            if (! is_array($details) || ! filter_var($details['selected'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            if (! ctype_digit((string) $studentId)) {
                throw ValidationException::withMessages(['students' => __('The selected student is invalid.')]);
            }

            $relationship = $details['relationship'] ?? null;

            if ($relationship !== null && ! is_string($relationship)) {
                throw ValidationException::withMessages(['students' => __('The selected relationship is invalid.')]);
            }

            $associations[(int) $studentId] = [
                'relationship' => filled($relationship) ? $relationship : null,
            ];
        }

        return $associations;
    }

    private function associationsForUser(StudentParent $parent, array $associations, User $user): array
    {
        if ($user->hasRole('Admin')) {
            return $associations;
        }

        $existingOtherTeacherAssociations = DB::table('parent_student')
            ->join('students', 'students.id', '=', 'parent_student.student_id')
            ->where('parent_student.parent_id', $parent->getKey())
            ->where('students.teacher_id', '!=', $user->getKey())
            ->get(['parent_student.student_id', 'parent_student.relationship'])
            ->mapWithKeys(static fn (\stdClass $association): array => [
                (int) $association->student_id => [
                    'relationship' => $association->relationship === null ? null : (string) $association->relationship,
                ],
            ])
            ->all();

        return array_replace($existingOtherTeacherAssociations, $associations);
    }

    private function authorizeParentAction(User $user, string $permission): void
    {
        if ((! $user->hasRole('Admin') && ! $user->hasRole('Teacher')) || ! $user->can($permission)) {
            throw new AuthorizationException;
        }
    }

    private function parentFields(): array
    {
        return ['first_name', 'last_name', 'email', 'phone', 'alternate_phone', 'occupation', 'address'];
    }
}
