<?php

namespace App\Http\Controllers\Admin;

use App\WorkingHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWorkingHoursRequest;
use App\Http\Requests\Admin\UpdateWorkingHoursRequest;

class WorkingHoursController extends Controller
{
    /**
     * Display a listing of WorkingHour.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('working_hour_access')) {
            return abort(401);
        }

        $working_hours = WorkingHour::all();

        return view('admin.working_hours.index', compact('working_hours'));
    }

    /**
     * Show the form for creating new WorkingHour.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('working_hour_create')) {
            return abort(401);
        }
        $relations = [
            'employees' => \App\Employee::get(),
        ];

        return view('admin.working_hours.create', $relations);
    }

    /**
     * Store a newly created WorkingHour in storage.
     *
     * @param  \App\Http\Requests\StoreWorkingHoursRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWorkingHoursRequest $request)
    {
        if (! Gate::allows('working_hour_create')) {
            return abort(401);
        }
        
    

        do {
            $workingHour=new WorkingHour();
            $workingHour->employee_id=$request->employee_id;
            $workingHour->date=$request->date;
            $request->date=date('Y-m-d', strtotime('+1 day', strtotime($request->date)));
            $workingHour->start_time=$request->start_time;
            $workingHour->finish_time=$request->finish_time;
            //    $working_hour = WorkingHour::create($request->all());
            $workingHour->save();
        } while (date("Y-m-t", strtotime($request->date))>$request->date);
        // dd($working_hour);

        return redirect()->route('admin.working_hours.index');
    }


    /**
     * Show the form for editing WorkingHour.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('working_hour_edit')) {
            return abort(401);
        }
        $relations = [
            'employees' => \App\Employee::get()->pluck('first_name', 'id')->prepend('Please select', ''),
        ];

        $working_hour = WorkingHour::findOrFail($id);

        return view('admin.working_hours.edit', compact('working_hour') + $relations);
    }

    /**
     * Update WorkingHour in storage.
     *
     * @param  \App\Http\Requests\UpdateWorkingHoursRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWorkingHoursRequest $request, $id)
    {
        if (! Gate::allows('working_hour_edit')) {
            return abort(401);
        }
        $working_hour = WorkingHour::findOrFail($id);
        $working_hour->update($request->all());



        return redirect()->route('admin.working_hours.index');
    }


    /**
     * Display WorkingHour.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! Gate::allows('working_hour_view')) {
            return abort(401);
        }
        $relations = [
            'employees' => \App\Employee::get()->pluck('first_name', 'id')->prepend('Please select', ''),
        ];

        $working_hour = WorkingHour::findOrFail($id);

        return view('admin.working_hours.show', compact('working_hour') + $relations);
    }


    /**
     * Remove WorkingHour from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('working_hour_delete')) {
            return abort(401);
        }
        $working_hour = WorkingHour::findOrFail($id);
        $working_hour->delete();

        return redirect()->route('admin.working_hours.index');
    }

    /**
     * Delete all selected WorkingHour at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (! Gate::allows('working_hour_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = WorkingHour::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }

}
