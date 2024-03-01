<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Jetstream\Jetstream;
use Illuminate\Validation\Rules\Password; // เพิ่มบรรทัดนี้
use Illuminate\Support\Facades\Storage;


class OwnerController extends Controller
{
    public function users(){
        $ีusers=User::all();
        return view('owner.employee',compact('users'));
    }

    public function employee(){
        $employee=User::where('userType', '1')->get();
        return view('owner.employee',compact('employee'));
    }
    public function employeedetail(){
        $employeedetail=User::where('userType', '1')->get();
        return view('owner.employeedetail',compact('employeedetail'));
    }

    public function checkUserType()
    {
        if (!Auth::check()) {
            return redirect()->route('welcome');
        }

        $userType = Auth::user()->userType;

        if ($userType === "0") {
            return redirect()->route('dashboard');
        }

        if ($userType === "1") {
            return redirect()->route('employeehome');
        }
        if ($userType === "2") {
            return redirect()->route('home');
        }
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'tel' => 'required|string|max:20',
            'start_date' => 'required|date',
            'birthday' => 'required|date',
            'address' => 'required|string|max:255',
            'image' => 'required|image|max:10240', // 10MB max size
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'tel' => $request->tel,
            'start_date' => $request->start_date,
            'birthday' => $request->birthday,
            'address' => $request->address,
            'image' => $request->file('image')->store('images', 'public'), // Save the image to public/images
            'userType' => $request->userType,
        ]);

        return redirect()->route('employee')->with('success', 'Employee created successfully');
    }

    public function edit($id){
        $user = User::find($id);  
        return view('owner.editemployee', compact('user'));
    }
    
    public function update(Request $request, $id){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'tel' => 'required|string|max:20',
            'start_date' => 'required|date',
            'birthday' => 'required|date',
            'address' => 'required|string|max:255',
            'image' => 'image|max:10240', // 10MB max size
        ]);
    
        $user = User::find($id);
    
        if (!$user) {
            return redirect()->route('employee.edit', $id)->with('error', 'ไม่พบผู้ใช้ที่ต้องการแก้ไข');
        }
    
        $user->name = $request->name;
        $user->email = $request->email;
        $user->tel = $request->tel;
        $user->start_date = $request->start_date;
        $user->birthday = $request->birthday;
        $user->address = $request->address;
    
        if ($request->hasFile('image')) {
            // Delete the old image if exists
            Storage::disk('public')->delete($user->image);
    
            // Save the new image
            $user->image = $request->file('image')->store('images', 'public');
        }
    
        $user->save();
    
        return redirect()->route('employee')->with('success', 'ข้อมูลพนักงานอัปเดตเรียบร้อย');
    }

    public function delete($id){
        $user = User::find($id);
    
        if ($user) {
            $user->delete();
            return redirect()->route('employee')->with('success', 'ลบข้อมูลสำเร็จ');
        } else {
            return redirect()->route('employee')->with('error', 'ไม่พบผู้ใช้ที่ต้องการลบ');
        }
    }
}

