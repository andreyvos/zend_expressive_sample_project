<?php

namespace Options\Action\Post;

use Options\InputFilter;
use Options\Model\OptionsTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Helper\UrlHelper;

class Options
{
    /**
     * @var OptionsTable
     */
    private $optionsTable;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(OptionsTable $optionsTable, UrlHelper $urlHelper)
    {
        $this->optionsTable = $optionsTable;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = $request->getParsedBody() ?? [];

        $filter = new InputFilter\Options();
        $filter->setData($data);

        if (!$filter->isValid()) {
            return new JsonResponse([
                'errors' => $filter->getMessages()
            ]);
        }

        $values = $filter->getValues();
        if (isset($values['amazon_secret']) && empty($values['amazon_secret'])) {
            unset($values['amazon_secret']);
        }

        if (isset($values['stripe_secret_key']) && empty($values['stripe_secret_key'])) {
            unset($values['stripe_secret_key']);
        }

        /** @var \Zend\Diactoros\UploadedFile[] $uploadeds */
        $uploadeds = $request->getUploadedFiles();
        foreach ($uploadeds as $key => $uploaded) {
            if (!in_array($key, ['certificate_background', 'website_logo']))
                continue;

            if ($uploaded->getError() !== UPLOAD_ERR_NO_FILE) {
                try {
                    $mediaType = $uploaded->getClientMediaType();
                    if (!in_array($mediaType, ['image/png', 'image/jpeg'])) {
                        throw new \Exception("Please upload jpeg or png file");
                    }

                    $type = str_replace('image/', '', $mediaType);
                    $stream = $uploaded->getStream();
                    $values[$key] = 'data:image/' . $type . ';base64,' . base64_encode($stream);
                } catch (\Exception $e) {
                    return new JsonResponse(['errors' => $e->getMessage()]);
                }
            } else {
                unset($values[$key]);
            }
        }

        $this->optionsTable->save($values);

        return new JsonResponse(['redirectTo' => ($this->urlHelper)('options/form/options')]);
    }
}
