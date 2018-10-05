<?php
namespace Payum\WayForPay\Action;

use function GuzzleHttp\Psr7\parse_query;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\SensitiveValue;

/**
 * @property \WayForPay $api
 */
class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    
    public function __construct()
    {
        $this->apiClass = \WayForPay::class;
    }
    
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null != $model['response_code']) {
            return;
        }

        $api = clone $this->api;
        list($url, $fields) = explode('?', $api->generatePurchaseUrl($model->toUnsafeArray()));
        $url = str_replace('/get', '', $url);
        $fields = parse_query($fields);

        throw new HttpPostRedirect($url, $fields);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
