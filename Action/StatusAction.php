<?php
namespace Payum\WayForPay\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = \WayForPay::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['orderReference']) {
            $request->markNew();

            return;
        }

        $data = $model->toUnsafeArray();

        $response = $this->api->checkStatus($data);

        if ($response['reasonCode'] == 1100) {
            $request->markCaptured();

            return;
        }

        if ($response['reasonCode'] == 1134) {
            $request->markPending();

            return;
        }

        $request->markFailed();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
