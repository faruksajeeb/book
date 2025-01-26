<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoryExport;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public $webspice;
    protected $category;
    protected $categories;
    protected $categoryid;
    public $tableName;

    public function __construct(Category $category, Webspice $webspice)
    {
        $this->webspice = $webspice;
        $this->categories = $category;
        $this->tableName = 'categories';
        $this->middleware('JWT');
    }

    public function index()
    {

        #permission verfy
        $this->webspice->permissionVerify('category.manage');

        try {
            $paginate = request('paginate', 5);
            $searchTerm = request('search', '');

            $sortField = request('sort_field', 'created_at');
            if (!in_array($sortField, ['id', 'category_name'])) {
                $sortField = 'created_at';
            }
            $sortDirection = request('sort_direction', 'created_at');
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }

            $filled = array_filter(request([
                'id',
                'category_name',
            ]));

            $categories = Category::when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }

            })->when(request('search', '') != '', function ($query) use ($searchTerm) {
                $query->search(trim($searchTerm));
            })->orderBy($sortField, $sortDirection)->paginate($paginate);

            // return ProductResource::collection($products);
            return response()->json($categories);
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
        $this->webspice->permissionVerify('category.create');

        $request->validate(
            [
                'category_name' => 'required|regex:/^[a-zA-Z 0-9]+$/u|min:3|max:20|unique:categories',
            ],
            [
                'category_name.required' => 'category name field is required.',
                'category_name.unique' => 'The category name has already been taken.',
                'category_name.regex' => 'The category name format is invalid. Please enter alpabatic text.',
                'category_name.min' => 'The category name must be at least 3 characters.',
                'category_name.max' => 'The category name may not be greater than 20 characters.',
            ]
        );

        try {
            // $this->categories->create($data);
            $input = $request->all();
            $input['slug'] = Str::slug($request->category_name);
            $input['created_by'] = $this->webspice->getUserId();
          
            $this->categories->create($input);
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
            $category = Category::find($id);
            return $category;
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
        $this->webspice->permissionVerify('category.edit');

        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

        $request->validate(
            [
                'category_name' => 'required|regex:/^[a-zA-Z 0-9]+$/u|min:3|max:20|unique:categories,category_name,' . $id,
            ],
            [
                'category_name.required' => 'category Name field is required.',
                'category_name.unique' => '"' . $request->category_name . '" The category name has already been taken.',
                'category_name.regex' => 'The category name format is invalid. Please enter alpabatic text.',
                'category_name.min' => 'The category name must be at least 3 characters.',
                'category_name.max' => 'The category name may not be greater than 20 characters.',
            ]
        );
        // try {  
            $category = Category::find($id);         
            $category->category_name = $request->category_name;
            $category->updated_by = $this->webspice->getUserId();
            $category->save();
        // } catch (Exception $e) {
        //     // $this->webspice->message('error', $e->getMessage());
        //     return response()->json(
        //         [
        //             'error' => $e->getMessage(),
        //         ], 401);
        // }
        // return redirect()->route('categories.index');
    }

    public function destroy($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('category.delete');
        try {
            # decrypt value
            // $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $category = $this->categories->findById($id);
            $category->delete();
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
        $this->webspice->permissionVerify('category.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $category = category::withTrashed()->findOrFail($id);
            $category->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('category.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $category = category::withTrashed()->findOrFail($id);
            $category->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('categories.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('categories.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('category.restore');
        try {
            $categories = category::onlyTrashed()->get();
            foreach ($categories as $category) {
                $category->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('categories.index');
        // return redirect()->route('categories.index')->withSuccess(__('All categories restored successfully.'));
    }


    
    public function export()
    {
        try {
            // dd('hello');
            ini_set('max_execution_time', 30 * 60); //30 min
            ini_set('memory_limit', '2048M');
            return Excel::download(new CategoryExport, 'category.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }
    public function exportPdf()
    {
        try {
            // dd('hello');
            ini_set('max_execution_time', 30 * 60); //30 min
            ini_set('memory_limit', '2048M');
            $data = [];
            $pdf = PDF::loadView('pdf-export.category', ['data' => $data]);
            return $pdf->output();
            // return $pdf->download('itsolutionstuff.pdf');
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

    }

    public function import()
    {
        // Excel::import(new CategoryImport, request()->file('file'));

        return back();
    }


    public function getCategories()
    {
        $data = Category::where('status', 1)->get();
        return response()->json($data);
    }

}
