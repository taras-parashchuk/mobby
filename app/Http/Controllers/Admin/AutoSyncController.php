<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\DeleteAutoSync;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\XmlAddToQueueRequest;
use App\Models\Sync;
use Illuminate\Http\Request;

class AutoSyncController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return response(Sync::auto()->get());
    }


    public function store(XmlAddToQueueRequest $request)
    {
        //
        $request->validateResolved();

        $eAutoSync = new Sync();

        $eAutoSync->data_type = $request->input('data_type');
        $eAutoSync->manually = false;
        $eAutoSync->link = $request->input('link');
        $eAutoSync->configuration_id = $request->input('configuration_id');
        $eAutoSync->time_1 = $request->input('times.0');
        $eAutoSync->status = (boolean)$request->input('status');

        if($request->has('times.1')) $eAutoSync->time_2 = $request->input('times.1');

        $eAutoSync->save();

        return response()->json([
            'id' => $eAutoSync->id,
            'text' => trans('form.result.success-created')
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(XmlAddToQueueRequest $request, $id)
    {

        $request->validateResolved();

        $eAutoSync = Sync::findOrFail($id);

        $eAutoSync->link = $request->input('link');
        $eAutoSync->configuration_id = $request->input('configuration_id');
        $eAutoSync->time_1 = $request->input('times.0');
        $eAutoSync->status = (boolean)$request->input('status');

        if($request->has('times.1')) $eAutoSync->time_2 = $request->input('times.1');

        $eAutoSync->update();

        return response()->json([
            'id' => $eAutoSync->id,
            'text' => trans('form.result.success-updated')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Sync::find($id)->delete();

        return response()->json([
            'text' => trans('form.result.success-deleted')
        ]);

    }
}
