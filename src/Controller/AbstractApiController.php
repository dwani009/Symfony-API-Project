<?php

declare(strict_types=1);

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends AbstractFOSRestController
{
    protected const SERIALIZATION_GROUP_USER = 'user';
    protected const SERIALIZATION_GROUP_ADMIN = 'admin';

    private array $serializationGroups = [
        self::SERIALIZATION_GROUP_ADMIN,
    ];

    /**
     * @param string $type
     * @param $data
     * @param array $options
     * @return FormInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function buildForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->container->get('form.factory')->createNamed('', $type, $data, $options);
    }

    /**
     * Standardized API response method.
     *
     * @param mixed $data The primary data payload.
     * @param int $statusCode The HTTP status code.
     * @param string|null $message Optional message for additional context.
     * @return Response
     */
    protected function respond($data, int $statusCode = Response::HTTP_OK, string $message = null): Response
    {
        $status = ($statusCode >= 200 && $statusCode < 300) ? 'success' : 'error';
        $responseMessage = $this->getDefaultMessageForStatus($statusCode, $status);

        // In case resource is deleted passing 204 would not display any content message
        // Hence set it to 200 in order to display content message
        if ($statusCode === Response::HTTP_NO_CONTENT) {
            $statusCode = Response::HTTP_OK;
        }

        $responseContent = [
            'status' => $status,
            'statusCode' => $statusCode,
            'data' => $status === 'success' ? $data : null,
            'message' => $message ?? $responseMessage,
            'metadata' => [
                'requestId' => uniqid(), // Can be used for request tracking
                'requestDuration' => round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) * 1000) . 'ms',
            ]
        ];

        // Use view and serialization groups for `data` formatting
        $view = $this->view($responseContent, $statusCode);

        // Set serialization groups
        $view->getContext()->setGroups($this->serializationGroups);

        return $this->handleView($view);
    }

    /**
     * Provides default messages based on HTTP status codes for CRUD operations.
     *
     * @param int $statusCode The HTTP status code.
     * @param string $status The determined status string ('success' or 'error').
     * @return string
     */
    private function getDefaultMessageForStatus(int $statusCode, string $status): string
    {
        switch ($statusCode) {
            case Response::HTTP_CREATED:
                return 'Resource successfully created.';

            case Response::HTTP_NO_CONTENT:
                return 'Resource successfully deleted.';

            case Response::HTTP_OK:
                return $status === 'success' ? 'Request processed successfully.' : 'Failed to process the request.';

            case Response::HTTP_BAD_REQUEST:
                return 'The request could not be understood or was missing required parameters.';

            case Response::HTTP_UNAUTHORIZED:
                return 'Authentication failed or user does not have permissions.';

            case Response::HTTP_FORBIDDEN:
                return 'Access is forbidden to the requested resource.';

            case Response::HTTP_NOT_FOUND:
                return 'The requested resource could not be found.';

            case Response::HTTP_INTERNAL_SERVER_ERROR:
                return 'An internal server error occurred. Please try again later.';

            default:
                return $status === 'success' ? 'Success' : 'An error occurred.';
        }
    }

    /**
     * Set serialization groups dynamically.
     *
     * @param array $groups
     * @return void
     */
    protected function setSerializationGroups(array $groups): void
    {
        $this->serializationGroups = $groups;
    }
}