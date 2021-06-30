<?php

namespace App\Http\Controllers;

use function __;
use App\Http\Requests\RequestSuggestion;
use App\Models\Suggestion;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use function App\Utils\getItemsPerPage;
use function redirect;
use StormUtils;
use function view;

class SuggestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $suggestions = Suggestion::paginate(getItemsPerPage());

        return view('suggestions.index')->with('suggestions', $suggestions);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('suggestions.create');
    }

    /**
     *  Store a newly created resource in storage.
     *
     * @param RequestSuggestion $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RequestSuggestion $request)
    {
        $validated = $request->validated();
        $suggestion = Suggestion::create($validated);

        return redirect()->route('suggestions.index')->with(FLASH_SUCCESS, __('Suggestion created!'));
    }

    /**
     * Display the specified resource.
     *
     * @param Suggestion $suggestion
     * @return \Illuminate\Http\Response
     */
    public function show(Suggestion $suggestion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Suggestion $suggestion
     * @return \Illuminate\Http\Response
     */
    public function edit(Suggestion $suggestion)
    {
        return view('suggestions.edit')->withSuggestion($suggestion);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RequestSuggestion $request
     * @param Suggestion $suggestion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RequestSuggestion $request, Suggestion $suggestion)
    {
        $validated = $request->validated();
        $suggestion->fill($validated)->save();
        $suggestion->save();

        return redirect()->route('suggestions.index')->with(FLASH_SUCCESS, __('Suggestion updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Suggestion $suggestion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Suggestion $suggestion)
    {
        $suggestion->delete();

        return redirect()->route('suggestions.index')->with(FLASH_SUCCESS, __('Suggestion deleted'));
    }

    /**
     * Ask confirmation about the specified resource from storage to remove.
     *
     * @param Suggestion $suggestion
     * @return mixed
     */
    public function confirmDestroy(Suggestion $suggestion)
    {
        return view('suggestions.delete')->withSuggestion($suggestion);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchContext(Request $request)
    {
        $query = $request->input('query');
        $users = Suggestion::selectRaw('distinct context')->where('context', 'like', "%$query%")->get();

        return response()->json($users);
    }
}
