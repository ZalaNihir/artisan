<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParentRequest;
use App\Http\Requests\UpdateParentRequest;
use App\Models\StudentParent;
use App\Services\ParentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ParentController extends Controller
{
    public function __construct(protected ParentService $parents)
    {
        $this->middleware('can:parents.view')->only(['index', 'show']);
        $this->middleware('can:parents.create')->only(['create', 'store']);
        $this->middleware('can:parents.update')->only(['edit', 'update']);
        $this->middleware('can:parents.delete')->only('destroy');
    }

    public function index(Request $request): View
    {
        return view('parents.index', [
            'parents' => $this->parents->paginate($request->user()),
        ]);
    }

    public function create(Request $request): View
    {
        return view('parents.create', [
            'students' => $this->parents->studentsForUser($request->user(), 'parents.create'),
        ]);
    }

    public function store(StoreParentRequest $request): RedirectResponse
    {
        $parent = $this->parents->create($request->validated(), $request->user());

        return to_route('parents.show', $parent)->with('success', __('Parent created successfully.'));
    }

    public function show(Request $request, StudentParent $parent): View
    {
        return view('parents.show', [
            'parent' => $this->parents->findForUser($parent, $request->user()),
        ]);
    }

    public function edit(Request $request, StudentParent $parent): View
    {
        $user = $request->user();

        return view('parents.edit', [
            'parent' => $this->parents->findForUser($parent, $user, 'parents.update'),
            'students' => $this->parents->studentsForUser($user, 'parents.update'),
        ]);
    }

    public function update(UpdateParentRequest $request, StudentParent $parent): RedirectResponse
    {
        $this->parents->update($parent, $request->validated(), $request->user());

        return to_route('parents.show', $parent)->with('success', __('Parent updated successfully.'));
    }

    public function destroy(Request $request, StudentParent $parent): RedirectResponse
    {
        $this->parents->delete($parent, $request->user());

        return to_route('parents.index')->with('success', __('Parent deleted successfully.'));
    }
}
