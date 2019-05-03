<?php

namespace Core\Controller;

use Core\Entity\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends CoreController
{
    /**
     * @return string
     */
    public function indexAction()
    {
        return $this->render('Default/index');
    }

    /**
     * @param string $options
     * @param string $imageSrc
     *
     * @return Response
     * @throws \Exception
     */
    public function uploadAction(string $options, string $imageSrc = null): Response
    {
        $image = $this->imageHandler()->processImage($options, $imageSrc);

        $this->response->generateImageResponse($image);

        return $this->response;
    }

    /**
     * @param string $options
     * @param string $imageSrc
     *
     * @return Response
     * @throws \Exception
     */
    public function pathAction(string $options, string $imageSrc = null): Response
    {
        $image = $this->imageHandler()->processImage($options, $imageSrc);

        $this->response->generatePathResponse($image);

        return $this->response;
    }

    /**
     * Get image in POST data as base64 encoded string.
     *
     * @param string $options
     *
     * @return Response
     * @throws \Exception
     */
    public function postAction(string $options): Response
    {
        $request = Request::createFromGlobals();

        $encodedImage = $request->get('image');

        if ($encodedImage === null || strlen($encodedImage) === 0) {
            $this->response->setStatusCode(500, 'Missing "image" in post data');
        } else {
            $inputImage = base64_decode($encodedImage);

            $filename = TMP_DIR . uniqid('/post-original-', true);
            file_put_contents($filename, $inputImage);

            $outputImage = $this->imageHandler()->processImage($options, $filename);

            $this->response->generateImageResponse($outputImage);

            unlink($filename);
        }

        return $this->response;
    }
}
