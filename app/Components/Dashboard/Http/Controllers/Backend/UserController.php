<?php namespace App\Components\Dashboard\Http\Controllers\Backend;

use App\Components\Dashboard\Http\Requests\UserRequest;
use App\Components\Dashboard\Repositories\PermissionRepository;
use App\Components\Dashboard\Repositories\RoleRepository;
use App\Components\Dashboard\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Libraries\MediaManager;

class UserController extends Controller {

    protected $user;
    protected $role;
    protected $permission;

    public function __construct( UserRepository $user, RoleRepository $role, PermissionRepository $perms)
    {
        parent::__construct();
        $this->user = $user;
        $this->role = $role;
        $this->permission = $perms;
    }

    public function index()
    {
        $users = $this->user->all();
        $title = "List Users";
        return view('Dashboard::' . $this->link_type . '.' . $this->current_theme . '.users.index', compact('title', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $title = "Create New User";
        $roles = $this->role->listRoles();
        return view('Dashboard::' . $this->link_type . '.' . $this->current_theme . '.users.create_edit', compact('title', 'roles'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(UserRequest $request, MediaManager $media)
    {
        $attr = $request->all();

        if( !isset($attr['avatar']) )
            $attr['avatar'] = 'demo/default/default.png';

        $attr['password'] = bcrypt($attr['password']);

        $user = $this->user->create($attr);
        //attach role
        $user->attachRole($request->get('role'));

        return redirect(route('backend.user.index'))->with('success_message', 'The account has been created');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $title = "Edit User";
        $user = $this->user->find($id);
        $roles = $this->role->listRoles();

        return view('Dashboard::' . $this->link_type . '.' . $this->current_theme . '.users.create_edit', compact('user', 'title', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id, UserRequest $request, MediaManager $media)
    {
        $attr = $request->all();
        $user = $this->user->find($id);
        if( isset($attr['avatar']) ){
            $attr['avatar']=$user->avatar;
        }

        if( isset($attr['password']) ){
            $attr['password'] = bcrypt($attr['password']);
        }
        //attach role
        $user->detachRole($user->roles()->first());
        $user->attachRole($request->get('role'));
        $this->user->update($user, $attr);
        return redirect()->back()->with('success_message', 'The account has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        if(file_exists(ltrim($this->user->find($id)->avatar, '/')) && $this->user->find($id)->avatar!='/demo/default/default.png'){
            unlink(ltrim($this->user->find($id)->avatar,'/'));
        }
        $this->user->find($id)->delete();
        return redirect()->back()->with('success_message', 'The account has been deleted');
    }

}