<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\User;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\View\View;

class TeacherController extends RoutingController
{
    public function __construct(protected TeacherService $teachers)
    {
        $this->middleware('can:teachers.view')->only('index');
        $this->middleware('can:teachers.create')->only(['create', 'store']);
        $this->middleware('can:teachers.update')->only(['edit', 'update']);
        $this->middleware('can:teachers.delete')->only('destroy');
    }

    public function index(): View
    {
        return view('admin.teachers.index', ['teachers' => $this->teachers->paginate()]);
    }

    public function create(): View
    {
        return view('admin.teachers.create');
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $this->teachers->create($request->validated());

        return to_route('admin.teachers.index')->with('success', __('Teacher account created.'));
    }

    public function edit(User $teacher): View
    {
        return view('admin.teachers.edit', ['teacher' => $this->teachers->forEditing($teacher)]);
    }

    public function update(UpdateTeacherRequest $request, User $teacher): RedirectResponse
    {
        $this->teachers->update($teacher, $request->validated());

        return to_route('admin.teachers.index')->with('success', __('Teacher account updated.'));
    }

    public function destroy(User $teacher): RedirectResponse
    {
        $this->teachers->delete($teacher);

        return to_route('admin.teachers.index')->with('success', __('Teacher account deleted.'));
    }
}
