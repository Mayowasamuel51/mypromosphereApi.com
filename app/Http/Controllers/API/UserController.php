<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\User;
use Yabacon\Paystack;
use App\Models\Images;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    public function initializeTransaction(Request $request)
    {
        // API endpoint URL
        $url = "https://api.paystack.co/transaction/verify/:" . $request->reference;
        // Request data
        $data = [
            'email' => "customer@email.com",
            'amount' => "20000",
        ];

        // Set headers
        $headers = [
            "Authorization" => "Bearer YOUR_SECRET_KEY",
            "Cache-Control" => "no-cache",
        ];

        // Send POST request using Laravel's HTTP client
        $response = Http::withHeaders($headers)->post($url, $data);

        // Get and return the response content
        return $response->body();
    }
    public function publickey()
    {
        return response()->json([
            'publickey' => "pk_test_7ce5ec2ad909fa7b6203f6e43667889d0c2555db",
            'data' => 'ok'
        ]);
    }
    public function finalpayement(Request $request)
    {
    }
    public function updatereference(Request $request)
    {
        //update
        // $this->validate([
        //     'reference'=>'required'
        // ])
        // $auth = Auth::user()->id;
        $affected = DB::table('payments')
            ->where('user_id', $request->id)
            ->update(['TransactionID' => $request->reference]);

        return response()->json([
            'status' => 200,
            'data' => $affected
        ]);
    }
    public function payment(Request $request)
    {
        // check for the auth status ...............
        $headers = "pk_test_7ce5ec2ad909fa7b6203f6e43667889d0c2555db";
        $plan = "PLN_kcp1464jaxs7o74";
        $plan_code = 100;
        $payment = new  Payment;
        $payment->paymentmode = $request->paymentmode;
        $payment->email = $request->email;
        $payment->amount = $request->amount;
        $payment->status = $request->status;
        $payment->save();
        return response()->json([
            'status' => 200,
            'amount' => $plan_code,
            'headers' => $headers,
            'worked' => $payment
        ]);
        // Auth::user()->id;
        // $publickey= "pk_test_7ce5ec2ad909fa7b6203f6e43667889d0c2555db";
        // $data = [
        //     'email' => $request->email,
        //     'amount' => 40000,
        //     'payment'=>$request->paymentmode,
        //     'plan' => "PLN_kcp1464jaxs7o74",
        // ];

        //

        // $response = Http::withHeaders($headers)->post($url, $data);

        // return $response->body();

    }
    public function trending($trending)
    {
        //this function  will also show does thats have paid first before the free ones 
    }
    public function singhlePage()
    {
        // this is the page were all the person post ads will be ... something like youtude channel
    }
    public function lastestPost()
    {
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
            ->get();
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
        $post = new Post;
        $filename = $request->titleImage;
        $folderPath = "public/titleImage/";
        $fileName =  uniqid() .  '.png';
        $file = $folderPath . $fileName;
        Storage::put($file, $filename);
        $post->user_id = Auth::user()->id;
        $post->productName = $request->productName;
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
    public function showing(string $id)
    {
        $show_one_user = User::find($id);
        return response()->json([
            'status' => 200,
            'show' => $show_one_user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request,$id)
    // {
    //     // $user_auth =  Auth::user()->id;
    //     $user_infomation =  User::find($id);
    //     if (!$user_infomation) {
    //         // $user_infomation->profileImage = $request->profi
    //         // $user_infomation->password
    //         $user_infomation->name = $request->name;
    //         $user_infomation->countrys = $request->countrys;
    //         $user_infomation->location = $request->location;
    //         $user_infomation->websiteName = $request->websiteName;
    //         $user_infomation->messageCompany = $request->messageCompany;
    //         $user_infomation->aboutMe = $request->aboutMe;

    //         $user_infomation->save();

    //         return response()->json([
    //             'updated' => $user_infomation
    //         ]);
    //     }
    //     return response()->json([
    //         'not found' => 'error'
    //     ]);
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function updateuserinfo(Request $request, $iduser)
    {
        $validator = Validator::make($request->all(), [
            // 'names' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages(),
            ]);
        } else {
            $all_data = $request->all();
            if (auth('sanctum')->check()) {
                $user_infomation =
                    // User::where('id',$iduser)->first();
                    User::findOrFail($iduser);
                // $user_auth =  Auth::user()->id;
                // $user_infomation->profileImage = $request->profi
                // $user_infomation->password
                // $user_infomation->id = $user_auth;
                if ($user_infomation) {
                    $user_infomation->name = $request->names;
                    // $user_infomation->countrys = $request->countrys;
                    // $user_infomation->location = $request->location;
                    // $user_infomation->websiteName = $request->websiteName;
                    // $user_infomation->messageCompany = $request->messageCompany;
                    // $user_infomation->aboutMe = $request->aboutMe;
                    $user_infomation->update();
                    // return response()->json([
                    //     'status'=>200,
                    //     'updated' => $user_infomation
                    // ]);
                    return response()->json([
                        'status' => 200,
                        'd' => $request->names,
                        'age' => $request->age,
                        'data' => $all_data,
                        'updated' => $user_infomation
                    ]);
                }
            }
        }
    }

    public function getdata(Request $request, $id)
    {
        if (auth('sanctum')->check()) {
            // try {
            $user_infomation =  User::findorFail($id);
            $user_infomation =  User::findorFail($id);
            $user_infomation->id= Auth::id($id);
            // $user_infomation->countrys = 'sssssdsd';
            $user_infomation->location = $request->location;
            $user_infomation->websiteName = $request->websiteName;
            $user_infomation->messageCompany = $request->messageCompany;
            $user_infomation->aboutMe = $request->aboutMe;
            $user_infomation->brandName = $request->brandName;
            $user_infomation->save();
            return response()->json([
                'status' => 200,
                'updated' => $user_infomation
            ]);
            // } catch (\Exception $e) {
            //     return response()->json([
            //         'status' => 500,
            //         'error' => $e->getMessage()
            //     ]);
            // }
        } else {
            return response()->json([
                'status' => 500,
                'error' => 'not allowed!!!'
            ]);
        }
    }
}
