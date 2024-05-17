<?php

namespace App\Http\Controllers;

use App\Models\AppSettings;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $generalSettings = [];
        $generalSettings['devices_name'] = AppSettings::getDevicesName();

        $data = [
            'generalSettings' => $generalSettings,
            'translation' => AppSettings::getTranslation(),
        ];

        if (session('message')) {
            $data['message'] = session('message');
        }
        return view('applications.settings.index', $data);

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
        if (is_array($request->devices_name)) {
            AppSettings::updateOrCreate([
                'key' => 'devices_name',
            ], [
                'value' => $request->devices_name,
            ]);
        }
        if (is_array($request->translation)) {
            AppSettings::updateOrCreate([
                'key' => 'translation',
            ], [
                'value' => $request->translation,
            ]);
        }
        return redirect()->route('app-settings')->with('message', 'Settings saved');
    }


}
