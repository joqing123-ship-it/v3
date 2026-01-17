<?php

namespace App\Http\Controllers\v1;

use App\Models\report;
use App\Http\Requests\StorereportRequest;
use App\Http\Requests\UpdatereportRequest;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return request()->user()->reports()->paginate(10);
    }
    public function postReports(){

        return report::query()->where('reportable_type', 'App\Models\post')->get();
    }
    public function commentReports(){

        return report::query()->where('reportable_type', 'App\Models\comment')->get();
    }
    public function replyReports(){

        return report::query()->where('reportable_type', 'App\Models\reply')->get();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      $data =  $request->validate([
          "type" => "required|in:post,comment,reply",
          "id" => "required", // post_id, comment_id, reply_id
          "reason" => "required|string",
        ]);
        $id = $data['id'];
        $type = $data['type'];
        $reason = $data['reason'];
        return toggle_report($type, $id, $request->user()->id, $reason);
    }

    /**
     * Display the specified resource.
     */
    public function show(report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatereportRequest $request, report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(report $report)
    {
        //
    }
}
