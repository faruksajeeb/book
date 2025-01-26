<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Webspice;
use App\Models\Book;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Image;
use DB;

class BookController extends Controller
{
    public $webspice;
    protected $book;
    protected $books;
    protected $bookid;
    public $tableName;

    public function __construct(Book $book, Webspice $webspice)
    {
        $this->webspice = $webspice;
        $this->books = $book;
        $this->tableName = 'books';
        $this->middleware('JWT');
    }

    public function index()
    {
        #permission verfy
        $this->webspice->permissionVerify('book.manage');
        try {
            $paginate = request('paginate', 5);
            $searchTerm = request('search', '');

            $sortField = request('sort_field', 'created_at');
            if (!in_array($sortField, [
                'id',
                'title',
                'price',
                'stock_quantity',
            ])) {
                $sortField = 'created_at';
            }
            $sortDirection = request('sort_direction', 'created_at');
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }

            $filled = array_filter(request([
                'id',
                'title',
                'publisher_id',
                'category_id',
                'sub_category_id',
                'stock_quantity',
                'price',
            ]));

            $books = Book::with(['publisher', 'authors', 'category', 'sub_category'])
                ->when(count($filled) > 0, function ($query) use ($filled) {
                    foreach ($filled as $column => $value) {
                        $query->where($column, 'LIKE', '%' . $value . '%');
                    }
                })
                ->when(request('search', '') != '', function ($query) use ($searchTerm) {
                    $query->search(trim($searchTerm));
                })
                // ->when(!empty($searchTerm), function ($query) use ($searchTerm) {
                //     $query->where(function ($q) use ($searchTerm) {
                //         $q->whereHas('authors', function ($authorQuery) use ($searchTerm) {
                //             $authorQuery->where('author_name', 'LIKE', '%' . $searchTerm . '%');
                //         });
                //     });
                // })
                ->when(request('author_id'), function ($query) {
                    // If author_id is provided, filter books by specific authors
                    $authorIds = is_array(request('author_id')) ? request('author_id') : [request('author_id')];
                    $query->whereHas('authors', function ($authorQuery) use ($authorIds) {
                        $authorQuery->whereIn('id', $authorIds);
                    });
                })
                ->orderBy($sortField, $sortDirection)
                ->paginate($paginate);

            return response()->json($books);
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
        $this->webspice->permissionVerify('book.create');
        $request->validate(
            [
                'title' => [
                    'required',
                    'min:1',
                    'max:1000',
                    Rule::unique('books')
                        ->where(function ($query) use ($request) {
                            return $query->where('publisher_id', $request->publisher_id);
                        }),
                    function ($attribute, $value, $fail) use ($request) {
                        // Check if the combination of title, publisher_id, and author_id already exists
                        $exists = DB::table('book_author')
                            ->join('books', 'books.id', '=', 'book_author.book_id')
                            ->where('books.title', $value)
                            ->where('books.publisher_id', $request->publisher_id)
                            //->whereIn('book_author.author_id', $request->selected_authors)
                            ->exists();

                        if ($exists) {
                            $fail('The combination of title, publisher_id, and selected_authors has already been taken.');
                        }
                    },
                ],
                'publisher_id' => 'required',
                'selected_authors' => 'required|array|min:1',
                'selected_authors.*' => 'exists:authors,id',
                'buying_discount_percentage' => 'required|numeric',
                'selling_discount_percentage' => 'required|numeric',
                'buying_vat_percentage' => 'required|numeric',
                'selling_vat_percentage' => 'required|numeric',
                'price' => ['required', 'numeric', 'min:0.01'],
                'publication_year' => ['required', 'integer', 'between:1950,2050', 'date_format:Y'],
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ],
            [
                'title.unique' => 'The book (title,publisher_id,selected_authors) has already been taken.',
            ]
        );

        try {
            // $input = $request->all();
            $input = $request->except(['photo', 'selected_authors']);
            
            if ($request->hasFile('photo')) {
                $image = Image::make($request->file('photo'));
                $imageName = time() . '-' . $request->file('photo')->getClientOriginalName();

                $destinationPath = 'assets/img/book/';
                $uploadSuccess = $image->save($destinationPath . $imageName);

                /**
                 * Generate Thumbnail Image Upload on Folder Code
                 */
                $destinationPathThumbnail = public_path('assets/img/book/thumbnail/');
                $image->resize(50, 50);
                $image->save($destinationPathThumbnail . $imageName);

                // $file = $request->file('photo');
                // $filename = $file->getClientOriginalName();
                // $uploadedPath = $file->move(public_path($destinationPath), $filename);
                if ($uploadSuccess) {
                    $input['photo'] = $imageName;
                }
            }
            
            $input['created_by'] = $this->webspice->getUserId();
         
            // Create the book entry
            $book = $this->books->create($input);

            // Attach authors to the book
            if ($request->has('selected_authors')) {
                $book->authors()->attach($request->selected_authors); // Proper many-to-many relationship handling
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
            $book = Book::with(['publisher', 'authors', 'category', 'sub_category'])->find($id);
            return $book;
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
        // dd($request->all());
        #permission verfy
        $this->webspice->permissionVerify('book.edit');

        # decrypt value
        // $id = $this->webspice->encryptDecrypt('decrypt', $id);

        $request->validate(
            [
                'title' => [
                    'required',
                    'min:1',
                    'max:1000',
                    Rule::unique('books')
                        ->ignore($id) // Ignore the current book being updated
                        ->where(function ($query) use ($request) {
                            return $query->where('publisher_id', $request->publisher_id);
                        }),
                    function ($attribute, $value, $fail) use ($request, $id) {
                        // Check if the combination of title, publisher_id, and author_id already exists
                        $exists = DB::table('book_author')
                            ->join('books', 'books.id', '=', 'book_author.book_id')
                            ->where('books.title', $value)
                            ->where('books.publisher_id', $request->publisher_id)
                            ->whereIn('book_author.author_id', $request->selected_authors)
                            ->where('books.id', '<>', $id) // Exclude the current book being updated
                            ->exists();

                        if ($exists) {
                            $fail('The combination of title, publisher_id, and author_id has already been taken.');
                        }
                    },
                ],
                'publisher_id' => 'required',
                'selected_authors' => 'required|array',
                'selected_authors.*' => 'exists:authors,id',
                'buying_discount_percentage' => 'required',
                'selling_discount_percentage' => 'required',
                'buying_vat_percentage' => 'required',
                'selling_vat_percentage' => 'required',
                'price' => ['required', 'numeric', 'min:0.01'],
                'publication_year' => 'required',
            ],
            [
                'title.unique' => 'The book (title,publisher_id,selected_authors) has already been taken.',
            ]
        );
        try {
            $input = $request->except(['photo', 'selected_authors']);
            
            if ($request->hasFile('photo')) {
                $image = Image::make($request->file('photo'));
                $imageName = time() . '-' . $request->file('photo')->getClientOriginalName();

                $destinationPath = 'assets/img/book/';
                $uploadSuccess = $image->save($destinationPath . $imageName);

                /**
                 * Generate Thumbnail Image Upload on Folder Code
                 */
                $destinationPathThumbnail = public_path('assets/img/book/thumbnail/');
                $image->resize(50, 50);
                $image->save($destinationPathThumbnail . $imageName);
                if ($uploadSuccess) {
                    //Delete Old File
                    $imgExist = Book::where('id', $id)->first();
                    $existingImage = $imgExist->photo;
                    if ($existingImage) {

                        if (Storage::disk('local')->exists($destinationPath . $existingImage)) {
                            unlink($destinationPath . $existingImage);
                        }
                        if (Storage::disk('local')->exists($destinationPathThumbnail . $existingImage)) {
                            unlink($destinationPathThumbnail . $existingImage);
                        }
                    }
                    $input['photo'] = $imageName;
                }
            }
            
            $input['updated_by'] = $this->webspice->getUserId();
            // Fetch the book by ID and update
            $book = Book::findOrFail($id);
            $book->update($input); // Update book details

            // Sync authors for many-to-many relationship
            $book->authors()->sync($request->selected_authors);
        } catch (Exception $e) {
            // $this->webspice->message('error', $e->getMessage());
            return response()->json(
                [
                    'error' => $e->getMessage(),
                ], 401);
        }
        
    }

    public function destroy($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('book.delete');
        try {
            # decrypt value
            // $id = $this->webspice->encryptDecrypt('decrypt', $id);

            $book = $this->books->findById($id);
            $book->delete();
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
        $this->webspice->permissionVerify('book.force_delete');
        try {
            #decrypt value
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $book = Book::withTrashed()->findOrFail($id);
            $book->forceDelete();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->back();
    }
    public function restore($id)
    {
        #permission verfy
        $this->webspice->permissionVerify('book.restore');
        try {
            $id = $this->webspice->encryptDecrypt('decrypt', $id);
            $book = Book::withTrashed()->findOrFail($id);
            $book->restore();
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        // return redirect()->route('books.index', ['status' => 'archived'])->withSuccess(__('User restored successfully.'));
        return redirect()->route('books.index');
    }

    public function restoreAll()
    {
        #permission verfy
        $this->webspice->permissionVerify('book.restore');
        try {
            $books = Book::onlyTrashed()->get();
            foreach ($books as $book) {
                $book->restore();
            }
        } catch (Exception $e) {
            $this->webspice->message('error', $e->getMessage());
        }
        return redirect()->route('books.index');
        // return redirect()->route('books.index')->withSuccess(__('All books restored successfully.'));
    }

    public function getbooks()
    {
        $data = Book::where('status', 1)->get();
        return response()->json($data);
    }

    public function getStockQuantity($productId)
    {
        $product = Book::find($productId);

        if ($product) {
            return response()->json(['stock_quantity' => $product->stock_quantity]);
        } else {
            return response()->json(['message' => 'Book not found'], 404);
        }
    }


}
