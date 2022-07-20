<?php

namespace App\Http\Controllers;

use App\Models\Dummy;
use App\Http\Resources\Dummy as DummyResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DummyController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Dummy::query();

        $query->orderByPriority();

        $records = $query->paginate($request->query('limit', 30));

        return DummyResource::collection($records);
    }
}
