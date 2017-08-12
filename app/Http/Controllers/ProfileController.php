<?php

// Todo: Add logo upload

namespace App\Http\Controllers;

use Auth;
use Route;
use Image;
use App\Profile;
use App\Http\Requests\ProfileRequest;

class ProfileController extends Controller
{
    /**
     * ProfileController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth')->only([
            'myProfiles', 'create', 'store',
            'edit', 'update'
        ]);
    }

    /**
     * Get the index of all profiles.
     *
     * @return $this
     */
    public function index()
    {
        $view = $this->getTemplateByRoute();
        $profiles = $this->getProfilesByRoute();

        return view('profiles.' . $view)->with([
            'profiles' => $profiles
        ]);
    }

    /**
     * Returns the (paginated) list of profiles based on the current route.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|static[]
     */
    private function getProfilesByRoute()
    {
        switch(Route::currentRouteName())
        {
            case 'guide':return Profile::paginate(16);break;
            case 'guide.map':return Profile::all();break;
            case 'guide.list':return Profile::paginate(25);break;
        }
    }

    /**
     * Returns the template name based on the current route.
     *
     * @return string
     */
    private function getTemplateByRoute()
    {
        switch(Route::currentRouteName())
        {
            case 'guide':return 'cards';break;
            case 'guide.map':return 'map';break;
            case 'guide.list':return 'table';break;
        }
    }

    /**
     * List the current users profiles.
     *
     * @return $this
     */
    public function myProfiles()
    {
        return view('profiles.table')->with([
            'profiles' => Auth::user()->profiles
        ]);
    }

    /**
     * Create a profile.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $profile = new Profile();

        return view('profiles.manage.create')->with(['profile' => $profile]);
    }

    /**
     * Save a profile to the database.
     *
     * @param ProfileRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(ProfileRequest $request)
    {
        $input = $request->all();
        $input['logo'] = $this->uploadLogo();
        $input['date']  = \Carbon\Carbon::createFromFormat('Y', $input['founded_at']);

        $profile = Profile::create($input);

        Auth::user()->profiles()->attach($profile);

        flash('Het profiel toegevoegd.');

        return redirect(route('profile.list'));
    }

    /**
     * Edit an existing profile.
     *
     * @param Profile $profile
     * @return $this
     */
    public function edit(Profile $profile)
    {
        if(!Auth::user()->profiles->contains($profile)) {
            flash('Je hebt geen toegang tot dit profiel', 'error');

            return redirect(route('profile.list'));
        }

        return view('profiles.manage.edit')->with(['profile' => $profile]);
    }

    /**
     * Update a profile.
     *
     * @param ProfileRequest $request
     * @param Profile $profile
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(ProfileRequest $request, Profile $profile)
    {
        if(!Auth::user()->profiles->contains($profile)) {
            flash('Je hebt geen toegang tot dit profiel', 'error');

            return redirect(route('profile.list'));
        }

        $input = $request->except(['_token', '_method']);
        $input['logo'] = $this->uploadLogo($profile->logo);
        $input['date']  = \Carbon\Carbon::createFromFormat('Y', $input['founded_at']);

        $profile->update($input);
    }

    /**
     * Delete the logo.
     *
     * @param Profile $profile
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function removeLogo(Profile $profile)
    {
        if(file_exists(storage_path('uploads/logos/' . $profile->logo)) && $profile->logo != "") {
            File::delete(public_path('uploads/articles/' . $profile->logo));
            $profile->image = '';
            $profile->save();

            flash('Het logo is verwijderd.');
        }

        return redirect(route('profile.edit', [$profile->slug]));
    }

    /**
     * Upload a logo.
     *
     * @param string $old
     * @return string
     */
    public function uploadLogo($old = '')
    {
        if (Request::hasFile('logo'))
        {
            $image = Request::file('logo');
            $filename  = time() . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $path = public_path('uploads/logos/' . $filename);

            try
            {
                Image::make($image->getRealPath())->resize(400, 400)->save($path);

                if($old != "") {
                    if(file_exists(public_path('uploads/logos/'.$old))) {
                        File::delete(public_path('uploads/logos/' . $old));
                    }
                }

                return $filename;
            } catch (Exception $e) {
                return $old;
            }
        }

        return $old;
    }

    /**
     * Delete a profile.
     *
     * @param Profile $profile
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(Profile $profile)
    {
        if(!Auth::user()->profiles->contains($profile)) {
            flash('Je hebt geen toegang tot dit profiel', 'error');

            return redirect(route('profile.list'));
        }

        $profile->delete();

        flash('Het bedrijfsprofiel is verwijderd.');

        return redirect(route('profile.list'));
    }
}
