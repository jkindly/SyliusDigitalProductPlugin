<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Controller\Admin\Action;

use Sylius\Bundle\CoreBundle\Provider\FlashBagProvider;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use SyliusDigitalProductPlugin\CommandDispatcher\ResendDigitalDownloadEmailDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Webmozart\Assert\Assert;

final readonly class ResendDigitalDownloadEmailAction
{
    /** @param OrderRepositoryInterface<OrderInterface> $orderRepository */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ResendDigitalDownloadEmailDispatcherInterface $resendDigitalDownloadEmailDispatcher,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private RequestStack $requestStack,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $orderId = $request->attributes->get('id', '');
        Assert::stringNotEmpty($orderId, 'Order id must be provided.');

        if (!$this->csrfTokenManager->isTokenValid(
            new CsrfToken($orderId, (string) $request->query->get('_csrf_token', '')),
        )) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Invalid csrf token.');
        }

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOrderById($orderId);
        if (null === $order) {
            throw new NotFoundHttpException(sprintf('The order with id %s has not been found', $orderId));
        }

        $this->resendDigitalDownloadEmailDispatcher->dispatch($order);

        FlashBagProvider
            ::getFlashBag($this->requestStack)
            ->add('success', 'sylius_digital_product.email.digital_download_resent')
        ;

        return $this->redirect($request, $order);
    }

    private function redirect(Request $request, OrderInterface $order): RedirectResponse
    {
        $redirectConf = $request->attributes->get('_sylius', []);
        Assert::isArray($redirectConf, 'Redirect configuration must be an array.');

        $redirect = $redirectConf['redirect'] ?? null;

        if (null === $redirect || is_array($redirect)) {
            return new RedirectResponse($this->router->generate(
                $redirect['route'] ?? 'sylius_admin_order_show',
                $redirect['params'] ?? ['id' => $order->getId()],
            ));
        }

        return new RedirectResponse($this->router->generate($redirect));
    }
}
