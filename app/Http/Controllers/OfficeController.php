<?php

namespace App\Http\Controllers;

use App\OfficeObjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;

class OfficeController extends Controller
{
    /**
     * Display a listing of the offices.
     * If you send a get request with sort_value, this function will return you sorted objects.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sort_value = Input::get('sort_value');
        if ($sort_value !== null) {
            if ($sort_value == 'max') {
                $offices = OfficeObjects::orderBy('price', 'DESC')->get();
            } else {
                $offices = OfficeObjects::orderBy('price', 'ASC')->get();
            }
        } else {
            $offices = OfficeObjects::all();
        }

        return View::make('offices.index')
            ->with('offices', $offices);
    }

    /**
     * Display a found listing of the resource by address.
     * If you send a post request with address in the body, this function will return you liked objects.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */

    public function address_index(Request $request)
    {
        $address = $request->address;
        $offices = OfficeObjects::where('address', 'LIKE', "%{$address}%")->get();
        return View::make('offices.index')
            ->with('offices', $offices);
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $office = OfficeObjects::find($id);
        return View::make('offices.show')->with('office', $office);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
