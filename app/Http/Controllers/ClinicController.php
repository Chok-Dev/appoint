<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Group;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['index', 'show']);
    }

    public function index()
    {
        $clinics = Clinic::with('group')->get();
        return view('clinics.index', compact('clinics'));
    }

    public function create()
    {
        $groups = Group::all();
        return view('clinics.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group_id' => 'required|exists:groups,id',
        ]);

        Clinic::create($validated);

        return redirect()->route('clinics.index')
            ->with('success', 'Clinic created successfully.');
    }

    public function show(Clinic $clinic)
    {
        $clinic->load('group', 'doctors');
        return view('clinics.show', compact('clinic'));
    }

    public function edit(Clinic $clinic)
    {
        $groups = Group::all();
        return view('clinics.edit', compact('clinic', 'groups'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group_id' => 'required|exists:groups,id',
        ]);

        $clinic->update($validated);

        return redirect()->route('clinics.index')
            ->with('success', 'Clinic updated successfully.');
    }

    public function destroy(Clinic $clinic)
    {
        $clinic->delete();

        return redirect()->route('clinics.index')
            ->with('success', 'Clinic deleted successfully.');
    }
}