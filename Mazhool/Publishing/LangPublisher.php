<?php

namespace AwatBayazidi\Foundation\Mazhool\Publishing;

class LangPublisher extends Publisher
{

    protected $showMessage = false;


    public function getDestinationPath()
    {
        $name = $this->module->getLowerName();
        return base_path("resources/lang/mazhool/{$name}");
    }

    public function getSourcePath()
    {
        return $this->getModule()->getExtraPath(
            $this->repository->getPathGenerator('lang')
        );
    }
}
