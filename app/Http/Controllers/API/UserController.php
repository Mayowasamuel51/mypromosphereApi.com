<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use App\Models\Images;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller{

    public function trending($trending){
        //this function  will also show does thats have paid first before the free ones 
    }
    public function singhlePage()   {
        // this is the page were all the person post ads will be ... something like youtude channel
    }
    public function lastestPost(){
        // Note unlimted posting for Everyone!!!!!!!!!!!!!!!!!!
        /*
                                  THIS IS FOR FREE POEPLE 
         we need to show lastest people who has posted done,
         and aslo show there profile picture  done .
        also when they click on the type categories they go straight into the details page
         there will be sign of Premium post 
       */
        /*
        there will be Premium were when they pay monlthly they get ahead of people who did for free 
        amount example #10000, they will be become first in every parts   ............  .............. 
        */
        /*
        if you have paid all your post will rank first  on every page.............
        */
        $lastCategories =
            DB::table('users')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->latest()->get();


        // DB::select("SELECT profileImage, 
        // titleImage,categories,
        //  name FROM users INNER JOIN posts
        //   ON users.id = posts.user_id;")->latest();
        return response()->json([
            'latest' => $lastCategories
        ]);
    }
    public function searchbycategories($categories)
    {
        /// we need to show lastest people who has posted .

        // $post = Post::where('categories','Like', '%'.$categories.'%')->latest()->get();
        $categoriesSearch = Post::where('categories', $categories)->latest()->get();
        $categoriesCount = Post::where('categories', $categories)->latest()->count();
        if (!$categoriesSearch) {
            return response()->json([
                'categories' => 'nothing found'
            ]);
        }
        return response()->json([
            'categories' => $categoriesSearch,
            'total' => $categoriesCount
        ]);
    }
    public function index()
    {
        // wanto the post images and users info
        return response()->json([
            "index" => ['one']
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $post =  new Post;
        $post->name = $request->name;
        $post->save();

        foreach ($request->file('images') as $imagefile) {
            $image = new Images;
            $path = $imagefile->store('/images/resource', ['public' =>   'postuploads']);
            $image->url = $path;
            $image->product_id = $post->id;
            $image->save();
        }

        return response()->json([
            "name" => $post
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $post =     new Post;
        $filename = $request->titleImage;
        $folderPath = "public/titleImage/";
        $fileName =  uniqid() .  '.png';
        $file = $folderPath . $fileName;
        Storage::put($file, $filename);
        $post->user_id = Auth::user()->id;
        $post->productName    = $request->productName;
        $post->price = $request->price;
        $post->categories =  $request->categories;
        $post->description = $request->description;
        $post->usedOrnew = $request->usedOrnew;
        $post->titleImage = $fileName;
        $post->website = $request->website;
        $muitpleimages    =   array();
        if ($files = $request->file('muitpleimages')) {
            foreach ($files as $file) {
                $name =  $file->getClientOriginalName();
                $file->move('image', $name);
                $muitpleimages[] = $name;
            }
        }
        $createimageInfo = Images::create([
            'user_id' => Auth::user()->id,
            'muitpleimages' =>  implode("|", $muitpleimages),
        ]);
        $post->save();



        return response()->json([
            "data" => $post,
            'image' => $createimageInfo
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
