<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestAddress;
use App\Http\Requests\RequestSite;
use App\Site;
use Countries;
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
        return view('sites.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\RequestSite  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RequestSite $request)
    {
        $validated = $request->validated();
        $site = Site::create($validated);
        return redirect()->route('sites.index')
            ->with('flash_message', __('Site :name updated!', ['name' => $site->name]));
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

        $validated = $request->validated();
        $site->fill($validated)->save();

        return redirect()->route('sites.index')
            ->with('flash_message', __('Site :name updated!', ['name' => $site->name]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Site::findOrFail($id)->delete();

        return redirect()->route('sites.index')
            ->with('flash_message', __('Site deleted'));
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


    /*
     * *************************************************************
     *                      ADDRESSES
     * *************************************************************
     */

    /**
     * Addresses list for a Site
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function addressesIndex($id)
    {
        $site = Site::findOrFail($id);
        $addresses = $site->getAddresses();
        return view('sites.addresses.index')->with(['addresses' => $addresses, 'site' => $site]);
    }

    /**
     * Show the form for creating a new addresses for the Site.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function addressesCreate($id)
    {
        return view('sites.addresses.create')->with(['site' => Site::findOrFail($id)]);
    }


    /**
     * Store a newly created addresses for the Site in storage.
     *
     * @param  \App\Http\Requests\RequestAddress  $request
     * @return \Illuminate\Http\Response
     */
    public function addressesStore(RequestAddress $request)
    {
        $validated = $request->validated();
        $site = Site::findOrFail($validated['site_id']);
        unset($validated['site_id']);

        try {
            // Qua si innesca anche il Validator della HasAddresses che segue queste regole:
//            'street'       => 'required|string|min:3|max:60',
//            'street_extra' => 'string|min:3|max:60',
//            'city'         => 'required|string|min:3|max:60',
//            'state'        => 'string|min:3|max:60',
//            'post_code'    => 'required|min:4|max:10|AlphaDash',
//            'country_id'   => 'required|integer',
            // ...la country viene gestite ricercando al stringa nei campi iso_3166_2 o iso_3166_3 di countries
            $site->addAddress($validated);
        } catch (\Exception $e) {

        }

        return redirect()->route('sites.index')
            ->with('flash_message', __('New address added for site :name!', ['name' => $site->name]));
    }
}
