<?php

namespace App\Http\Controllers;

use App\Models\status;
use App\Http\Requests\StorestatusRequest;
use App\Http\Requests\UpdatestatusRequest;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorestatusRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(status $status)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatestatusRequest $request, status $status)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(status $status)
    {
        //
    }
}
