<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestSite;
use App\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sites = Site::all();
        return view('sites.index')->with('sites', $sites);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function show(Site $site)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function edit(Site $site)
    {
        return view('sites.edit')->withSite($site);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\RequestSite  $request
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function update(RequestSite $request, Site $site)
    {

//        $site = Site::findOrFail($id);

        $validated = $request->validated();

//        $input = $request->all();
        $site->fill($validated)->save();

        return redirect()->route('sites.index')
            ->with('flash_message', __('Site :name updated!', ['name' => $site->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function destroy(Site $site)
    {
        //
    }


    /**
     * Ask confirmation about the specified resource from storage to remove.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function confirmDestroy($id)
    {
        $site = Site::findOrFail($id);
        return view('sites.delete')->withSite($site);
    }
}
