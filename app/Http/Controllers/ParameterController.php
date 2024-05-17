<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;
use Illuminate\Http\Request;

class ParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {

        $data = [
            'parameter_profile' => AppSettings::getParameterProfile(),
        ];

        if (session('message')) {
            $data['message'] = session('message');
        }
        return view('settings.parameter.index', $data);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Trusting user input here...
        if (is_array($request->input('parameter_profile'))) {
            AppSettings::update([
                'key' => 'parameter_profile',
                'value' => $request->input('parameter_profile'),
            ]);
        }

        return redirect()->route('settings.parameter')//->with('message', 'Settings saved')
            ;
    }

}
