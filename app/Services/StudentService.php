<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StudentService
{
    public function paginate(User $user): LengthAwarePaginator
    {
        $this->authorizeStudentAction($user, 'students.view');

        return $this->studentQuery($user)
            ->with('teacher:id,name')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();
    }

    public function teachers(): Collection
    {
        return User::role('Teacher')
            ->whereDoesntHave('roles', function (Builder $query) {
                $query->where('name', 'Admin');
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function create(array $data, User $user): Student
    {
        $this->authorizeStudentAction($user, 'students.create');

        $teacherId = $user->hasRole('Admin')
            ? (int) ($data['teacher_id'] ?? 0)
            : (int) $user->id;

        if (! $this->teacherQuery()->whereKey($teacherId)->exists()) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Please select a valid teacher.',
            ]);
        }

        $image = $data['image'] ?? null;

        unset($data['image'], $data['teacher_id']);

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('students', 'public');
        }

        try {
            return Student::create([
                ...$data,
                'teacher_id' => $teacherId,
            ]);
        } catch (\Throwable $exception) {
            if (! empty($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }

            throw $exception;
        }
    }

    public function findForUser(Student $student, User $user): Student
    {
        $this->authorizeStudentAction($user, 'students.view');

        return $this->studentQuery($user)
            ->with('teacher:id,name')
            ->findOrFail($student->id);
    }

    public function update(Student $student, array $data, User $user): Student
    {
        $this->authorizeStudentAction($user, 'students.update');

        $student = $this->studentQuery($user)->findOrFail($student->id);

        if ($user->hasRole('Admin') && isset($data['teacher_id'])) {
            $teacherId = (int) $data['teacher_id'];

            if (! $this->teacherQuery()->whereKey($teacherId)->exists()) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Please select a valid teacher.',
                ]);
            }

            $data['teacher_id'] = $teacherId;
        } else {
            unset($data['teacher_id']);
        }

        $oldImage = $student->image;
        $image = $data['image'] ?? null;
        $removeImage = (bool) ($data['remove_image'] ?? false);

        unset($data['image'], $data['remove_image']);

        if ($image instanceof UploadedFile) {
            $data['image'] = $image->store('students', 'public');
        } elseif ($removeImage) {
            $data['image'] = null;
        }

        try {
            $student->update($data);
        } catch (\Throwable $exception) {
            if (! empty($data['image'])) {
                Storage::disk('public')->delete($data['image']);
            }

            throw $exception;
        }

        if (($image instanceof UploadedFile || $removeImage) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $student;
    }

    public function delete(Student $student, User $user): void
    {
        $this->authorizeStudentAction($user, 'students.delete');

        $student = $this->studentQuery($user)->findOrFail($student->id);
        $image = $student->image;

        $student->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }
    }

    private function studentQuery(User $user): Builder
    {
        $query = Student::query();

        if (! $user->hasRole('Admin')) {
            $query->where('teacher_id', $user->id);
        }

        return $query;
    }

    private function teacherQuery(): Builder
    {
        return User::role('Teacher')
            ->whereDoesntHave('roles', function (Builder $query) {
                $query->where('name', 'Admin');
            });
    }

    private function authorizeStudentAction(User $user, string $permission): void
    {
        if (
            (! $user->hasRole('Admin') && ! $user->hasRole('Teacher'))
            || ! $user->can($permission)
        ) {
            throw new AuthorizationException;
        }
    }
}
