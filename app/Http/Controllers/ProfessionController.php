<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestProfession;
use App\Profession;
use Illuminate\Http\Request;

class ProfessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $professions = Profession::all();
        return view('professions.index')->with('professions', $professions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('professions.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\RequestProfession  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RequestProfession $request)
    {
        $validated = $request->validated();
        $profession = Profession::create($validated);
        return redirect()->route('professions.index')
            ->with(FLASH_SUCCESS, __('Profession :name updated!', ['name' => $profession->name]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Profession  $profession
     * @return \Illuminate\Http\Response
     */
    public function show(Profession $profession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Profession  $profession
     * @return \Illuminate\Http\Response
     */
    public function edit(Profession $profession)
    {
        return view('professions.edit')->withProfession($profession);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\RequestProfession  $request
     * @param  \App\Profession  $profession
     * @return \Illuminate\Http\Response
     */
    public function update(RequestProfession $request, Profession $profession)
    {

        $validated = $request->validated();
        $profession->fill($validated)->save();
        $profession->is_storm = $request->has('is_storm');
        $profession->save();

        return redirect()->route('professions.index')
            ->with(FLASH_SUCCESS, __('Profession :name updated!', ['name' => $profession->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Profession::findOrFail($id)->delete();

        return redirect()->route('professions.index')
            ->with(FLASH_SUCCESS, __('Profession deleted'));
    }


    /**
     * Ask confirmation about the specified resource from storage to remove.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function confirmDestroy($id)
    {
        $profession = Profession::findOrFail($id);
        return view('professions.delete')->withProfession($profession);
    }
}
