<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\Author;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;

class AuthorController extends Controller
{
    public $webspice;
    protected $author;
    protected $authors;
    protected $authorid;
    public $tableName;

    public function __construct(Author $author, Webspice $webspice)
    {
        $this->webspice = $webspice;
        $this->authors = $author;
        $this->tableName = 'authors';
        $this->middleware('JWT');
    }

    public function index()
    {
        #permission verfy
        $this->webspice->permissionVerify('author.manage');

        try {
            $paginate = request('paginate', 10);
            $searchTerm = request('search', '');

            $sortField = request('sort_field', 'created_at');
            if (!in_array($sortField, [
                'id',
                'author_name',
                'author_email',
                'author_phone',
                'author_address',
            ])) {
                $sortField = 'created_at';
            }
            $sortDirection = request('sort_direction', 'created_at');
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }

            $filled = array_filter(request([
                'id',
                'author_name',
                'author_email',
                'author_phone',
                'author_address',
            ]));

            $authors = Author::when(count($filled) > 0, function ($query) use ($filled) {
                foreach ($filled as $column => $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                }

            })->when(request('search', '') != '', function ($query) use ($searchTerm) {
                $query->search(trim($searchTerm));
            })->orderBy($sortField, $sortDirection)->paginate($paginate);

            return response()->json($authors);
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
         $this->webspice->permissionVerify('author.create');

        $request->validate(
            [
                'author_name' => 'required|regex:/^[a-zA-Z 0-9]+$/u|min:3|max:50|unique:authors',
              
                'author_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ],
            [
                'author_name.required' => 'author name field is required.',
                'author_phone.unique' => 'The author phone has already been taken.',
                'author_name.regex' => 'The author name format is invalid. Please enter alpabatic text.',
                'author_name.min' => 'The author name must be at least 3 characters.',
                'author_name.max' => 'The author name may not be greater than 20 characters.',
            ]
        );

        try {
            // $this->authors->create($data);
            $input = $request->all();
            if ($request->hasFile('author_photo')) {
                $image = Image::make($request->file('author_photo'));
                $imageName = time() . '-' . $request->file('author_photo')->getClientOriginalName();

                $destinationPath = 'assets/img/author/';
                $uploadSuccess = $image->save($destinationPath . $imageName);

                /**
                 * Generate Thumbnail Image Upload on Folder Code
                 */
                $destinationPathThumbnail = public_path('assets/img/author/thumbnail/');
                $image->resize(50, 50);
                $image->save($destinationPathThumbnail . $imageName);

                // $file = $request->file('author_photo');
                // $filename = $file->getClientOriginalName();
                // $uploadedPath = $file->move(public_path($destinationPath), $filename);
                if ($uploadSuccess) {
                    $input['author_photo'] = $imageName;
                }
            }
            $input['created_by'] = $this->webspice->getUserId();

            $this->authors->create($input);
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
            $author = Author::find($id);
            return $author;
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
        // dd($request->isMethod('put'));
        #permission verfy
        $this->webspice->permissionVerify('author.edit');

        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

        $request->validate(
            [
                'author_name' => 'required|regex:/^[a-zA-Z 0-9]+$/u|min:3|max:50|unique:authors,author_name,' . $id,               
                'author_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ],
            [
                'author_name.required' => 'author Name field is required.',
                'author_phone.unique' => '"' . $request->author_phone . '" The author phone has already been taken.',
                'author_name.regex' => 'The author name format is invalid. Please enter alpabatic text.',
                'author_name.min' => 'The author name must be at least 3 characters.',
                'author_name.max' => 'The author name may not be greater than 20 characters.',
            ]
        );
        try {
            $author = Author::find($id);
            $author->author_name = $request->author_name;
            $author->author_phone = $request->author_phone;
            $author->author_email = $request->author_email;
            $author->author_address = $request->author_address;
            $author->author_country = $request->author_country;
            if ($request->hasFile('author_photo')) {
                $image = Image::make($request->file('author_photo'));
                $imageName = time() . '-' . $request->file('author_photo')->getClientOriginalName();

                $destinationPath = 'assets/img/author/';
                $uploadSuccess = $image->save($destinationPath . $imageName);

                /**
                 * Generate Thumbnail Image Upload on Folder Code
                 */
                $destinationPathThumbnail = public_path('assets/img/author/thumbnail/');
                $image->resize(50, 50);
                $image->save($destinationPathThumbnail . $imageName);
                if ($uploadSuccess) {
                    //Delete Old File
                    $imgExist = Author::where('id', $id)->first();
                    $existingImage = $imgExist->author_photo;
                    if ($existingImage) {
                      
                        if (Storage::disk('local')->exists($destinationPath . $existingImage)) {                           
                            unlink($destinationPath . $existingImage);
                        }
                        if (Storage::disk('local')->exists($destinationPathThumbnail . $existingImage)) {
                            unlink($destinationPathThumbnail . $existingImage);
                        }
                    }
                    $author->author_photo = $imageName;
                }
            }
            $author->updated_by = $this->webspice->getUserId();
            $author->save();
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
        // return redirect()->route('authors.index');
    }

    public function destroy($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('author.delete');
        try {
            # decrypt value
            // $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $author = $this->authors->findById($id);
            $author->delete();
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
        $this->webspice->permissionVerify('author.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $author = Author::withTrashed()->findOrFail($id);
            $author->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('author.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $author = Author::withTrashed()->findOrFail($id);
            $author->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('authors.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('authors.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('author.restore');
        try {
            $authors = Author::onlyTrashed()->get();
            foreach ($authors as $author) {
                $author->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('authors.index');
        // return redirect()->route('authors.index')->withSuccess(__('All authors restored successfully.'));
    }

    public function getAuthors()
    {
        $data = Author::where('status', 1)->get();
        return response()->json($data);
    }

}
