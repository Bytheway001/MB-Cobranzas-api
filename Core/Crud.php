<?php

/**
 * Class to auto generate crud for Models.
 */

namespace Core;

use App\Libs\Inflect;

/**
 * Class to auto generate crud for Models.
 */
class Crud
{
    /**
     *	@var string The name of the resource for which crud will be generated.
     */
    private $resource_name;

    /**
     *	@var \App\Models\Model_name Stores an instance of the model class.
     */
    private $model;

    /**
     *	The constructor, which sets the name of the resource, (singular and plural) and sets an instance of it.
     */
    public function __construct() {
        $this->model_namespace = "\App\Models\\";
        $this->resource_name = Inflect::singularize(ucwords($this->resource));
        $this->model = $this->model_namespace.$this->resource_name;
    }

    /**
     * Action Show.
     *
     * @param int $id The id of the resource.
     */
    public function show(int $id) {
        $resource = call_user_func([$this->model, 'find'], [$id]);
        View::set(strtolower($this->resource_name), $resource);
        View::render(Inflect::pluralize(strtolower($this->resource_name)).'/show');
    }

    /**
     * Action Index.
     *
     * @param string $title The title of the view
     * @param string $view  The view that should be rendered
     */
    public function index(string $title = null, string $view = null) {
        $resources = call_user_func([$this->model, 'all']);
        if (count($resources) == 0) {
            $this->msg->info('No records found, redirecting to form', Inflect::pluralize(strtolower($this->resource_name)).'/new');
        }
        View::set(Inflect::pluralize(strtolower($this->resource_name)), $resources);
        if ($title == null) {
            View::set('title', strtolower($this->resource_name).'/index');
        } else {
            View::set('title', strtolower($title));
        }

        if ($view == null) {
            View::render('../../Core/crud/index');
        } else {
            View::render(strtolower($view));
        }
    }

    /**
     * Action New.
     *
     * @param string $title The title of the view
     * @param string $view  The view that should be rendered
     */
    public function add(string $title = null, string $view = null) {
        $resource = new $this->model();
        View::set(strtolower($this->resource_name), $resource);

        if ($title == null) {
            View::set('title', strtolower($this->resource_name).'/new');
        } else {
            View::set('title', strtolower($title));
        }

        if ($view == null) {
            View::render(Inflect::pluralize(strtolower($this->resource_name)).'/new');
        } else {
            View::render(strtolower($view));
        }
    }

    /**
     * Action Create.
     *
     * @param int $id The id of the resource.
     */
    public function create() {
        $resource = new $this->model($_POST[$this->resource_name]);
        if ($resource->save()) {
            $this->msg->success('Saved Successfully', '/'.$this->pluralized);
        } else {
            $this->msg->success("Couldn't save to database", '/'.$this->pluralized.'/new');
        }
    }

    /**
     * Action Edit.
     *
     * @param int $id The id of the resource.
     */
    public function edit(int $id) {
        $resource = call_user_func([$this->model, 'find'], [$id]);
        View::set(strtolower($this->resource_name), $resource);
        View::render(Inflect::pluralize(strtolower($this->resource_name)).'/edit');
    }

    /**
     * Action Update.
     *
     * @param int $id The id of the resource.
     */
    public function update(int $id) {
        $resource = call_user_func([$this->model, 'find'], [$id]);
        if ($resource->update_attributes($_POST[$this->resource_name])) {
            $this->msg->success('Saved Successfully', '/'.$this->pluralized);
        } else {
            $this->msg->success("Couldn't save to database", '/'.$this->pluralized.'/edit/'.$resource->id);
        }
    }

    /**
     * Action Destroy.
     *
     * @param int $id The id of the resource.
     */
    public function destroy(int $id) {
        $resource = call_user_func([$this->model, 'find'], [$id]);
        if ($resource->delete()) {
            $this->msg->success('Deleted Successfully', '/'.$this->pluralized);
        } else {
            $this->msg->success("Couldn't delete registry", '/'.$this->pluralized);
        }
    }
}
