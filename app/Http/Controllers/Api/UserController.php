<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(Request $request){
        if(Auth::attempt([
                'username' => $request->username, 
                'password' => $request->password
            ])){
            $user = Auth::user();
            $org=DB::table('organizations')->where('id', $user->organization_id)->first();
            if($request->isWeb=="1") {
                if($org->is_str!=1) return response()->json(['message' => 'Unauthorized'], 401);
            }
            $success['company_id'] = $user->company_id;
            $success['file'] = $user->file;
            $success['accessToken'] = $user->createToken('nApp')->accessToken;
            return response()->json($success, $this->successStatus);
        }
        else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
            'file' => 'required',
            'name' => 'required',
            'password' => 'required',
            'company_id' => 'required',
            'organization_id' => 'required',
            'nik' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);            
        }

        /* START FILE UPLOAD */
        $file64 = $request->file;
        $ext = explode('/', explode(':', substr($file64, 0, strpos($file64, ';')))[1])[1];
        $replace = substr($file64, 0, strpos($file64, ',')+1);
        $file = str_replace($replace, '', $file64);
        $file = str_replace(' ', '+', $file);
        $filename = Str::random(10).'.'.$ext;
        Storage::disk('public')->put('files/'.$filename, base64_decode($file));
        /* END FILE UPLOAD */

        $username="";
        $company=DB::table('companies')->where('id', $request->company_id)->first();
        $username=$username.$company->code;
        $org=DB::table('organizations')->where('id', $request->organization_id)->first();
        $username=$username.$org->code;
        $username=$username.$request->nik;

        $cek=DB::table('users')->where('nik', $request->nik)->count();
        if($cek>0) return response()->json(['message' => 'NIK sudah terdaftar'],500);

        DB::beginTransaction();
        $userGetId=DB::table('users')->insertGetId([
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'company_id' => $request->company_id,
            'image' => '',
            'file' => 'files/'.$filename,
            'organization_id' => $request->organization_id,
            'nik' => $request->nik,
            'username' => $username
        ]);
        
        if($request->filled('image')) {
            $imgName='';
            $baseString = explode(';base64,', $request->image);
            $image = base64_decode($baseString[1]);
            $image = imagecreatefromstring($image);

            $ext = explode('/', $baseString[0]);
            $ext = $ext[1];
            $imgName = 'user_'.uniqid().'.'.$ext;
            if($ext=='png'){
                imagepng($image,public_path().'/files/'.$imgName,8);
            } else {
                imagejpeg($image,public_path().'/files/'.$imgName,20);
            }
            DB::table('users')->where('id', $userGetId)->update(['image' => $imgName]);
        }
        DB::commit();
        return response()->json(['data' => $userGetId, 'message' => 'Data berhasil disimpan!'], $this->successStatus);
    }

    public function details()
    {
        $user = User::with(['company','organization'])
            ->where('id', Auth::user()->id)
            ->get();
        return response()->json(['message' => 'success','data' => $user], $this->successStatus);
    }

    public function detailsUser($id)
    {
        $user = User::with(['company','organization'])
            ->where('id', $id)
            ->first();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function index()
    {
        $user = User::with(['company','organization'])
            ->where('company_id', Auth::user()->company_id)
            ->orderBy('id', 'DESC')
            ->get();
        return response()->json(['data' => $user]);
    }

    public function update(request $request, $id)
    {
        $name = $request->name;
        $nik = $request->nik;
        $company_id = $request->company_id;
        $organization_id = $request->organization_id;
        $image = '';
        $file = $request->file;

        /* START FILE UPLOAD */
        $file64 = $request->file;
        $ext = explode('/', explode(':', substr($file64, 0, strpos($file64, ';')))[1])[1];
        $replace = substr($file64, 0, strpos($file64, ',')+1);
        $file = str_replace($replace, '', $file64);
        $file = str_replace(' ', '+', $file);
        $filename = Str::random(10).'.'.$ext;
        Storage::disk('public')->put('files/'.$filename, base64_decode($file));
        /* END FILE UPLOAD */

        if($request->filled('image')) {
            $imgName='';
            $baseString = explode(';base64,', $request->image);
            $image = base64_decode($baseString[1]);
            $image = imagecreatefromstring($image);

            $ext = explode('/', $baseString[0]);
            $ext = $ext[1];
            $imgName = 'user_'.uniqid().'.'.$ext;
            if($ext=='png'){
                imagepng($image,public_path().'/files/'.$imgName,8);
            } else {
                imagejpeg($image,public_path().'/files/'.$imgName,20);
            }
        }

        $user = User::find($id);
        $user->image = $imgName;
        $user->file = 'files/'.$filename;
        $user->name = $name;
        $user->nik = $nik;
        $user->company_id = $company_id;
        $user->organization_id = $organization_id;
        $user->save();

        return response()->json([
            'success'=>$user,
            'message'=>'Update Successfully'], 
        $this->successStatus);
    }

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();
        return response()->json(['message' => 'Delete Successfully']);
    }

    public function logout(Request $request)
    {
        auth()->user()->token()->revoke();
        return response()->json(['message' => 'Logout Success']);
    }
}
