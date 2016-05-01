<?php

namespace AwatBayazidi\Foundation\Mazhool\Publishing;
class MigrationPublisher extends AssetPublisher
{
    public function getDestinationPath()
    {
        return $this->repository->config('paths.migration');
    }

    public function getSourcePath()
    {
        return $this->getModule()->getExtraPath($this->repository->getPathGenerator('migration'));
    }
}
