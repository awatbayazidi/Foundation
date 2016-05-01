<?php

namespace AwatBayazidi\Foundation\Mazhool\Publishing;

class AssetPublisher extends Publisher
{

    protected $showMessage = false;


    public function getDestinationPath()
    {
        return $this->repository->assetPath($this->getModule()->getLowerName());
    }


    public function getSourcePath()
    {
        return $this->getModule()->getExtraPath(
            $this->repository->getPathGenerator('assets')
        );
    }
}
