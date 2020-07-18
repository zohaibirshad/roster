<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Appointment;
use App\Service;
use App\WorkingHour;
use Carbon\Carbon;
use App\Employee;
use Auth;

// This controller is from a service provider website where 
// a customer selects a service and book employee(service provider)

class ServiceController extends Controller
{
    public function index()
    {
        $services=Service::all();
        return view('user.client.index', compact('services'));
    }

    public function store(Request $request)
    {
        // This Piece of code is main algorithm when user clicks on Booking button
        // and selects time and an appointment is set for the employee
        // Appointment is automtically assigned to employee which employee is 
        // free in that slot of time and related to that service
        // 
        
        if($request->session()->exists('appointment_data')){
            
            $data=session('appointment_data');
            $id=$data['id'];
            $request->start_time=$data['start_time'];
            $request->end_time=$data['end_time'];
            $request->date=$data['date'];
            $request->session()->forget('appointment_data');
        }
    
        else{
    
            $id=$request->id;
    
        }

        date_default_timezone_set("Asia/Karachi");
        $employeesAvailable=array();
        $selectedEmployees=array();
       $servicesData=Service::select()->with('employees')->where('id', $id)->get();
       foreach ($servicesData[0]->employees as $employee) {
        $workingHours = WorkingHour::where('employee_id', $employee->id)->where('date', '=', date("Y-m-d", strtotime($request->date)))
            
            ->whereTime('start_time', '<=', date("H:i:s", strtotime($request->start_time)))
            ->whereTime('finish_time', '>', date("H:i:s", strtotime($request->start_time)))
            ->whereTime('start_time', '<', date("H:i", strtotime($request->end_time)))
            ->whereTime('finish_time', '>=', date("H:i", strtotime($request->end_time)))->get();
  
            if (!$workingHours->isEmpty()) {
                $employeesAvailable[]=$employee->id;
            }
        }

        if(count($employeesAvailable)==0){
        return view('user.client.booking', ['employeeData' => null]);
        }
     
        //assign job to a free employee
        foreach ($employeesAvailable as $employee) {
            $appointment = Appointment::where('employee_id', $employee)->whereDay('date', '=', date("d", strtotime($request->date)))->get();
            
            if ($appointment->isEmpty()) {
                $selectedEmployees[]=$employee;
            } else {
                $selectedEmployeesWithAppointment[]=$employee;
            }
        }



        if (count($selectedEmployees) > 0) {
            $setAppointment=new Appointment();
            $setAppointment->client_id=Auth::guard('client')->user()->id;
            $setAppointment->employee_id=end($selectedEmployees);
            $setAppointment->service_id=$id;
            $setAppointment->date=$request->date;
            $setAppointment->start_time=date('H:i:s', strtotime($request->start_time));
            $setAppointment->finish_time=date('H:i:s', strtotime($request->end_time));
            $setAppointment->comments="appointment set";
            $setAppointment->save();
        } else {
            // assign job to employee who  already have aapointments
            foreach ($selectedEmployeesWithAppointment as $key => $employee) {
                $appointmentz = Appointment::where('employee_id', $employee)->whereDay('date', '=', date("d", strtotime($request->date)))->get();
                foreach ($appointmentz as $appointment) {
                    if ((date("H:i:s", strtotime($request->start_time)) >= $appointment->start_time &&
                date("H:i:s", strtotime($request->start_time)) < $appointment->finish_time)
                ||(date("H:i:s", strtotime($request->end_time)) > $appointment->start_time &&
                date("H:i:s", strtotime($request->end_time)) <= $appointment->finish_time) ||
                (date("H:i:s", strtotime($request->start_time)) == $appointment->start_time &&
                date("H:i:s", strtotime($request->end_time)) == $appointment->finish_time)) {
                        $selectedEmployees=array_unique($selectedEmployees);
                        foreach ($selectedEmployees as $key => $emp) {
                            if ($selectedEmployees[$key]==$employee) {
                                unset($selectedEmployees[$key]);
                            }
                        }

                        break;
                    } else {
                        $selectedEmployees[]=$employee;
                        // break;
                    }
                }
            }
            
            if (!empty($selectedEmployees)) {
                $setAppointment=new Appointment();
                $setAppointment->client_id=Auth::guard('client')->user()->id;
                $setAppointment->employee_id=end($selectedEmployees);
                $setAppointment->service_id=$id;
                $setAppointment->date=$request->date;
                $setAppointment->start_time=date('H:i:s', strtotime($request->start_time));
                $setAppointment->finish_time=date('H:i:s', strtotime($request->end_time));
                $setAppointment->comments="appointment set";
                $setAppointment->save();
            } else {

                //check for empty slot and assign job
                foreach ($selectedEmployeesWithAppointment as $key => $employee) {
                    $appointments = Appointment::where('employee_id', $employee)->whereDay('date', '=', date("d", strtotime($request->date)))->get();


                    $time=strtotime($request->end_time) - strtotime($request->start_time);

                    foreach ($appointments as $appointment) {
                        $request->start_time=$appointment->finish_time;

                        $hours=floor($time/3600);
                        $minutes=floor($time/60)% 60;

                        $request->end_time=date('H:i:s', strtotime('+'.$hours.'hour +'.$minutes. 'minutes', strtotime($request->start_time)));


                        $current_start = Appointment::whereTime('start_time', '<=', $request->start_time)
            ->whereTime('finish_time', '>', $request->start_time)->where('service_id',$id)
            ->get();

            
                        $current_end = Appointment::whereTime('start_time', '<', $request->end_time)
            ->whereTime('finish_time', '>', $request->end_time)->where('service_id',$id)
            ->get();

                        if (count($current_start) == 0 && count($current_end) == 0) {
                            $start_time=$request->start_time;
                            return view('user.client.different_slot', ['start_time' => $start_time,'end_time' => $request->end_time, 'date' => $request->date,'service_id' => $id]);
                        }
                    }
                }
            }
        }
        $employeeData=Employee::find(end($selectedEmployees));
        return view('user.client.booking', compact('employeeData'));
    }
    //specific service data

    public function show($id)
    {
        $serviceData=Service::find($id);
        return view('user.client.serviceDetails', compact('serviceData'));
    }
}