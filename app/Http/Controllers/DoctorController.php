<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function doctorList()
    {
        return view('manager.sub-page.requests.doctors-list');
    }

    public function saveDoctor(Request $request)
    {
        $doctorModel = new Doctor();
        $doctor = ucwords($request->doctor_name);

        $doctorModel->doctor_name = $doctor;

        if ($doctorModel->save()) {
            return back()->with("success", "New Doctor Added.");
        }
    }
}
