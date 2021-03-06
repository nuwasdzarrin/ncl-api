<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class LeaderboardController extends Controller
{
    public $successStatus = 200;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = DB::table('leaderboards as s')
        ->leftJoin('users as u','u.id','s.user_id')
        ->where('u.organization_id', $request->organization_id)
        ->orderBy('s.point', 'DESC')
        ->select([
            'u.username',
            'u.name',
            's.point',
            's.level'
        ])->get();
        return response()->json(['data' => $data]);
    }
    
    // public function user_course(Request $request)
    // {
    //     $dt = DB::table('user_scores as us');
    //     $dt = $dt->leftJoin('courses as c','c.id','us.course_id');
    //     $dt = $dt->where('us.user_id', auth()->id());
    //     $dt = $dt->orderBy('c.id','DESC');
    //     $dt = $dt->selectRaw('
    //         us.id,
    //         us.score,
    //         us.status,
    //         c.id,
    //         c.image,
    //         c.title,
    //         c.description
    //     ')->get();
    //     return response()->json(['data' => $dt]);
    // }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
