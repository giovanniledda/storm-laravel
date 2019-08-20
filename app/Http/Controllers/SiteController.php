<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestAddress;
use App\Http\Requests\RequestSite;
use App\Site;
use Countries;
use const FLASH_ERROR;
use Illuminate\Http\Request;
use Lecturize\Addresses\Facades\Addresses;
use StormUtils;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $sites = Site::all();
        $sites = Site::paginate(StormUtils::getItemsPerPage());

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
            ->with(FLASH_SUCCESS, __('Site :name created!', ['name' => $site->name]));
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
            ->with(FLASH_SUCCESS, __('Site :name updated!', ['name' => $site->name]));
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
            ->with(FLASH_SUCCESS, __('Site deleted'));
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
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function addressesStore(RequestAddress $request, $id)
    {
        $validated = $request->validated();
        $site = Site::findOrFail($id);

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
            $message = __('New address added for site :name!', ['name' => $site->name]);
            $message_type = FLASH_SUCCESS;

        } catch (\Exception $e) {
            $message = __('Something went wrong adding new address, check your data!');
            $message_type = FLASH_ERROR;
        }

        return redirect()->route('sites.addresses.index', ['id' => $id])->with($message_type, $message);
    }


    /**
     * Show the form for editing the specified addresses for the Site.
     *
     * @param  int $site_id
     * @param  int $address_id
     * @return \Illuminate\Http\Response
     */
    public function addressesEdit($site_id, $address_id)
    {
        $site = Site::findOrFail($site_id);
        $address = $site->getAddress($address_id);

        return view('sites.addresses.edit')->with(['address' => $address, 'site' => $site]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\RequestAddress  $request
     * @param  int $site_id
     * @param  int $address_id
     * @return \Illuminate\Http\Response
     */
    public function addressesUpdate(RequestAddress $request, $site_id, $address_id)
    {
        $message = __('Address [:id] has not been updated!', ['id' => $address_id]);
        $message_type = FLASH_ERROR;
        $validated = $request->validated();
        $site = Site::findOrFail($site_id);

        $address = $site->getAddress($address_id);
        if ($address) {
            try {
                $site->updateAddress($address, $validated);
                $message = __('Address [:id] in :city updated!', ['id' => $address_id, 'city' => $address->city]);
                $message_type = FLASH_SUCCESS;

            } catch (\Exception $e) {
                $message = __('Something went wrong updating address [:id], check your data!', ['id' => $address_id]);
                $message_type = FLASH_ERROR;
            }
        }

        return redirect()->route('sites.addresses.index', ['id' => $site_id])->with($message_type, $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $site_id
     * @param  int $address_id
     * @return \Illuminate\Http\Response
     */
    public function addressesDestroy($site_id, $address_id)
    {
        $message = __('Address [:id] has not been deleted!', ['id' => $address_id]);
        $message_type = FLASH_ERROR;
        $site = Site::findOrFail($site_id);

        $address = $site->getAddress($address_id);
        if ($address) {
            $site->deleteAddress($address); // delete by passing it as argument
            $message = __('Address [:id] deleted!', ['id' => $address_id]);
            $message_type = FLASH_SUCCESS;
        }

        return redirect()->route('sites.addresses.index', ['id' => $site_id])->with($message_type, $message);
    }


    /**
     * Ask confirmation about the specified resource from storage to remove.
     *
     * @param  int $site_id
     * @param  int $address_id
     * @return \Illuminate\Http\Response
     */
    public function addressesConfirmDestroy($site_id, $address_id)
    {
        $site = Site::findOrFail($site_id);
        $address = $site->getAddress($address_id);

        return view('sites.addresses.delete')->with(['address' => $address, 'site' => $site]);
    }
}
