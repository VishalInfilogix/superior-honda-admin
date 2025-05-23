<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use App\Imports\LocationImport;
use Maatwebsite\Excel\Facades\Excel;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (! Gate::allows('view location')) {
            abort(403);
        }

        $locations = Location::latest()->get();
        return view('locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (! Gate::allows('create location')) {
            abort(403);
        }

        return view('locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (! Gate::allows('create location')) {
            abort(403);
        }

        $request->validate([
            'name'    => 'required'
        ]);

        $location_exists = Location::where('name', $request->name)->where('deleted_at', NULL)->first();

        if(!empty($location_exists))
        {
            return redirect()->route('locations.index')->with('error', 'This location already exists.'); 
        }else{

            $locationImageFile = $request->file('location_image');
            $locationImageFilename = time().'.'.$locationImageFile->getClientOriginalExtension();
            $locationImageFile->move(public_path('uploads/locations/'), $locationImageFilename);

            Location::create([
                'name'          => $request->name,
                'location_image'      => isset($locationImageFilename) ? 'uploads/locations/'.$locationImageFilename : NULL,
                'disable_location'        => !empty($request->disable_location) ? $request->disable_location : '0',
            ]);
    
            return redirect()->route('locations.index')->with('success', 'Location saved successfully'); 
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $location)
    {
        if (! Gate::allows('edit location')) {
            abort(403);
        }

        $location = Location::where('id', $location)->first();

        return view('locations.edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $location)
    {
        if (! Gate::allows('edit location')) {
            abort(403);
        }

        $request->validate([
            'name'    => 'required',
        ]);

        // check if this location exists on another id
        $location_exists = Location::where('name', $request->name)->where('deleted_at', NULL)->where('id', '!=', $location)->first();

        if(!empty($location_exists))
        {
            return redirect()->route('locations.index')->with('error', 'This Location Already Exists.'); 
        }else{

            $location_data = Location::where('id', $location)->first();

            $oldLocationImage = NULL;
            if($location_data != '') {
                $oldLocationImage = $location_data->location_image;
            }

            if(!empty($request->file('location_image')))
            {
                $locationImageFile = $request->file('location_image');
                $locationImageFilename = time().'.'.$locationImageFile->getClientOriginalExtension();
                $locationImageFile->move(public_path('uploads/locations/'), $locationImageFilename);
            }

            Location::where('id', $location)->update([
                'name'            => $request->name,
                'location_image'      => isset($locationImageFilename) ? 'uploads/locations/'.$locationImageFilename : $oldLocationImage,
                'disable_location'          => !empty($request->disable_location) ? $request->disable_location : '0'
            ]);
        }

        return redirect()->route('locations.index')->with('success', 'Location updated successfully'); 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $location)
    {
        if (! Gate::allows('delete location')) {
            abort(403);
        }

        Location::where('id', $location)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully.'
        ]);
    }

    public function disableLocation(Request $request)
    {
        $location = Location::where('id', $request->id)->first();
        $status = 1;
        $message = 'Location disabled successfully.';
        if($request->disable_location == 'disabled'){
            $status = '0';
            $message = 'Location enabled successfully.';
        }else{
            $status = '1';
            $message = 'Location disabled successfully.';
        }

        Location::where('id', $request->id)->update([
          'disable_location' => $status  
        ]);

        return response()->json([
                'success' => true,
                'message' => $message
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required',
        ], [
            'file.required' => 'Please upload a file.',
        ]);

        $import = new LocationImport;
        Excel::import($import, $request->file('file'));

        Session::flash('import_errors', $import->getErrors());

        return redirect()->route('locations.index')->with('success', 'Locations imported successfully.');
    }
}
