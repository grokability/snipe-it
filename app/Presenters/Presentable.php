<?php

namespace App\Presenters;

trait Presentable
{
    protected $presenterInterface;

    public function present()
    {
        if (! $this->presenter || ! class_exists($this->presenter)) {
            throw new \Exception('Presenter class does not exist');
        }

        if (! isset($this->presenterInterface)) {
            $this->presenterInterface = new $this->presenter($this);
        }

        return $this->presenterInterface;
    }
}
