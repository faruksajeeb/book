<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\Option;
use Exception;
use Illuminate\Http\Request;

class OptionController extends Controller
{

    public $webspice;
    protected $option;
    protected $options;
    protected $optionid;
    public $tableName;

    public function __construct(Option $option, Webspice $webspice)
    {
        $this->webspice = $webspice;
        $this->options = $option;
        $this->tableName = 'options';
        $this->middleware('JWT');
    }

    public function index()
    {
        #permission verfy
        $this->webspice->permissionVerify('option.manage');
        try {
            $paginate = request('paginate', 10);
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

            $Options = Option::with('option_group')->when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }

            })->when(request('group_name', '') != '', function ($query){
                $query->where('option_group_id',request('group_name'));
            })->when(request('search', '') != '', function ($query) use ($searchTerm) {
                $query->search(trim($searchTerm));
            })->orderBy($sortField, $sortDirection)->paginate($paginate);

            // return ProductResource::collection($products);
            return response()->json($Options);
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    public function store(Request $request)
    {

        #permission verfy
        $this->webspice->permissionVerify('option.create');

        $request->validate(
            [
                'name' => 'required|min:2|max:500|unique:options',
                'option_group_id' => 'required',
            ],
            [
                'name.required' => 'option Name field is required.',
                'name.unique' => 'The option name has already been taken.',
                'name.regex' => 'The option name format is invalid. Please enter alpabatic text.',
                'name.min' => 'The option name must be at least 3 characters.',
                'name.max' => 'The option name may not be greater than 20 characters.',
            ]
        );

        try {
            $input = $request->all();
            $input['created_by'] = $this->webspice->getUserId();
            $this->options->create($input);
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
            $option = Option::with('option_group')->find($id);
            return $option;
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
    }

    public function update(Request $request, $id)
    {

        #permission verfy
        $this->webspice->permissionVerify('option.edit');

        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

        $request->validate(
            [
                'name' => 'required|min:2|max:500|unique:options,name,' . $id,
            ],
            [
                'name.required' => 'option Name field is required.',
                'name.unique' => '"' . $request->name . '" The option name has already been taken.',
                'name.regex' => 'The option name format is invalid. Please enter alpabatic text.',
                'name.min' => 'The option name must be at least 3 characters.',
                'name.max' => 'The option name may not be greater than 20 characters.',
            ]
        );
        try {
            $option = $this->options->find($id);
            $option->name = $request->name;
            $option->option_group_id = $request->option_group_id;
            $option->save();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
        // return redirect()->route('options.index');
    }

    public function destroy($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('option.delete');
        try {
            # decrypt value
            // $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $option = $this->options->findById($id);
            $option->delete();
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
        $this->webspice->permissionVerify('option_group.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $option = option_group::withTrashed()->findOrFail($id);
            $option->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('option_group.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $option = option_group::withTrashed()->findOrFail($id);
            $option->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('options.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('options.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('option_group.restore');
        try {
            $options = option_group::onlyTrashed()->get();
            foreach ($options as $option) {
                $option->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('options.index');
        // return redirect()->route('options.index')->withSuccess(__('All options restored successfully.'));
    }

    public function getDiscountTypes(){
        $data = Option::where('option_group_id',4)->where('status', 1)->get();
        return response()->json($data);
    }
    public function getPaymentMethods(){
        $data = Option::where('option_group_id',2)->where('status', 1)->get();
        return response()->json($data);
    }

}
