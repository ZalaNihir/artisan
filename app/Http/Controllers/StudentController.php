<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(protected StudentService $students)
    {
        $this->middleware('can:students.view')->only(['index', 'show']);
        $this->middleware('can:students.create')->only(['create', 'store']);
        $this->middleware('can:students.update')->only(['edit', 'update']);
        $this->middleware('can:students.delete')->only('destroy');
    }

    public function index(Request $request): View
    {
        return view('students.index', [
            'students' => $this->students->paginate($request->user()),
        ]);
    }

    public function create(Request $request): View
    {
        $teachers = $request->user()->hasRole('Admin')
            ? $this->students->teachers()
            : collect();

        return view('students.create', compact('teachers'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $student = $this->students->create(
            $request->validated(),
            $request->user()
        );

        return to_route('students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    public function show(Request $request, Student $student): View
    {
        return view('students.show', [
            'student' => $this->students->findForUser(
                $student,
                $request->user()
            ),
        ]);
    }

    public function edit(Request $request, Student $student): View
    {
        $user = $request->user();

        return view('students.edit', [
            'student' => $this->students->findForUser($student, $user),
            'teachers' => $user->hasRole('Admin')
                ? $this->students->teachers()
                : collect(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse {
        $this->students->update(
            $student,
            $request->validated(),
            $request->user()
        );

        return to_route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->students->delete($student, $request->user());

        return to_route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
