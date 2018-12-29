<?php

namespace Reallyli\LaravelUnibehavior;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Jenssegers\Agent\Facades\Agent;
use Torann\GeoIP\Facades\GeoIP;

class Unibehavior
{
    /**
     * @var AuthManager
     */
    protected $auth;

    /**
     * @var mixed
     */
    protected $causedBy;

    /**
     * @var
     */
    protected $config;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var BehaviorLog
     */
    protected $modelInstance;

    /**
     * Construct
     *
     * @param AuthManager $auth
     * @param Repository $config
     * @param Request $request
     * @return void
     */
    public function __construct(AuthManager $auth, Repository $config, Request $request)
    {
        $this->auth = $auth;
        $this->config = $config->get('unibehavior');
        $this->request = $request;

        $authDriver = $this->config['default_auth_driver'] ?? $auth->getDefaultDriver();

        if (starts_with(app()->version(), '5.1')) {
            $this->causedBy = $auth->driver($authDriver)->user();
        } else {
            $this->causedBy = $auth->guard($authDriver)->user();
        }

        $this->modelInstance = $this->modelInstance();
    }

    /**
     * 记录
     *
     * @param string $description
     * @return mixed
     */
    public function record(string $description = '')
    {
        if (! $this->isEnabled()) {
            return;
        }
        try {
            $this->modelInstance->ip = ip2long($this->ip());
            $this->modelInstance->route = $this->route();
            $this->modelInstance->behavior = $this->defaultBehavior();
            $this->modelInstance->device = $this->device();
            $this->modelInstance->user_agent = $this->userAgent();
            $this->modelInstance->country = $this->location()['country'] ?? null;
            $this->modelInstance->city = $this->location()['city'] ?? null;
            $this->modelInstance->description = $description;
            $this->modelInstance->causer_id = $this->causedBy ? $this->causedBy->id : null;
            $this->modelInstance->causer_type = $this->causedBy ? get_class($this->causedBy) : null;
            $this->modelInstance->save();

        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }

        return $this->modelInstance;
    }

    /**
     * 致使者
     * @param $authorizedUser
     * @return $this
     */
    public function causedBy($authorizedUser)
    {
        $this->causedBy = $authorizedUser;

        return $this;
    }

    /**
     * 行为
     *
     * @since 2018/12/29
     * @param $behavior
     * @return $this
     */
    public function behavior($behavior)
    {
        $this->modelInstance->behavior = $behavior;

        return $this;
    }

    /**
     * 开启记录
     *
     * @since 2018/12/29
     * @return string
     */
    protected function defaultBehavior()
    {
        if ($this->config['trans']) {
            $behavior = __($this->config['trans'])[$this->route()] ?? '';

            return $behavior;
        }

        return $this->config['default_behavior'];
    }

    /**
     * 开启记录
     *
     * @since 2018/12/29
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->config['enabled'];
    }

    /**
     * 模型实例
     *
     * @since 2018/12/29
     * @return BehaviorLog
     */
    protected function modelInstance()
    {
        throw_unless(class_exists($this->config['model']), '\Exception', 'Model not exist');

        return new $this->config['model'];
    }

    /**
     * Ip
     *
     * @since 2018/12/29
     * @return string
     */
    protected function ip()
    {
        return $this->request->ip();
    }

    /**
     * Route
     *
     * @since 2018/12/29
     * @return string
     */
    protected function route()
    {
        return str_before($this->request->getRequestUri(), '?');
    }

    /**
     * device
     *
     * @since 2018/12/29
     * @return string
     */
    protected function device()
    {
        return Agent::device();
    }

    /**
     * userAgent
     *
     * @since 2018/12/29
     * @return string
     */
    protected function userAgent()
    {
        return $this->request->userAgent();
    }

    /**
     * Location
     *
     * @since 2018/12/29
     * @return array
     */
    protected function location()
    {
        $location = GeoIP::getLocation($this->request->ip());

        throw_unless($location && is_object($location), '\Exception', 'geo ip get location error');

        return $location->toArray();
    }
}