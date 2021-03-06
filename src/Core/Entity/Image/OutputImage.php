<?php

namespace Core\Entity\Image;

use Core\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OutputImage
 * @package Core\Entity
 */
class OutputImage
{
    /** Content TYPE */
    const WEBP_MIME_TYPE = 'image/webp';
    const JPEG_MIME_TYPE = 'image/jpeg';
    const PNG_MIME_TYPE = 'image/png';
    const GIF_MIME_TYPE = 'image/gif';

    /** Extension output */
    const EXT_INPUT = 'input';
    const EXT_AUTO = 'auto';
    const EXT_PNG = 'png';
    const EXT_WEBP = 'webp';
    const EXT_JPG = 'jpg';
    const EXT_GIF = 'gif';

    /** @var InputImage */
    protected $inputImage;

    /** @var string */
    protected $outputImageName;

    /** @var string */
    protected $outputImagePath;

    /** @var string */
    protected $outputImageExtension;

    /** @var string */
    protected $outputImageContent;

    /** @var string */
    protected $commandString;

    /** @var array list of the supported output extensions */
    protected $allowedOutExtensions = [self::EXT_PNG, self::EXT_JPG, self::EXT_GIF, self::EXT_WEBP];

    /**
     * OutputImage constructor.
     *
     * @param InputImage $inputImage
     *
     * @throws InvalidArgumentException
     */
    public function __construct(InputImage $inputImage)
    {
        $this->inputImage = $inputImage;
        $this->generateFilesName();
        $this->outputImageExtension = $this->generateFileExtension();
        $this->outputImagePath .= '.'.$this->outputImageExtension;
        $this->outputImageName .= '.'.$this->outputImageExtension;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function extractKey(string $key): string
    {
        return $this->inputImage->extractKey($key);
    }

    /**
     * @return InputImage
     */
    public function getInputImage(): InputImage
    {
        return $this->inputImage;
    }

    /**
     * @return string
     */
    public function getOutputImageName(): string
    {
        return $this->outputImageName;
    }

    /**
     * @return string
     */
    public function getOutputImagePath(): string
    {
        return $this->outputImagePath;
    }

    /**
     * @param string $commandStr
     */
    public function setCommandString(string $commandStr)
    {
        $this->commandString = $commandStr;
    }

    /**
     * @return string
     */
    public function getCommandString(): string
    {
        return $this->commandString;
    }

    /**
     * @return string
     */
    public function getOutputImageContent(): string
    {
        return $this->outputImageContent;
    }

    /**
     * @param string $outputImageContent
     */
    public function attachOutputContent(string $outputImageContent)
    {
        $this->outputImageContent = $outputImageContent;
    }

    /**
     * @return string
     */
    public function getOutputImageExtension(): string
    {
        return $this->outputImageExtension;
    }

    /**
     * Remove Temporary file
     */
    public function removeOutputImage()
    {
        if (file_exists($this->getOutputImagePath())) {
            unlink($this->getOutputImagePath());
        }
    }

    /**
     * Generate files name + files path
     */
    protected function generateFilesName()
    {
        $hashedOptions = $this->inputImage->optionsBag();
        $this->outputImageName = $hashedOptions->hashedOptionsAsString($this->inputImage->sourceImageUrl());
        $this->outputImagePath = TMP_DIR.$this->outputImageName;

        if ($hashedOptions->get('refresh')) {
            $this->outputImagePath .= uniqid("-", true);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function generateFileExtension()
    {
        $requestedOutput = $this->extractKey('output');

        if ($requestedOutput == self::EXT_AUTO && $this->isWebPBrowserSupported()) {
            return self::EXT_WEBP;
        }

        if ($requestedOutput == self::EXT_INPUT || $requestedOutput == self::EXT_AUTO) {
            return $this->extensionByMimeType($this->inputImage->sourceImageMimeType());
        }

        if (!in_array($requestedOutput, $this->allowedOutExtensions)) {
            throw new InvalidArgumentException("Invalid file output requested : ".$requestedOutput);
        }

        return $requestedOutput;
    }

    /**
     * given a mime-type this returns the extension associated to it
     *
     * @param  string $mimeType mime-type
     *
     * @return string extension OR jpeg as default
     */
    protected function extensionByMimeType(string $mimeType): string
    {
        $mimeToExtensions = [
            self::PNG_MIME_TYPE => self::EXT_PNG,
            self::WEBP_MIME_TYPE => self::EXT_WEBP,
            self::JPEG_MIME_TYPE => self::EXT_JPG,
            self::GIF_MIME_TYPE => self::EXT_GIF,
        ];

        return array_key_exists($mimeType, $mimeToExtensions) ? $mimeToExtensions[$mimeType] : self::EXT_JPG;
    }

    /**
     * Return boolean stating if WebP image format is supported; following these conditions:
     *  - The request is specifically expecting a webP response, independent of the browser's capabilities
     *  OR:
     *  - The browser sent headers explicitly stating it supports webp (absolute requirement)
     *
     * @return bool
     */
    public function isOutputWebP(): bool
    {
        return $this->outputImageExtension == self::EXT_WEBP;
    }

    /**
     * @return bool
     */
    public function isOutputGif(): bool
    {
        return $this->outputImageExtension == self::EXT_GIF;
    }

    /**
     * @return bool
     */
    public function isOutputPng(): bool
    {
        return $this->outputImageExtension == self::EXT_PNG;
    }

    /**
     * @return bool
     */
    public function isInputGif(): bool
    {
        return $this->inputImage->sourceImageMimeType() == self::GIF_MIME_TYPE;
    }

    /**
     * @return bool
     */
    public function isOutputMozJpeg(): bool
    {
        return $this->extractKey('mozjpeg') == 1 &&
            (!$this->isOutputPng() || $this->outputImageExtension == self::EXT_JPG) &&
            (!$this->isOutputGif());
    }

    /**
     * This just checks if the browser requesting the asset explicitly supports WebP
     * @return boolean
     */
    public function isWebPBrowserSupported(): bool
    {
        return in_array(self::WEBP_MIME_TYPE, Request::createFromGlobals()->getAcceptableContentTypes());
    }
}
