<?php

namespace App\Http\Controllers;

use App\Lib\Webspice;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserExport;
use App\Models\PermissionGroup;

class UserController extends Controller
{

    protected $webspice;
    protected $user;
    protected $tableName;
    protected $users;

    public function __construct(User $users)
    {
        $this->tableName = 'users';
        $this->webspice = new Webspice();
        $this->middleware('JWT');
        $this->users = $users;
    }

    public function index(Request $request)
    {
        // #permission verfy
        $this->webspice->permissionVerify('user.manage');

        // # Query Start
        // $fileTag = '';
        // if ($request->get('status') == 'archived') {
        //     $fileTag = 'Archived ';
        //     $query = $this->users->orderBy('deleted_at', 'desc');
        //     $query->onlyTrashed();
        // } else {
        //     $query = $this->users->orderBy('created_at', 'desc');
        // }
        // if ($request->search_status != null) {
        //     $query->where('status', $request->search_status);
        // }
        // $searchText = $request->search_text;
        // if ($searchText != null) {
        //     // $query = $query->search($request->search_text); // search by value     

        //     $query->where(function ($query) use ($searchText) {
        //         $query->where('name', 'LIKE', '%' . $searchText . '%')
        //             ->orWhere('email', 'LIKE', '%' . $searchText . '%');
        //     });
        // }

        // if ($request->submit_btn == 'export') {
        //     $title = $fileTag . 'User List';
        //     $fileName = str_replace(' ', '_', strtolower($title));
        //     return Excel::download(new UserExport($query->get(), $title), $fileName . '_' . time() . '.xlsx');
        // }
        // $users = $query->paginate(5);

        // #Query End
        // return view('users.index', compact('users'));

        $paginate = request('paginate', 5);
        $searchTerm = request('search', '');

        $sortField = request('sort_field', 'created_at');
        if (!in_array($sortField, ['id', 'name'])) {
            $sortField = 'created_at';
        }
        $sortDirection = request('sort_direction', 'created_at');
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $filled = array_filter(request([
            'id',
            'name',
        ]));

        $users = User::with(['roles', 'permissions'])->when(count($filled) > 0, function ($query) use ($filled) {
            foreach ($filled as $column => $value) {
                $query->where($column, 'LIKE', '%' . $value . '%');
            }

        })->when(request('search', '') != '', function ($query) use ($searchTerm) {
            $query->search(trim($searchTerm));
        })->orderBy($sortField, $sortDirection)->paginate($paginate);
       
        return response()->json($users);
    }


    public function create()
    {
        #permission verfy
        //$this->webspice->permissionVerify('user.create');
        $roles = Role::where('status', 1)->get();
        $permission_groups = PermissionGroup::with('activePermissions')->where('status', 1)->orderBy('order')->get();

        return view('users.create', compact('roles', 'permission_groups'));
    }

