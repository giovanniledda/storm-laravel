<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestTaskInterventType;
use App\TaskInterventType;
use Illuminate\Http\Request;
use StormUtils;

class TaskInterventTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $intervent_types = TaskInterventType::all();

        $intervent_types = TaskInterventType::paginate(StormUtils::getItemsPerPage());

        return view('task_intervent_types.index')->with('intervent_types', $intervent_types);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('task_intervent_types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\RequestTaskInterventType  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RequestTaskInterventType $request)
    {
        $validated = $request->validated();
        $intervent_type = TaskInterventType::create($validated);

        return redirect()->route('task_intervent_types.index')
            ->with(FLASH_SUCCESS, __('Intervent type :name created!', ['name' => $intervent_type->name]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\TaskInterventType  $taskInterventType
     * @return \Illuminate\Http\Response
     */
    public function show(TaskInterventType $taskInterventType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TaskInterventType  $taskInterventType
     * @return \Illuminate\Http\Response
     */
    public function edit(TaskInterventType $taskInterventType)
    {
        return view('task_intervent_types.edit')->with('intervent_type', $taskInterventType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\RequestTaskInterventType  $request
     * @param  \App\TaskInterventType  $taskInterventType
     * @return \Illuminate\Http\Response
     */
    public function update(RequestTaskInterventType $request, TaskInterventType $taskInterventType)
    {
        $validated = $request->validated();
        $taskInterventType->fill($validated)->save();

        return redirect()->route('task_intervent_types.index')
            ->with(FLASH_SUCCESS, __('Intervent types :name updated!', ['name' => $taskInterventType->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        TaskInterventType::findOrFail($id)->delete();

        return redirect()->route('task_intervent_types.index')
            ->with(FLASH_SUCCESS, __('Intervent type deleted'));
    }

    /**
     * Ask confirmation about the specified resource from storage to remove.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function confirmDestroy($id)
    {
        $taskInterventType = TaskInterventType::findOrFail($id);

        return view('task_intervent_types.delete')->with('intervent_type', $taskInterventType);
    }
}
