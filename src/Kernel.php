<?php

declare(strict_types=1);

namespace App;

use PDO;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => '8404696ea1c78c8ef2e6e97e987c21ef',
        ]);
        $container->services()->set('pdo', PDO::class)
            ->arg('$dsn', 'mysql:dbname=messenger;host=mysql')
            ->arg('$username', 'admin')
            ->arg('$password', 'secret')
            ->public();
        $container->services()->set('message_repository', MessageRepository::class)
            ->arg('$connection', service('pdo'))
            ->public();
        $container->services()->set('messenger', Messenger::class)
            ->arg('$messageRepository', service('message_repository'))
            ->public();
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('fetch', '/')->controller([$this, 'fetch'])->methods(['GET']);
        $routes->add('create', '/')->controller([$this, 'create'])->methods(['POST']);
    }

    public function fetch(Request $request): JsonResponse
    {
        $timestamp = $request->query->get('timestamp', Messenger::DEFAULT_TIMESTAMP);
        $limit = $request->query->getInt('limit', Messenger::DEFAULT_LIMIT);

        $messenger = $this->getContainer()->get('messenger');

        try {
            $messages = $messenger->fetch($timestamp, $limit);
        } catch (Throwable) {
            return $this->error('Something goes wrong.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(array_map(fn(Message $message) => $message->toArray(), $messages));
    }

    public function create(Request $request): JsonResponse
    {
        $messenger = $this->getContainer()->get('messenger');

        $data = json_decode($request->getContent(), true);
        $violations = Validation::createValidator()->validate($data, new Collection([
            'content' => [
                new NotBlank(['message' => 'Content should not be blank.']),
                new Length([
                    'min' => 5,
                    'max' => 500,
                    'minMessage' => 'Content is too short.',
                    'maxMessage' => 'Content is too long.',
                ]),
            ]
        ]));

        if ($violations->count() > 0) {
            return $this->error($violations->get(0)->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $messenger->save(Message::create(Uuid::uuid4()->toString(), $data['content']));
        } catch (Throwable) {
            return $this->error('Something goes wrong.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'success'], Response::HTTP_CREATED);
    }

    private function error(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['status' => 'error', 'message' => $message], $status);
    }
}
