<?php

namespace HeyMoon\MVTTools\Registry;

use HeyMoon\MVTTools\Exception\MissingDependencyException;
use HeyMoon\MVTTools\Exception\UnknownFormatException;
use HeyMoon\MVTTools\Export\ExportFormatInterface;

abstract class AbstractExportFormatRegistry
{
    /** @var ExportFormatInterface[] */
    protected array $formats = [];

    public function __construct()
    {
        foreach ($this->supports() as $format) {
            if ($format instanceof ExportFormatInterface) {
                $this->addFormat($format);
            }
        }
    }

    public function addFormat(ExportFormatInterface $format): self
    {
        foreach ($format->supports() as $ext) {
            $this->formats[$ext] = $format;
        }
        return $this;
    }

    public function byPath(string $path): ExportFormatInterface
    {
        return $this->get(pathinfo($path)['extension'] ?? '');
    }

    public function get(string $ext): ExportFormatInterface
    {
        if (!array_key_exists($ext, $this->formats)) {
            throw new UnknownFormatException($ext);
        }
        $format = $this->formats[$ext];
        if (!$format->isAvailable()) {
            throw new MissingDependencyException($ext, $format->require());
        }
        return $format;
    }

    protected abstract function supports(): array;
}
