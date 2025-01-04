<?php

namespace App\Http\Controllers;

use App\Models\PermissionGroup;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Lib\Webspice;

// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    protected $role;
    protected $roles;
    protected $roleid;
    public $tableName;
    protected $webspice;

    public function __construct(Role $roles)
    {
        $this->roles = $roles;
        $this->tableName = 'roles';
        $this->middleware('JWT');
        $this->webspice = new Webspice();
    }

    public function index()
    {
        #permission verfy
       $this->webspice->permissionVerify('role.manage');

        // $fileTag = '';
        // if ($request->get('status') == 'archived') {
        //     $fileTag = 'Archived ';
        //     $query = $this->roles->orderBy('deleted_at', 'desc');
        //     $query->onlyTrashed();
        // } else {
        //     $query = $this->roles->orderBy('created_at', 'desc');
        // }

        // if ($request->search_status != null) {
        //     $query->where('status', $request->search_status);
        // }
        // $searchText = $request->search_text;
        // if ($searchText != null) {
        //     // $query = $query->search($request->search_text); // search by value
        //     $query->where(function ($query) use ($searchText) {
        //         $query->where('name', 'LIKE', '%' . $searchText . '%')
        //             ->orWhere('guard_name', 'LIKE', '%' . $searchText . '%');
        //     });
        // }
        // if ($request->submit_btn == 'export') {
        //     $title = $fileTag . 'Role List';
        //     $fileName = str_replace(' ', '_', strtolower($title));
        //     return Excel::download(new RoleExport($query->get(), $title), $fileName . '_' . time() . '.xlsx');
        // }

        // $roles = $query->paginate(5);
        // return view('role.index', compact('roles'));
        try {
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

            $roles = Role::with('permissions')->when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }

            })->when(request('search', '') != '', function ($query) use ($searchTerm) {
                $query->search(trim($searchTerm));
            })->orderBy($sortField, $sortDirection)->paginate($paginate);

            // return ProductResource::collection($products);
            return response()->json($roles);
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    public function create()
    {
        #permission verfy
        $this->webspice->permissionVerify('role.create');

        $permission_groups = PermissionGrou::with('activePermissions')->where('status', 1)->orderBy('order')->get();
        
        return view('role.create', [
            'permission_groups' => $permission_groups,
        ]);
    }

    public function store(Request $request)
    {

      //  dd($request->all());
        #permission verfy
        $this->webspice->permissionVerify('role.create');

        $request->validate(
            [
                'name' => 'required|regex:/^[a-zA-Z ]+$/u|min:3|max:20|unique:roles',
                //'selectedPermissions' => 'required'
            ],
            [
                'selectedPermissions.required' => 'Please assign at least one permission.',
                'name.required' => 'Role Name field is required.',
                'name.unique' => 'The role name has already been taken.',
                'name.regex' => 'The role name format is invalid. Please enter alpabatic text.',
                'name.min' => 'The role name must be at least 3 characters.',
                'name.max' => 'The role name may not be greater than 20 characters.',
            ]
        );

        if (($request->selectedPermissions == null || count($request->selectedPermissions) == 0)) {
            return response()->json(
                [
                    'error' => 'The cart/ courtesy cart must contain at least one item.',
                ], 401);
        }

        $data = array(
            'name' => $request->name,
        );
        if (count($request->selectedPermissions) == 0) {
            return response()->json(
                [
                    'error' => 'Please select at least one permission',
                ], 401);
        }
        try {
            // Reset cached roles and permissions
            app()['cache']->forget('spatie.permission.cache');

            $role = $this->roles->create($data);
            $permissions = $request->selectedPermissions;
            if (!empty($permissions)) {
                for ($i = 0; $i < count($permissions); $i++) {
                    $role->givePermissionTo($permissions[$i]);
                }
            }
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }

        // return redirect()->back();
    }

    public function show($id)
    {
        try {
            $role = Role::with('permissions')->find($id);
            return $role;
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    // public function show(Role $role)
    // {
    //     return $role;
    // }

    public function edit($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('role.edit');
        try {
            # decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $roleInfo = $this->roles->findById($id);

            $permission_groups = PermissionGroup::with('activePermissions')->where('status', 1)->orderBy('order')->get();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return view('role.edit', [
            'roleInfo' => $roleInfo,
            'permission_groups' => $permission_groups,
        ]);
    }

    public function update(Request $request, $id)
    {
        #permission verfy
        $this->webspice->permissionVerify('role.edit');

        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

        $request->validate(
            [
                'name' => 'required|regex:/^[a-zA-Z ]+$/u|min:3|max:20|unique:roles,name,' . $id,
            ],
            [
                'name.required' => 'Role Name field is required.',
                'name.unique' => '"' . $request->name . '" The role name has already been taken.',
                'name.regex' => 'The role name format is invalid. Please enter alpabatic text.',
                'name.min' => 'The role name must be at least 3 characters.',
                'name.max' => 'The role name may not be greater than 20 characters.',
            ]
        );
        try {
            $role = $this->roles->findById($id);
            $permissions = $request->input('selectedPermissions');
            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
            }
            if (in_array($role->name, ['superadmin', 'developer'])) {
                // $role->name = $role->name;
            } else {
                $role->name = $request->name;
            }
            $role->save();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
        // return redirect()->route('roles.index');
    }

    public function destroy($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('role.delete');
        try {
            # decrypt value
            // $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $role = $this->roles->findById($id);
            $role->delete();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
        // return back();
    }

    public function forceDelete($id)
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
        #permission verfy
        $this->webspice->permissionVerify('role.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $role = Role::withTrashed()->findOrFail($id);
            $role->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('role.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $role = Role::withTrashed()->findOrFail($id);
            $role->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('roles.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('roles.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('role.restore');
        try {
            $roles = Role::onlyTrashed()->get();
            foreach ($roles as $role) {
                $role->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('roles.index');
        // return redirect()->route('roles.index')->withSuccess(__('All roles restored successfully.'));
    }

    public function clearPermissionCache()
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        Session::flash('success', 'Permission cache cleared Successfully.');
        return back();
    }

    public function getRoleData($roleId)
    {
        $role = Role::with('permissions')->find($roleId);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json(['role' => $role]);
    }

    public function getRoles()
    {
        $data = Role::where('status', 1)->get();
        return response()->json($data);
    }

}
