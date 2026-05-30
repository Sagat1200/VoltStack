<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Actions;

use Quantum\Actions\ActionDispatcher;
use Quantum\Exceptions\HttpException;
use Quantum\Validation\ValidationException;
use VoltStack\Framework\Tests\TestCase;

final class ActionDispatcherTest extends TestCase
{
    public function test_dispatcher_resolves_and_runs_action(): void
    {
        $app = $this->createApplication();
        $result = $app->actions()->dispatch(CreateUserAction::class, [
            'email' => 'user@example.com',
            'name' => 'VoltStack',
        ]);

        self::assertSame([
            'email' => 'user@example.com',
            'name' => 'VoltStack',
            'validated' => true,
        ], $result);
    }

    public function test_dispatcher_throws_for_failed_authorization(): void
    {
        $app = $this->createApplication();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        $app->actions()->dispatch(DeniedAction::class);
    }

    public function test_dispatcher_throws_validation_exception(): void
    {
        $app = $this->createApplication();

        $this->expectException(ValidationException::class);

        $app->actions()->dispatch(CreateUserAction::class, [
            'email' => 'bad-email',
            'name' => 'ok',
        ]);
    }

    public function test_controller_can_dispatch_action_helper(): void
    {
        $app = $this->createApplication();
        $app->router()->post('/users', UsersController::class . '@store');

        $response = $app->kernel()->handle(
            \Quantum\Http\Request::create('POST', '/users', [], [
                'email' => 'helper@example.com',
                'name' => 'Action Helper',
            ])
        );

        self::assertSame(200, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame(
            '{"email":"helper@example.com","name":"Action Helper","validated":true}',
            $response->content()
        );
    }
}

final class CreateUserAction
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:3'],
        ];
    }

    public function handle(array $payload): array
    {
        return $payload + ['validated' => true];
    }
}

final class DeniedAction
{
    public function authorize(): bool
    {
        return false;
    }

    public function handle(): string
    {
        return 'never';
    }
}

final class UsersController extends \Quantum\Controllers\Controller
{
    public function store(): \Quantum\Http\Response
    {
        return $this->json($this->action(CreateUserAction::class, [
            'email' => 'helper@example.com',
            'name' => 'Action Helper',
        ]));
    }
}