    public function store(Request $request)
    {
        #permission verfy
        $this->webspice->permissionVerify('user.create');
        $request->validate(
            [
                'name' => 'required|min:3|max:20',
                'email' => 'required|min:3|email|max:20|unique:users',
                'password' => 'required|min:6|confirmed',
                'selectedRoles' => 'required',
            ],
            [
                'selectedRoles.required' => 'Please assign at least one role',
                'name.required' => 'User Name field is required.',
                'name.unique' => 'The User name has already been taken.',
                'name.regex' => 'The User name format is invalid. Please enter alpabatic text.',
                'name.min' => 'The User name must be at least 3 characters.',
                'name.max' => 'The User name may not be greater than 20 characters.'
            ]
        );
        //$insertId = $this->users->insertUser($request);
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;

            $user->password = Hash::make($request->password);
            $user->created_at = $this->webspice->now('datetime24');
            // $user->created_by = $this->webspice->getUserId();

            if ($request->hasFile('file')) {
                $destinationPath = 'assets/img/user/';
                $file = $request->file('file');
                $filename = $file->getClientOriginalName();
                // $file->storeAs('uploads/product', $filename, 'public');
                $file->move(public_path($destinationPath), $filename);
                $user->file = $destinationPath.$filename;
            }

            $user->save();

            if ($request->selectedRoles) {
                $user->assignRole($request->selectedRoles);
            }

            // $permissions = $request->permissions;

            // if (!empty($permissions)) {
            //     $user->syncPermissions($permissions);
            // }

            # Success Message & Log into Observers
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    public function show($id)
    {
         #permission verfy
        $this->webspice->permissionVerify('user.manage');
        try {
            $role = User::with('roles')->find($id);
            return $role;
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    public function edit($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('user.edit');

        # decrypt value
        $id = $this->webspice->encryptDecrypt('decrypt', $id);
        $user = $this->users->find($id);
        $roles = Role::where('status', 1)->get();
        $permission_groups = PermissionGroup::with('activePermissions')->where('status', 1)->orderBy('order')->get();

        return view('users.edit', [
            'user' => $user,
            'roles' => $roles,
            'permission_groups' => $permission_groups
        ]);
    }


    public function update(Request $request, $id)
    {
      
        #permission verfy
        $this->webspice->permissionVerify('user.edit');
        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

      
        $request->validate(
            [
                'name'     => 'required|min:3|max:50',
                'email'    => 'required|min:3|email|max:20|unique:users,email,' . $id,
                'password' => 'nullable|min:6|confirmed',
                'selectedRoles' => 'required',
            ],
            [
                'selectedRoles.required' => 'Please assign at least one role',
                'name.required' => 'User Name field is required.',
                'name.unique'   => 'The User name has already been taken.',
                'name.regex'    => 'The User name format is invalid. Please enter alpabatic text.',
                'name.min'      => 'The User name must be at least 3 characters.',
                'name.max'      => 'The User name may not be greater than 20 characters.'
            ]
        );
        try {
            // $user = $this->users->find($id);
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->password) {

                $user->password = Hash::make($request->password);
            }
            $user->updated_at = $this->webspice->now('datetime24');
            // $user->updated_by = $this->webspice->getUserId();
            $user->save();

            $user->roles()->detach(); // delete from model table
            if ($request->selectedRoles) {
                $user->assignRole($request->selectedRoles);
            }

            // $permissions = $request->permissions;
            // if (!empty($permissions)) {
            //     $user->syncPermissions($permissions);
            // }
            # Success Message & Log into UserObservers
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
        // return redirect('users');
    }

    public function changePassword(Request $request)
    {

        if ($request->all()) {

            $request->validate(
                [
                    'old_password' => 'required',
                    'new_password' => 'required|min:6|confirmed'
                ],
                [
                    'name.required' => 'Password is required.',
                    'password.min' => 'Password must be at least 6 characters.'
                ]
            );
            #Match The Old Password
            if (!Hash::check($request->old_password, auth()->user()->password)) {
                return back()->with("error", "Old Password Doesn't match!");
            }

            #Update the new Password
            User::whereId(auth()->user()->id)->update([
                'password' => Hash::make($request->new_password)
            ]);
            Webspice::log($this->tableName, auth()->user()->id, "Password changed.");
            return back()->with("success", "Password changed successfully!");
        }
        return view('users.change-password');
    }

    public function destroy($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('user.delete');
        try {
            # decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $user = $this->users->find($id);
            if (!is_null($user)) {
                $user->delete();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }

        return redirect()->back();
    }


    public function userProfile()
    {
        return view('users.user-profile');
    }



    public function forceDelete($id)
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
        #permission verfy
        $this->webspice->permissionVerify('user.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $user = User::withTrashed()->findOrFail($id);
            $user->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('user.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('users.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('users.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('user.restore');
        try {
            $users = User::onlyTrashed()->get();
            foreach ($users as $user) {
                $user->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('users.index');
        // return redirect()->route('users.index')->withSuccess(__('All users restored successfully.'));
    }
}
