<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\User;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\View\View;

class TeacherController extends RoutingController
{
    public function __construct()
    {
        $this->middleware('can:teachers.view')->only('index');
        $this->middleware('can:teachers.create')->only(['create', 'store']);
        $this->middleware('can:teachers.update')->only(['edit', 'update']);
        $this->middleware('can:teachers.delete')->only('destroy');
    }

    public function index(TeacherService $teachers): View
    {
        return view('admin.teachers.index', ['teachers' => $teachers->paginate()]);
    }

    public function create(): View
    {
        return view('admin.teachers.create');
    }

    public function store(StoreTeacherRequest $request, TeacherService $teachers): RedirectResponse
    {
        $teachers->create($request->validated());

        return to_route('admin.teachers.index')->with('success', __('Teacher account created.'));
    }

    public function edit(User $teacher, TeacherService $teachers): View
    {
        return view('admin.teachers.edit', ['teacher' => $teachers->forEditing($teacher)]);
    }

    public function update(UpdateTeacherRequest $request, User $teacher, TeacherService $teachers): RedirectResponse
    {
        $teachers->update($teacher, $request->validated());

        return to_route('admin.teachers.index')->with('success', __('Teacher account updated.'));
    }

    public function destroy(User $teacher, TeacherService $teachers): RedirectResponse
    {
        $teachers->delete($teacher);

        return to_route('admin.teachers.index')->with('success', __('Teacher account deleted.'));
    }
}
