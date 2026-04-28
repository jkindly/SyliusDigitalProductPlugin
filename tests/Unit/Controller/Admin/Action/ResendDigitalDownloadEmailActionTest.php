<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Unit\Controller\Admin\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use SyliusDigitalProductPlugin\CommandDispatcher\ResendDigitalDownloadEmailDispatcherInterface;
use SyliusDigitalProductPlugin\Controller\Admin\Action\ResendDigitalDownloadEmailAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ResendDigitalDownloadEmailActionTest extends TestCase
{
    /** @var MockObject&OrderRepositoryInterface<OrderInterface> */
    private MockObject&OrderRepositoryInterface $orderRepository;

    private MockObject&ResendDigitalDownloadEmailDispatcherInterface $dispatcher;

    private MockObject&CsrfTokenManagerInterface $csrfTokenManager;

    private MockObject&RouterInterface $router;

    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->dispatcher = $this->createMock(ResendDigitalDownloadEmailDispatcherInterface::class);
        $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->requestStack = new RequestStack();

        $session = new Session(new MockArraySessionStorage());
        $sessionRequest = Request::create('/');
        $sessionRequest->setSession($session);
        $this->requestStack->push($sessionRequest);
    }

    private function createAction(): ResendDigitalDownloadEmailAction
    {
        return new ResendDigitalDownloadEmailAction(
            $this->orderRepository,
            $this->dispatcher,
            $this->csrfTokenManager,
            $this->requestStack,
            $this->router,
        );
    }

    public function testDispatchesEmailAndRedirectsToDefaultRouteWhenOrderFound(): void
    {
        $orderId = '42';
        $order = $this->createMock(OrderInterface::class);
        $order->method('getId')->willReturn(42);

        $request = new Request(
            query: ['_csrf_token' => 'valid-token'],
            attributes: ['id' => $orderId, '_sylius' => []],
        );

        $this->csrfTokenManager
            ->expects($this->once())
            ->method('isTokenValid')
            ->with(new CsrfToken($orderId, 'valid-token'))
            ->willReturn(true);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrderById')
            ->with($orderId)
            ->willReturn($order);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($order);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('sylius_admin_order_show', ['id' => 42])
            ->willReturn('/admin/orders/42');

        $response = ($this->createAction())($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin/orders/42', $response->getTargetUrl());
    }

    public function testThrowsForbiddenWhenCsrfTokenIsInvalid(): void
    {
        $orderId = '42';

        $request = new Request(
            query: ['_csrf_token' => 'invalid-token'],
            attributes: ['id' => $orderId],
        );

        $this->csrfTokenManager
            ->expects($this->once())
            ->method('isTokenValid')
            ->with(new CsrfToken($orderId, 'invalid-token'))
            ->willReturn(false);

        $this->orderRepository->expects($this->never())->method('findOrderById');
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Invalid csrf token.');

        ($this->createAction())($request);
    }

    public function testThrowsNotFoundWhenOrderDoesNotExist(): void
    {
        $orderId = '99';

        $request = new Request(
            query: ['_csrf_token' => 'token'],
            attributes: ['id' => $orderId],
        );

        $this->csrfTokenManager
            ->method('isTokenValid')
            ->willReturn(true);

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrderById')
            ->with($orderId)
            ->willReturn(null);

        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('The order with id 99 has not been found');

        ($this->createAction())($request);
    }

    public function testAddsSuccessFlashMessageOnSuccess(): void
    {
        $orderId = '42';
        $order = $this->createMock(OrderInterface::class);
        $order->method('getId')->willReturn(42);

        $request = new Request(
            query: ['_csrf_token' => 'token'],
            attributes: ['id' => $orderId, '_sylius' => []],
        );

        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);
        $this->orderRepository->method('findOrderById')->willReturn($order);
        $this->router->method('generate')->willReturn('/admin/orders/42');

        ($this->createAction())($request);

        $flashBag = $this->requestStack->getSession()->getBag('flashes');
        $this->assertSame(
            ['sylius_digital_product.email.digital_download_resent'],
            $flashBag->get('success'),
        );
    }

    public function testRedirectsToStringRouteWhenRedirectIsString(): void
    {
        $orderId = '42';
        $order = $this->createMock(OrderInterface::class);

        $request = new Request(
            query: ['_csrf_token' => 'token'],
            attributes: ['id' => $orderId, '_sylius' => ['redirect' => 'sylius_admin_order_index']],
        );

        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);
        $this->orderRepository->method('findOrderById')->willReturn($order);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('sylius_admin_order_index')
            ->willReturn('/admin/orders');

        $response = ($this->createAction())($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin/orders', $response->getTargetUrl());
    }

    public function testRedirectsWithCustomRouteAndParamsWhenRedirectIsArray(): void
    {
        $orderId = '42';
        $order = $this->createMock(OrderInterface::class);

        $request = new Request(
            query: ['_csrf_token' => 'token'],
            attributes: [
                'id' => $orderId,
                '_sylius' => [
                    'redirect' => [
                        'route' => 'sylius_admin_order_show',
                        'params' => ['id' => 42],
                    ],
                ],
            ],
        );

        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);
        $this->orderRepository->method('findOrderById')->willReturn($order);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('sylius_admin_order_show', ['id' => 42])
            ->willReturn('/admin/orders/42');

        $response = ($this->createAction())($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin/orders/42', $response->getTargetUrl());
    }

    public function testRedirectsToDefaultRouteWithOrderIdWhenArrayRedirectHasNoRoute(): void
    {
        $orderId = '42';
        $order = $this->createMock(OrderInterface::class);
        $order->method('getId')->willReturn(42);

        $request = new Request(
            query: ['_csrf_token' => 'token'],
            attributes: ['id' => $orderId, '_sylius' => ['redirect' => []]],
        );

        $this->csrfTokenManager->method('isTokenValid')->willReturn(true);
        $this->orderRepository->method('findOrderById')->willReturn($order);

        $this->router
            ->expects($this->once())
            ->method('generate')
            ->with('sylius_admin_order_show', ['id' => 42])
            ->willReturn('/admin/orders/42');

        $response = ($this->createAction())($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin/orders/42', $response->getTargetUrl());
    }

    public function testThrowsWhenOrderIdIsEmpty(): void
    {
        $request = new Request(
            query: ['_csrf_token' => 'token'],
            attributes: ['id' => ''],
        );

        $this->expectException(\InvalidArgumentException::class);

        ($this->createAction())($request);
    }
}
