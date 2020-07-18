<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('service_access')) {
            return abort(401);
        }

        $services = Service::all();

        return view('admin.services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('service_create')) {
            return abort(401);
        }
        return view('admin.services.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('service_create')) {
            return abort(401);
        }
        $imageName=uniqid('SERVICE_');
        $data=[
            'name' => $request->name,
            'price' => $request->price,
            'image' => $imageName,
        ];
        $image=file_get_contents($request->file('image'));
        Storage::disk('public')->put($imageName,$image);
        $service = Service::create($data);
        return redirect()->route('admin.services.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $service=Service::find($id);
        return view('admin.services.edit',compact('service'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('service_create')) {
            return abort(401);
        }
        $service = Service::find($id);

        $imageName=uniqid('SERVICE_');
        $data=[
            'name' => $request->name,
            'price' => $request->price,
            'image' => $imageName,
        ];
        $image=file_get_contents($request->file('image'));
        Storage::disk('public')->put($imageName,$image);
        Storage::delete($service->image);
        $service->update($data);
        return redirect()->route('admin.services.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $service=Service::find($id);
        $service->delete();
        Storage::delete($service->image);
        return redirect()->route('admin.services.index');

    }
	
	public function massDestroy(Request $request)
    {
        if (! Gate::allows('service_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Service::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }
}
